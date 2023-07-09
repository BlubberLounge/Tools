<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Permission Types
         *
         */
        $permissions = [
            // User Model permissions
            [
                'name'        => 'Can View Users Timetable',
                'slug'        => 'view.users.timetable',
                'description' => 'Can view users timetable',
                'model'       => 'Permission',
            ],

            // Timetable Model permissions
            [
                'name'        => 'Can View Timetable',
                'slug'        => 'view.timetable',
                'description' => 'Can view timetable',
                'model'       => 'Permission',
            ],
            [
                'name'        => 'Can Create Timetable',
                'slug'        => 'create.timetable',
                'description' => 'Can create timetable',
                'model'       => 'Permission',
            ],
            [
                'name'        => 'Can Update Timetable',
                'slug'        => 'update.timetable',
                'description' => 'Can update timetable',
                'model'       => 'Permission',
            ],
            [
                'name'        => 'Can Edit Timetable',
                'slug'        => 'edit.timetable.homeview',
                'description' => 'Can edit timetable on home view',
                'model'       => 'Permission',
            ],
        ];

        /*
         * Add Permission Items
         *
         */
        foreach ($permissions as $item) {
            $newItem = config('roles.models.permission')::where('slug', $item['slug'])->first();

            if ($newItem === null)
                $newItem = config('roles.models.permission')::create([
                    'name'          => $item['name'],
                    'slug'          => $item['slug'],
                    'description'   => $item['description'],
                    'model'         => $item['model'],
                ]);
        }
    }
}
