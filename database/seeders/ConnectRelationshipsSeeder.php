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

        $this->attachRoleToPermissions(config('roles.models.role')::where('slug', 'game.master')->first(), [

        ]);

        $this->attachRoleToPermissions(config('roles.models.role')::where('slug', 'user')->first(), [
            'update.user.language',
            'viewany.device',
            'view.moving.average',
            'view.calculator',
            'create.feedback',
            'viewany.f.a.q',
        ]);

        $this->attachRoleToPermissions(config('roles.models.role')::where('slug', 'dart.player')->first(), [
                'viewany.dart',
                'view.dart.info',
                'view.dart.checkouts',
                'view.dart.game',
        ]);


    }

    /**
     *
     */
    private function attachRoleToPermissions(Role $role, array $permissions): void
    {
        $permissions = config('roles.models.permission')::whereIn('slug', $permissions)->get();
        foreach ($permissions as $permission) {
            $role->attachPermission($permission);
            $this->command->getOutput()->writeln(
                '<info>Seeding:</info> ConnectRelationshipsSeeder - Role:'. $role->name .' attached to Permission:'. $permission->slug
            );
        }
    }
}
