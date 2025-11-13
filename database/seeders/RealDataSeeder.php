<?php

namespace Database\Seeders;

use App\Enums\Units;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Cocktail;
use App\Models\Ingredient;
use App\Models\Recipe;
use Exception;

class RealDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->runWithRealData();
    }

    protected function runWithRealData()
    {
        $cocktails = $this->realData();

        foreach ($cocktails as $cocktailData) {
            $cocktail = new Cocktail();
            $cocktail->name = $cocktailData['name'];
            $cocktail->description = $cocktailData['description'];
            $cocktail->image = $cocktailData['image'];
            $cocktail->created_by = $cocktailData['created_by'];
            $cocktail->save();
            $cocktail->rate($this->calculateAverageRating($cocktailData['rating']), null, 1);

            $firstRecipe = null;
            foreach ($cocktailData['steps'] as $i =>  $step) {
                if (!empty($step)) {
                    $recipe = new Recipe();
                    $recipe->description = $step;
                    $recipe->order = $i;
                    $recipe->created_by = $cocktailData['created_by'];
                    $cocktail->recipes()->save($recipe);
                    if($i == 0)
                        $firstRecipe = $recipe;
                }
            }

            foreach ($cocktailData['ingredients'] as $ingredientData) {
                if (!empty($ingredientData) && $firstRecipe != null) {
                    $ingredient = new Ingredient();
                    $ingredient->quantity = $ingredientData[0];
                    $ingredient->unit_of_measurement = $ingredientData[1];
                    $ingredient->name = $ingredientData[2] ?? 'no name';
                    $firstRecipe->ingredients()->save($ingredient);
                }
            }

            // if(!empty($cocktailData['tags'])) {
            //     $cocktail->syncTags($cocktailData['tags']);
            // }
        }
    }

    /**
     * Berechnet die durchschnittliche Bewertung.
     *
     * @param array $ratings
     * @return float
     */
    protected function calculateAverageRating(array $ratings): float
    {
        // Die Summe der Bewertungen berechnen
        $ratingSum = 0;
        $totalRatings = 0;

        foreach ($ratings as $stars => $count) {
            $ratingSum += $stars * $count;
            $totalRatings += $count;
        }

        return $totalRatings > 0 ? $ratingSum / $totalRatings : 0;
    }

    protected function realData(): array
    {
        return [
            // Runde 0
            [
                'name' => 'Mojito',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 3, 2, 1
                ],
                'ingredients' => [
                    [5, Units::CL, 'Rum (weiß, z.b. Bacardi od. Havana Club)'],
                    [6, Units::CL, 'Soda zum Auffüllen'],
                    [1, Units::PIECE, 'Limette'],
                    [4, Units::TABLE_SPOON, 'Crushed Ice'],
                    [2, Units::TEA_SPOON, 'brauner Zucker (Rohrzucker)'],
                    [8, Units::PIECE, 'Minze (frisch)'],
                ],
                'steps' => [
                    'Die unbehandelten Limetten waschen und die Limettenenden abschneiden. Nun die Limetten achteln und zusammen mit dem braunen Zucker und der Minze in ein Longdrinkglas geben.',
                    'Die Limettenstücke und auch die Minzblätter mit einem Stößel oder Pistill etwas zerdrücken - aber nicht zu viel also nicht komplett zerquetschen.',
                    'Zum Schluss das Glas mit den Crushed Ice füllen, den weißen Rum dazu und das Glas mit dem Soda auffüllen.',
                    'Der Mojito kann beliebig mit ein paar Limettenscheiben und Minzblätter garniert werden.'
                ],
                'tags' => [
                    'rum', 'lime', 'fresh', 'refreshing'
                ]
            ],
            [
                'name' => 'Blue dream',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 3, 1, 2
                ],
                'ingredients' => [
                    [2, Units::CL, 'Gin'],
                    [2, Units::CL, 'Curaçao blue'],
                    [1, Units::CL, 'Zitronensaft'],
                    [100, Units::ML, 'Sodawasser zum Auffüllen'],
                ],
                'steps' => [
                    'Einige Eiswürfel in einen Shaker geben.',
                    'Gin, Curaçao blue und Zitronensaft zufügen und schütteln.',
                    'Alles in ein hohes Longdrinkglas geben und mit gut gekühltem Sodawasser auffüllen.'
                ],
                'tags' => [
                    'gin', 'curacao blue', 'tropical', 'refreshing'
                ]
            ],
            [
                'name' => 'Gintonic + Lovehandle (custom von jannis)',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 2, 3, 1
                ],
                'ingredients' => [
                    [1, Units::PIECE, 'nothing'],
                ],
                'steps' => [
                    'nothing',
                ],
                'tags' => [

                ]
            ],
            [
                'name' => 'Tequila Sunrise',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 0, 6, 0
                ],
                'ingredients' => [
                    [5, Units::PIECE, 'Eiswürfel'],
                    [4, Units::CL, 'Tequila'],
                    [1, Units::CL, 'Zitronensaft'],
                    [11, Units::CL, 'Orangensaft'],
                    [1, Units::CL, 'Grenadinesirup'],
                    [1, Units::WEDGE, 'Zitronenspalte'],
                ],
                'steps' => [
                    'Den Shaker mit 5 Eiswürfeln füllen, dann Tequila, Zitronensaft und den Orangensaft hinzufügen und ca. 15 Sek. kräftig schütteln.',
                    'In ein Longdrinkglas etwas Crushed Ice geben und den Drink über ein Barsieb abgießen.',
                    'Nun den Grenadinesirup vorsichtig über einen Löffelrücken in das Glas gießen - so erhält man den sogenannten Sunrise-Effekt (=typischen Farbverlauf von tiefem Rot über Orange zu Gelb).',
                    'Nicht umrühren, warten bis der Sirup auf dem Glasboden ist und anschließend mit Trinkhalm und einer Orangenscheibe sowie Cocktailkirsche servieren.'
                ],
                'tags' => [
                    'tequila', 'fresh', 'fruity', 'strong'
                ]
            ],
            [
                'name' => 'Strawberry Dequirrie',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 5, 0, 0, 1
                ],
                'ingredients' => [
                    [3, Units::TABLE_SPOON, 'Crushed Ice'],
                    [8, Units::PIECE, 'Erdbeeren (klein)'],
                    [4, Units::CL, 'Rum'],
                    [2, Units::CL, 'Limettensaft (frisch gepresst)'],
                    [2, Units::CL, 'Strawberry Liqueur'],
                ],
                'steps' => [
                    'In einen Mixer etwas Crushed Ice mit den Erdbeeren fein zerkleinern. Anschließend die restlichen Zutaten Rum, Limettensaft und den Erdbeerlikör zufügen und gut shaken.',
                    'Den Drink in ein Cocktailglas gießen und mit einem Trinkhalm servieren.',
                    'Eventuell mit Erdbeerstücken und einem Minzblatt am Glasrand garnieren.'
                ],
                'tags' => [
                    'tequila', 'fresh', 'fruity', 'strong'
                ]
            ],
            [
                'name' => 'Malibu Paloma',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    4, 2, 0, 0, 0
                ],
                'ingredients' => [
                    [50, Units::ML, 'Malibu Original'],
                    [100, Units::ML, 'Pink Grapefruit-Limonade'],
                    [15, Units::ML, 'Limettensaft'],
                    [10, Units::GRAMM, 'Salz für Salzrand'],
                    [1, Units::PIECE, 'Grapefruit'],
                    [5, Units::PIECE, 'Eis'],
                ],
                'steps' => [
                    'Glasrand anfeuchten und in Salz tauchen',
                    'Alle Zutaten mit Eiswürfeln ins Glas geben und genießen!'
                ],
                'tags' => [
                    'malibu original', 'grapefruit', 'salty', 'tropical'
                ]
            ],
            [
                'name' => 'Malibu Mojito',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 0, 4, 0
                ],
                'ingredients' => [
                    [45, Units::ML, 'Malibu Original'],
                    [15, Units::ML, 'Limettensaft'],
                    [135, Units::ML, 'Sodawasser'],
                    [10, Units::PIECE, 'Minzblätter'],
                ],
                'steps' => [
                    'Gebe Pfefferminzblätter in ein Longdrinkglas und fülle es mit Eiswürfeln.',
                    'Gib Malibu und Limettensaft hinzu.',
                    'Fülle auf mit Sodawasser und garniere den Drink mit einem Pfefferminzblatt und einer Limettenspalte.'
                ],
                'tags' => [
                    'malibu original', 'lime', 'fresh', 'refreshing'
                ]
            ],

            // Runde 1
            [
                'name' => 'Balles flat white martini',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    2, 1, 3, 2, 1
                ],
                'ingredients' => [
                    [50, Units::ML, 'Baileys Original Irish Cream'],
                    [2, Units::SHOT, 'frischer Espresso'],
                    [25, Units::ML, 'Smirnoff Vodka'],
                    [3, Units::PIECE, 'Kaffeebohnen zum garnieren'],
                    [5, Units::PIECE, 'Eis'],
                ],
                'steps' => [
                    'Cocktail-Shaker mit Eis füllen.',
                    'Gieße 50ml Baileys Original Irish Cream, 25ml Smirnoff Vodka und 1-2 Shots kalten Espresso hinein.',
                    'Ordentlich durchschütteln und in ein Cocktailglas abgießen.',
                    'Mit 3 Kaffeebohnen garnieren und servieren!'
                ],
                'tags' => [
                    'baileys', 'vodka', 'creamy', 'coffee'
                ]
            ],
            [
                'name' => 'Erbeer Mochjito',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 5, 1, 2, 1
                ],
                'ingredients' => [
                    [20, Units::ML, 'Limettensaft'],
                    [2, Units::TEA_SPOON, 'Rohrzucker'],
                    [8, Units::PIECE, 'Minze'],
                    [50, Units::GRAMM, 'Erdbeeren'],
                    [40, Units::ML, 'Rum weiß'],
                    [60, Units::ML, 'Erdbeersaft'],
                    [50, Units::GRAMM, '	Crushed Ice'],
                ],
                'steps' => [
                    'Limettensaft, Rohrzucker und ein paar Minze-Blätter und die Erdbeeren in einen Behälter geben und mit einem Mixer kurz durchmixen.',
                    'Anschließend diese Erdbeermischung in ein Glas geben. Mit Crushed Ice auffüllen und mit Rum und Erdbeersaft aufgießen.',
                    'Mit frischen Erdbeeren und mit Minze dekorieren.'
                ],
                'tags' => [
                    'rum', 'strawberry', 'mint', 'fresh', 'refreshing'
                ]
            ],
            [
                'name' => 'Moscow Mule',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 1, 4, 5
                ],
                'ingredients' => [
                    [1, Units::PIECE, 'Limette (ausgepresst)'],
                    [5, Units::CL, 'Wodka'],
                    [10, Units::PIECE, 'Eiswürfel'],
                    [320, Units::ML, 'Ginger Beer zum Auffüllen'],
                    [3, Units::WEDGE, 'Limette'],
                    [1, Units::PIECE, 'Minzezweig (klein und frisch)'],
                ],
                'steps' => [
                    'Für den Moscow Mule zuerst die Limette auspressen.',
                    'In den Kupferbecher den Wodka einfüllen, dann sofort mit Eiswürfel füllen. Jetzt den Limettensaft über das Eis gießen.',
                    'Mit Ginger Beer auffüllen, vorsichtig mit einem Barlöffel umrühren.',
                    'Limettenspalten und Minzblätter zum Garnieren verwenden.'
                ],
                'tags' => [
                    'vodka', 'ginger beer', 'mint', 'refreshing'
                ]
            ],
            [
                'name' => 'Martini classic',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    4, 0, 2, 3, 1
                ],
                'ingredients' => [
                    [1, Units::PIECE, '(möglichst vorgekühlter) Martinikelch (15 cl)'],
                    [7, Units::CL, 'Gin oder Wodka'],
                    [1, Units::CL, 'trockener Wermut'],
                    [1, Units::PIECE, 'grüne Olive mit Kern (in Salzlake) oder'],
                    [1, Units::TWIST, '1 Bio-Zitrone (ca. 4 cm lang und 2 cm breit)'],
                ],
                'steps' => [
                    'Das Rührglas zu drei Vierteln mit Eiswürfeln füllen. Gin oder Wodka und den Wermut dazugeben. Alles so lange mit dem Barlöffel verrühren, bis das Rührglas beschlägt.',
                    'Den Inhalt des Rührglases durch das Barsieb in den Martinikelch gießen. Je nach Geschmack die Olive ins Glas geben oder den Glasrand mit der Innenseite der Zitronenschale einreiben und diese mit ins Cocktailglas geben.'
                ],
                'tags' => [
                    'gin', 'dry vermouth', 'elegant', 'classic'
                ]
            ],
            [
                'name' => 'Custom von Lukas (Weißer Rum, Orangensaft, Spezi, Orangenscheibe, Rohrzucker)',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    1, 2, 4, 1, 1
                ],
                'ingredients' => [
                    [1, Units::PIECE, 'Fantasie'],
                ],
                'steps' => [
                    'Alles zusammen',
                ],
                'tags' => [
                    //
                ]
            ],
            [
                'name' => 'Swimming Pool',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    0, 0, 0, 5, 5
                ],
                'ingredients' => [
                    [2, Units::PART, 'Wodka'],
                    [1, Units::PART, 'blauer Curaçao'],
                    [1, Units::PART, '1/2 Kokoscreme'],
                    [2, Units::PART, 'Sahne'],
                    [4, Units::PART, '4 1/2 Apfelsaft'],
                    [8, Units::PIECE, 'Eis'],
                ],
                'steps' => [
                    'Wodka, Kokoscreme, Eis, Sahne, Apfelsaft und blauen Curaçao in Shaker geben',
                    'schütteln',
                    'durch ein Sieb in Hurricaneglas gießen',
                    'garnieren'
                ],
                'tags' => [
                    'vodka', 'curacao blue', 'tropical', 'creamy'
                ]
            ],
            [
                'name' => 'Sunshine Fizz',
                'slug' => '',
                'description' => fake()->text(),
                'image' => 'img_compressed_and_resized/cocktail0.jpg',
                'created_by' => 1,
                'rating' => [
                    2, 2, 1, 1, 3
                ],
                'ingredients' => [
                    [4, Units::CL, 'Jim Beam® Sunshine Blend'],
                    [14, Units::CL, 'Grapefruit Limonade, z.B. von Thomas Henry'],
                    [8, Units::PIECE, 'Eis'],
                    [1, Units::PIECE, 'Grapefruit'],
                ],
                'steps' => [
                    'Fülle ein Glas mit Eiswürfeln.',
                    'Gib Jim Beam Sunshine Blend hinzu.',
                    'Fülle mit Pink Grapefruit auf.',
                    'Garniere mit einer Scheibe Grapefruit, cheers!'
                ],
                'tags' => [
                    'whisky', 'citrus', 'sparkling', 'fresh'
                ]
            ]
        ];
    }
}
