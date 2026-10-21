<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    const WORK_TYPES = [
        'branch' => 'Works in a branch',
        'rotating' => 'Moves between branches',
        'remote' => 'Works remotely',
        'hq' => 'Administration (head office)',
    ];

    protected $fillable = ['name', 'email', 'phone', 'password', 'branch_id', 'work_type', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['is_active' => 'boolean'];

    protected $permissionCache = null;

    /* ---------- Relations ---------- */

    /** Primary branch (optional) */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /** All branches the user may work on (for staff who rotate between branches) */
    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->orderBy('roles.level');
    }

    public function courier()
    {
        return $this->hasOne(Courier::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    /* ---------- Roles and permissions ---------- */

    public function hasRole($slug)
    {
        return $this->roles->contains('slug', $slug);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /** Highest hierarchy level of the user (lower number = higher rank) */
    public function rankLevel()
    {
        $min = $this->roles->min('level');

        return $min === null ? 99 : (int) $min;
    }

    public function extraPermissions()
    {
        return DB::table('user_permissions')->where('user_id', $this->id)->pluck('permission')->all();
    }

    /** All user permissions (from roles + directly granted ones). ['*'] = everything */
    public function permissions()
    {
        if ($this->permissionCache !== null) {
            return $this->permissionCache;
        }

        if ($this->isAdmin()) {
            return $this->permissionCache = ['*'];
        }

        $fromRoles = DB::table('permission_role')
            ->whereIn('role_id', $this->roles->pluck('id')->all())
            ->pluck('permission')->all();

        return $this->permissionCache = array_values(array_unique(array_merge($fromRoles, $this->extraPermissions())));
    }

    public function hasPermission($permission)
    {
        $all = $this->permissions();

        if (in_array($permission, $all, true)) {
            return true;
        }

        // The general manager has everything except the courier-only "own shipments" restriction
        return in_array('*', $all, true) && $permission !== 'scope.own_shipments';
    }

    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /** User who sees only the shipments assigned to them (courier) */
    public function isCourier()
    {
        return $this->hasPermission('scope.own_shipments');
    }

    public function roleLabel()
    {
        return $this->roles->map(function ($r) {
            return __($r->name);
        })->implode(__(', ')) ?: '-';
    }

    /** Can this user manage the target user according to the job hierarchy? */
    public function canManageUser(User $target)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $target->id !== $this->id && $target->rankLevel() > $this->rankLevel();
    }

    /* ---------- Branches ---------- */

    public function canAllBranches()
    {
        return $this->hasPermission('scope.all_branches');
    }

    /** IDs of the branches the user works on (primary + additional) */
    public function branchIds()
    {
        $ids = $this->branches->pluck('id');

        if ($this->branch_id) {
            $ids->push($this->branch_id);
        }

        return $ids->map(function ($id) {
            return (int) $id;
        })->unique()->values()->all();
    }

    public function defaultBranchId()
    {
        if ($this->branch_id) {
            return (int) $this->branch_id;
        }

        $ids = $this->branchIds();

        return $ids ? $ids[0] : null;
    }

    /** Must the user pick the branch manually when creating records? */
    public function needsBranchChoice()
    {
        return $this->canAllBranches() || count($this->branchIds()) > 1;
    }

    public function selectableBranches()
    {
        $query = Branch::where('is_active', true)->orderBy('name');

        if (! $this->canAllBranches()) {
            $query->whereIn('id', $this->branchIds() ?: [0]);
        }

        return $query->get();
    }

    /** Short text describing the user's workplace */
    public function workplaceLabel()
    {
        if ($this->canAllBranches() && $this->work_type !== 'branch') {
            return __(self::WORK_TYPES[$this->work_type] ?? 'All branches');
        }

        $names = $this->branches->pluck('name');
        if ($this->branch && ! $names->contains($this->branch->name)) {
            $names->push($this->branch->name);
        }

        if ($names->isNotEmpty()) {
            return $names->map(function ($n) {
                return __($n);
            })->implode(__(', '));
        }

        return __(self::WORK_TYPES[$this->work_type] ?? '-');
    }
}
