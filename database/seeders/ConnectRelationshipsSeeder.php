<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use jeremykenedy\LaravelRoles\Models\Role;

class ConnectRelationshipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Get Available Permissions.
         */
        $permissions = config('roles.models.permission')::all();

        /**
         * Attach Permissions to Roles.
         */
        $roleRoot = config('roles.models.role')::where('slug', 'root')->first();
        $roleDeveloper = config('roles.models.role')::where('slug', 'developer')->first();
        $roleAdmin = config('roles.models.role')::where('slug', 'admin')->first();

        foreach ($permissions as $permission) {
            $roleRoot->attachPermission($permission);
            $roleDeveloper->attachPermission($permission);
            $roleAdmin->attachPermission($permission);
            $this->command->getOutput()->writeln(
                '<info>Seeding:</info> ConnectRelationshipsSeeder - Role:Root, Role:Developer, Role:Admin  attached to Permission:'
                . $permission->slug
            );
        }


        $roleDartMaster = config('roles.models.role')::where('slug', 'dart.master')->first();
        $roleDartPlayer = config('roles.models.role')::where('slug', 'dart.player')->first();
        $roleTrusted = config('roles.models.role')::where('slug', 'trusted')->first();
        $roleUser = config('roles.models.role')::where('slug', 'user')->first();
        $roleUnverified = config('roles.models.role')::where('slug', 'unverified')->first();

        // Unverified
        $this->attachRoleToPermissions($roleUnverified, [
            'update.user.language',
        ]);

        // User
        $this->attachRoleToPermissions($roleUser, [
            'viewany.device',
            'view.moving.average',
            'create.feedback',
            'viewany.f.a.q',
            'view.airsoft',
            'view.iec7064',
            'viewany.cocktail'
        ], [$roleUnverified]);

        // Trusted
        $this->attachRoleToPermissions($roleTrusted, [
            'view.cocktail',
            'create.cocktail',
            'update.cocktail'
        ], [$roleUser]);

        // Dart - Player
        $this->attachRoleToPermissions($roleDartPlayer, [
            'viewany.dart',
            'view.dart.info',
            'view.dart.checkouts',
            'view.dart.game',
        ]);

        // Dart Master
        $this->attachRoleToPermissions($roleDartMaster, [

        ], [$roleDartPlayer]);
    }

    /**
     * Attach specific permissions and optionally copy permissions from other roles.
     *
     * @param Role $role
     * @param array $permissions
     *        - Array of permission slugs to be directly attached to the role.
     * @param array $copyRoles
     *        - Array of role slugs whose permissions will be copied to this role.
     * @return void
     */
    private function attachRoleToPermissions(Role $role, array $permissions, array $copyRoles = []): void
    {
        $permissions = config('roles.models.permission')::whereIn('slug', $permissions)->get();
        foreach ($permissions as $permission) {
            $role->attachPermission($permission);
            $this->command->getOutput()->writeln(
                "\t".'<info>Seeding:</info> ConnectRelationshipsSeeder - Role:'. $role->name .' attached to Permission:'. $permission->slug
            );
        }

        // Copy permissions from other roles to this role
        foreach ($copyRoles as $copiedRole) {
            foreach ($copiedRole->permissions as $permission) {
                $role->attachPermission($permission);
                $this->command->getOutput()->writeln(
                    "\t".'<question>Seeding:</question> ConnectRelationshipsSeeder - Role:' . $role->name
                    . ' inherited Permission: ' . $permission->slug . ' from Role: ' . $copiedRole->name
                );
            }
        }
    }
}
