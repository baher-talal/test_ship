<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();

        return view('roles.index', ['rows' => $this->tree($roles)]);
    }

    public function create()
    {
        return view('roles.create', [
            'role' => new Role(['level' => 4]),
            'selected' => [],
            'parents' => Role::orderBy('level')->get(),
            'permissionGroups' => Permissions::groups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|alpha_dash|max:50|unique:roles,slug',
            'level' => 'required|integer|min:2|max:20',
            'parent_id' => 'nullable|exists:roles,id',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::in(Permissions::keys())],
        ]);

        $this->assertHierarchy($data['level'], $data['parent_id'] ?? null);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'level' => $data['level'],
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', __('Role added').'.');
    }

    public function edit(Role $role)
    {
        if ($role->isAdmin()) {
            return redirect()->route('roles.index')->with('error', __('The general manager role has all permissions and cannot be modified').'.');
        }

        return view('roles.edit', [
            'role' => $role,
            'selected' => $role->permissionKeys(),
            'parents' => Role::where('id', '!=', $role->id)->orderBy('level')->get(),
            'permissionGroups' => Permissions::groups(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->isAdmin()) {
            return redirect()->route('roles.index')->with('error', __('The general manager role cannot be modified').'.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'level' => 'required|integer|min:2|max:20',
            'parent_id' => ['nullable', 'exists:roles,id', Rule::notIn([$role->id])],
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::in(Permissions::keys())],
        ]);

        $this->assertHierarchy($data['level'], $data['parent_id'] ?? null);

        $role->update([
            'name' => $data['name'],
            'level' => $data['level'],
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', __('Role and permissions updated').'.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', __('Core system roles cannot be deleted').'.');
        }
        if ($role->users()->exists()) {
            return back()->with('error', __('A role assigned to users cannot be deleted. Move the users to another role first').'.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', __('Role deleted').'.');
    }

    /** The parent role must have a higher level (lower number) than the role itself */
    protected function assertHierarchy($level, $parentId)
    {
        if (! $parentId) {
            return;
        }

        $parent = Role::findOrFail($parentId);
        if ($parent->level >= (int) $level) {
            throw ValidationException::withMessages([
                'parent_id' => __('The parent role must have a higher level (lower number) than this role').'.',
            ]);
        }
    }

    /** Order roles as a hierarchy tree with depth */
    protected function tree($roles)
    {
        $byParent = $roles->groupBy(function ($r) {
            return $r->parent_id ?: 0;
        });
        $rows = [];
        $walk = function ($parentId, $depth) use (&$walk, &$rows, $byParent) {
            foreach (($byParent[$parentId] ?? collect())->sortBy('level') as $role) {
                $role->depth = $depth;
                $rows[] = $role;
                $walk($role->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $rows;
    }
}
