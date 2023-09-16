<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
        $roleRoot = config('roles.models.role')::where('name', 'Root')->first();
        $roleDeveloper = config('roles.models.role')::where('name', 'Developer')->first();
        $roleAdmin = config('roles.models.role')::where('name', 'Admin')->first();

        // foreach ($permissions as $permission) {
        //     $roleRoot->attachPermission($permission);
        //     $roleDeveloper->attachPermission($permission);
        //     $roleAdmin->attachPermission($permission);
        //     $this->command->getOutput()->writeln(
        //         '<info>Seeding:</info> ConnectRelationshipsSeeder - Role:Root, Role:Developer, Role:Admin  attached to Permission:'
        //         . $permission->slug
        //     );
        // }


        $roleDartPlayer = config('roles.models.role')::where('name', 'Dart Player')->first();
        $permissionsDartPlayer = [
            'viewany.dart'
        ];

        foreach ($permissionsDartPlayer as $permissionSlug) {
            $permission = config('roles.models.permission')::where('slug', $permissionSlug)->first();
            $roleDartPlayer->attachPermission($permission);

            $this->command->getOutput()->writeln(
                '<info>Seeding:</info> ConnectRelationshipsSeeder - Role:Dart Player  attached to Permission:'
                . $permission->slug
            );
        }
    }
}
