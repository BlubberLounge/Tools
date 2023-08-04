<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Role Types
         *
         */
        $RoleItems = [
            [
                // basicly Root user => ONLY ONE SUPER-ADMIN/ROOT SHOULD EXIST
                'name'        => 'Root',
                'slug'        => 'root',
                'description' => 'Root Role - ONLY ONE ROOT USER SHOULD EXIST',
                'level'       => 10,
            ],
            [
                'name'        => 'Developer',
                'slug'        => 'developer',
                'description' => 'Developer Role',
                'level'       => 6,
            ],
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Admin Role',
                'level'       => 5,
            ],
            [
                'name'        => 'Trusted',
                'slug'        => 'trusted',
                'description' => 'Trusted User Role',
                'level'       => 2,
            ],
            [
                'name'        => 'User',
                'slug'        => 'user',
                'description' => 'User Role - default role',
                'level'       => 1,
            ],
            [
                'name'        => 'Unverified',
                'slug'        => 'unverified',
                'description' => 'Unverified Role',
                'level'       => 0,
            ],
        ];

        /*
         * Add Role Items
         *
         */
        foreach ($RoleItems as $RoleItem) {
            $newRoleItem = config('roles.models.role')::where('slug', $RoleItem['slug'])->first();
            if ($newRoleItem === null) {
                $newRoleItem = config('roles.models.role')::create([
                    'name'          => $RoleItem['name'],
                    'slug'          => $RoleItem['slug'],
                    'description'   => $RoleItem['description'],
                    'level'         => $RoleItem['level'],
                ]);
            }
        }
    }
}
