<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            ManufacturerSeeder::class,
            HookahSeeder::class,
            HookahHeadSeeder::class,
            TobaccoSeeder::class,

            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            ConnectRelationshipsSeeder::class,

            UserSeeder::class,
        ]);

        Model::reguard();
    }
}
