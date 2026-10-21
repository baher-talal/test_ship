<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesAndPermissionsTables extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->unsignedTinyInteger('level')->default(5); // 1 = highest level in the job hierarchy
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('roles')->onDelete('set null');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('permission', 60);
            $table->primary(['role_id', 'permission']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['role_id', 'user_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('permission', 60);
            $table->primary(['user_id', 'permission']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('branch_user', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['branch_id', 'user_id']);
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            // branch = branch | rotating = rotates between branches | remote = remote | hq = headquarters
            $table->string('work_type', 20)->default('branch');
        });

        // ---- Default roles ----
        $ids = [];
        foreach (config('permissions.default_roles') as $slug => $def) {
            list($name, $level, $parent, $description, $perms) = $def;
            $ids[$slug] = DB::table('roles')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'level' => $level,
                'parent_id' => $parent ? $ids[$parent] : null,
                'description' => $description,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($perms as $perm) {
                DB::table('permission_role')->insert(['role_id' => $ids[$slug], 'permission' => $perm]);
            }
        }

        // ---- Migrate existing users from the old role column ----
        $map = ['admin' => 'admin', 'manager' => 'branch_manager', 'employee' => 'employee', 'courier' => 'courier'];
        foreach (DB::table('users')->get() as $u) {
            $slug = $map[$u->role] ?? 'employee';
            DB::table('role_user')->insert(['role_id' => $ids[$slug], 'user_id' => $u->id]);
            if ($u->branch_id) {
                DB::table('branch_user')->insert(['branch_id' => $u->branch_id, 'user_id' => $u->id]);
            }
            if ($slug === 'admin') {
                DB::table('users')->where('id', $u->id)->update(['work_type' => 'hq']);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropColumn('role');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('employee')->index();
            $table->dropColumn('work_type');
        });

        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
    }
}
