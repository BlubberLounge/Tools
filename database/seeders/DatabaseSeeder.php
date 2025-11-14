<?php

namespace Database\Seeders;

use App\Models\DartGame;
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
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            ConnectRelationshipsSeeder::class,

            UserSeeder::class,

            ManufacturerSeeder::class,
            HookahSeeder::class,
            HookahHeadSeeder::class,
            TobaccoSeeder::class,

            // CocktailSeeder::class,
            RealDataSeeder::class,

            DartGameSeeder::class,
        ]);

        Model::reguard();
    }
}
