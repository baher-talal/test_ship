<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'branches', 'branch']);

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($role = $request->query('role')) {
            $query->whereHas('roles', function ($r) use ($role) {
                $r->where('roles.id', $role);
            });
        }

        $users = $query->latest('id')->paginate(15)->appends($request->query());

        return view('users.index', ['users' => $users, 'roles' => Role::orderBy('level')->get()]);
    }

    public function create(Request $request)
    {
        return view('users.create', array_merge([
            'user' => new User(['is_active' => true, 'work_type' => 'branch']),
            'selectedRoles' => [],
            'selectedBranches' => [],
            'selectedPermissions' => [],
        ], $this->formData($request->user())));
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        $data = $this->validated($request, $actor, null);

        $user = DB::transaction(function () use ($data, $actor) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'branch_id' => $data['branch_id'] ?? null,
                'work_type' => $data['work_type'],
                'is_active' => $data['is_active'],
            ]);
            $this->syncAccess($user, $data);

            return $user;
        });

        return redirect()->route('users.index')->with('success', __('User added').' '.$user->name);
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeTarget($request->user(), $user);
        $user->load(['roles', 'branches']);

        return view('users.edit', array_merge([
            'user' => $user,
            'selectedRoles' => $user->roles->pluck('id')->all(),
            'selectedBranches' => $user->branches->pluck('id')->all(),
            'selectedPermissions' => $user->extraPermissions(),
        ], $this->formData($request->user(), $user)));
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();
        $this->authorizeTarget($actor, $user);
        $data = $this->validated($request, $actor, $user);

        DB::transaction(function () use ($data, $user, $actor) {
            $fill = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'work_type' => $data['work_type'],
                'is_active' => $data['is_active'],
            ];
            if (! empty($data['password'])) {
                $fill['password'] = Hash::make($data['password']);
            }
            $user->update($fill);
            $this->syncAccess($user, $data);
        });

        return redirect()->route('users.index')->with('success', __('User updated').'.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', __('You cannot delete your current account').'.');
        }

        $this->authorizeTarget($request->user(), $user);
        $user->delete();

        return redirect()->route('users.index')->with('success', __('User deleted').'.');
    }

    /* ------------------------------------------------------------------ */

    /** Form data: roles available by job hierarchy + permissions + branches */
    protected function formData(User $actor, User $target = null)
    {
        $roles = Role::orderBy('level')->orderBy('name')->get();

        if (! $actor->isAdmin()) {
            // A role at your own level or higher cannot be granted
            $roles = $roles->filter(function ($r) use ($actor) {
                return $r->level > $actor->rankLevel();
            })->values();
        }

        $grants = [];
        foreach (Role::all() as $role) {
            $grants[$role->id] = $role->permissionKeys();
        }

        // Permissions the current user can grant (cannot grant what they do not have)
        $grantable = Permissions::keys();
        if (! $actor->isAdmin()) {
            $grantable = array_values(array_filter($grantable, function ($k) use ($actor) {
                return $actor->hasPermission($k);
            }));
        }

        return [
            'roles' => $roles,
            'roleGrants' => $grants,
            'permissionGroups' => Permissions::groups(),
            'grantable' => $grantable,
            'branches' => $actor->selectableBranches(),
        ];
    }

    protected function validated(Request $request, User $actor, User $target = null)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(optional($target)->id)],
            'phone' => 'nullable|string|max:30',
            'work_type' => ['required', Rule::in(array_keys(User::WORK_TYPES))],
            'branch_id' => 'nullable|exists:branches,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::in(Permissions::keys())],
            'password' => [$target ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ], ['roles.required' => __('Select at least one role').'.', 'roles.min' => __('Select at least one role').'.']);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['roles'] = array_map('intval', $data['roles']);
        $data['branches'] = array_map('intval', $data['branches'] ?? []);
        $data['permissions'] = $data['permissions'] ?? [];

        $roles = Role::whereIn('id', $data['roles'])->get();

        // ---- Job hierarchy rules ----
        if (! $actor->isAdmin()) {
            foreach ($roles as $role) {
                if ($role->level <= $actor->rankLevel()) {
                    throw ValidationException::withMessages(['roles' => __('You cannot grant the role').' '.'"'.$role->name.'" '.__('because it is at or above your level').'.']);
                }
            }
            foreach ($data['permissions'] as $perm) {
                if (! $actor->hasPermission($perm)) {
                    throw ValidationException::withMessages(['permissions' => __('You cannot grant a permission you do not have').': '.Permissions::label($perm)]);
                }
            }
            $allowedBranches = $actor->canAllBranches() ? null : $actor->branchIds();
            if ($allowedBranches !== null) {
                $chosen = array_filter(array_merge($data['branches'], [(int) ($data['branch_id'] ?? 0)]));
                if (array_diff($chosen, $allowedBranches)) {
                    throw ValidationException::withMessages(['branches' => __('You cannot assign branches outside your own branches').'.']);
                }
            }
        }

        // ---- Editing your own account: you cannot lose your admin role or deactivate yourself ----
        if ($target && $target->id === $actor->id) {
            $data['is_active'] = true;
            if ($actor->isAdmin()) {
                $adminId = Role::where('slug', 'admin')->value('id');
                $data['roles'] = array_values(array_unique(array_merge($data['roles'], [(int) $adminId])));
                $roles = Role::whereIn('id', $data['roles'])->get();
            }
        }

        // ---- Branch is optional, but branch-based or rotating staff need at least one branch ----
        $isAdmin = $roles->contains('slug', 'admin');
        $hasBranch = ! empty($data['branch_id']) || ! empty($data['branches']);
        if (! $isAdmin && in_array($data['work_type'], ['branch', 'rotating'], true) && ! $hasBranch) {
            throw ValidationException::withMessages([
                'branch_id' => __('Select at least one branch, or change the workplace to').' '.'"'.__('Remote').'" '.__('or').' '.'"'.__('Administration').'".',
            ]);
        }

        return $data;
    }

    protected function syncAccess(User $user, array $data)
    {
        $user->roles()->sync($data['roles']);

        // The primary branch is always added to the allowed branches list
        $branchIds = collect($data['branches'])->push(isset($data['branch_id']) ? (int) $data['branch_id'] : null)->filter()->unique()->values()->all();
        $user->branches()->sync($branchIds);

        // Directly granted permissions (besides what roles grant)
        $fromRoles = [];
        foreach (Role::whereIn('id', $data['roles'])->get() as $role) {
            $fromRoles = array_merge($fromRoles, $role->permissionKeys());
        }
        $extra = array_values(array_diff($data['permissions'], $fromRoles));

        DB::table('user_permissions')->where('user_id', $user->id)->delete();
        foreach ($extra as $perm) {
            DB::table('user_permissions')->insert(['user_id' => $user->id, 'permission' => $perm]);
        }
    }

    protected function authorizeTarget(User $actor, User $target)
    {
        if ($actor->id === $target->id) {
            return;
        }

        abort_unless($actor->canManageUser($target), 403, __('You cannot manage a user at or above your level').'.');
    }
}
