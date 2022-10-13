<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

use App\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Schema::disableForeignKeyConstraints();
        // delete all role table columns and reset the increment counter
        Role::truncate();
        Schema::enableForeignKeyConstraints();

        /* 
         * User Roles / Permissions
         * Permissions are defined with Laravel Policies system
         */
        Role::create([
            'id' => Role::ADMIN,
            'name' => 'Admin',
            'description' => 'Has the most permissions. should be the highest role.',
        ]);
        
        Role::create([
            'id' => Role::USER,
            'name' => 'User',
            'description' => 'Just a normal Application user.',
        ]);
        
        Role::create([
            'id' => Role::GUEST,
            'name' => 'Guest',
            'description' => 'Basicly has no rights at all.',
        ]);
        
        // always create one initial admin / root user
        User::create([
            'name' => 'Admin',
            'firstname' => 'Blubber',
            'lastname' => 'Lounge',
            'email' => 'admin@blubber-lounge.de',
            'role_id' => 1,
            'password' => Hash::make('123'),
        ]);
    }
}
