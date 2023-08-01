<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

use App\Models\DartGame;
use App\Models\DartThrow;
use App\Models\User;
use PhpParser\Node\Stmt\Foreach_;

class DartGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfGames = 25;

        // Probably could be done better
        for($i = 0; $i < $numberOfGames; $i++)
        {
            $playerPerGame = rand(1, 4);
            $turnsPerGame = rand(3, 20);
            $throwsPerUser = 3;

            $game = DartGame::factory(1)->create();

            $users = User::inRandomOrder()->limit($playerPerGame)->get();
            $users->push(User::getRootUser());

            foreach($users as $user) {
                $user->DartGames()->attach($game);

                DartThrow::factory()->count($turnsPerGame * $throwsPerUser)
                    ->for($user)
                    ->sequence( fn (Sequence $sequence) => ['turn' => (($sequence->index/$throwsPerUser) % $turnsPerGame)+1, 'throw' => ($sequence->index % $throwsPerUser)+1] )
                    ->make() // alternative: ->create()
                    ->each( function (DartThrow $throw) use ($game) { $throw->dart_game_id = $game[0]->id; $throw->save();});
                    // alternative: ->each( fn (DartThrow $throw) => $throw->update(['dart_game_id' => $game[0]->id]) );
            }
        }

    }
}
