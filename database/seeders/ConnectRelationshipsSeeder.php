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
        foreach ($permissions as $permission)
            $roleRoot->attachPermission($permission);

        $roleDeveloper = config('roles.models.role')::where('name', 'Developer')->first();
        foreach ($permissions as $permission)
            $roleDeveloper->attachPermission($permission);

        $roleAdmin = config('roles.models.role')::where('name', 'Admin')->first();
        foreach ($permissions as $permission)
            $roleAdmin->attachPermission($permission);
    }
}
