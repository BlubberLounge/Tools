<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Cocktail;
use App\Models\Ingredient;
use App\Models\Recipe;

class CocktailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rootUser = User::getRootUser();

        $cocktails = Cocktail::factory(10)
        ->has(
            Recipe::factory(rand(2, 6))
                ->has(
                    Ingredient::factory(rand(1, 2)),
                    'ingredients'
                ),
            'recipes'
        )
        ->for(User::inRandomOrder()->first('id'), 'createdBy')
        ->create();

        foreach ($cocktails as $cocktail) {
            $cocktail->recipes->each(function ($recipe, $index) {
                $recipe->update(['order' => $index + 1]);
            });
        }

        // Probably could be done better
        // for($i = 0; $i < $numberOfCocktails; $i++)
        // {


        //     // $users = User::inRandomOrder()->whereNot('id', $rootUser->id)->limit(10)->get();
        //     // $users->push($rootUser);
        // }
    }
}
