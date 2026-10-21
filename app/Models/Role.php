<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'level', 'parent_id', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean', 'level' => 'integer'];

    public function parent()
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Role::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function isAdmin()
    {
        return $this->slug === 'admin';
    }

    /** Permission keys granted to this role */
    public function permissionKeys()
    {
        if ($this->isAdmin()) {
            return Permissions::adminGrants();
        }

        return DB::table('permission_role')->where('role_id', $this->id)->pluck('permission')->all();
    }

    public function syncPermissions(array $keys)
    {
        $valid = array_values(array_intersect(array_unique($keys), Permissions::keys()));

        DB::table('permission_role')->where('role_id', $this->id)->delete();
        foreach ($valid as $key) {
            DB::table('permission_role')->insert(['role_id' => $this->id, 'permission' => $key]);
        }
    }
}
