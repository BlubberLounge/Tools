<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\DartGame;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = config('roles.models.permission')::all();

        // always create one root user, that root user has always to be verified
        if(User::where('email', 'admin+Tools@blubber-lounge.de')->first() === null) {
            $rootUser = User::create([
                'name' => 'Root',
                'firstname' => 'Blubber',
                'lastname' => 'Lounge',
                'email' => 'admin+Tools@blubber-lounge.de',
                'dob' => date('Y-m-d H:i:s', strtotime('02.11.1999')),
                'email_verified_at' => now(),
                'password' => Hash::make('123'), // Hash::make('blt-r00t')
            ]);

            $rootUser->attachRole(config('roles.models.role')::where('name', 'Root')->first());

            // foreach ($permissions as $permission)
            //     $adminUser->attachPermission($permission);
        }

        if(User::count() <= 50) {
            $userRole = config('roles.models.role')::where('name', 'User')->first();
            User::factory()
                ->count(rand(10, 18))
                ->hasAttached(
                    DartGame::factory()->count(5)
                )
                ->create()
                ->each( function($user) use ($userRole) {
                    $user->attachRole($userRole);
                });
        }
    }
}
