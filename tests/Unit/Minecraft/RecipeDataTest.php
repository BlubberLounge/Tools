<?php

namespace Tests\Unit\Minecraft;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use App\Minecraft\FarmCatalog;
use App\Minecraft\RecipeData;

/**
 * Validates the hand-maintained recipe files in resources/minecraft/recipes:
 * unknown item ids, missing rates and recipe loops.
 */
class RecipeDataTest extends TestCase
{
    /** Items the farm chains need (PF-7); every version must be able to make them. */
    private const REQUIRED_CRAFTABLE = [
        'book', 'paper', 'bookshelf', 'tnt', 'hopper', 'rail', 'powered_rail', 'iron_block', 'gold_block',
        'bread', 'glass', 'emerald', 'redstone', 'experience_bottle', 'dried_kelp_block', 'bone_meal',
        'piston', 'observer', 'white_wool', 'torch',
    ];

    public static function versionFiles(): array
    {
        $files = [];
        foreach (glob(dirname(__DIR__, 3).'/resources/minecraft/recipes/*.json') as $file)
            $files[basename($file)] = [$file];

        return $files;
    }

    #[DataProvider('versionFiles')]
    public function test_version_file_is_valid(string $file): void
    {
        $data = json_decode(file_get_contents($file), true);
        $version = basename($file, '.json');

        $this->assertIsArray($data, basename($file).' is not valid JSON');
        $this->assertSame($version, $data['version'], 'file name and version field differ');
        $this->assertTrue(FarmCatalog::exists($version), "version {$version} has no farm catalogue");

        $farmItems = array_keys(FarmCatalog::load($version)->items());
        $this->assertSame([], RecipeData::validate($data, $farmItems), basename($file).' has validation errors');
    }

    #[DataProvider('versionFiles')]
    public function test_every_file_names_dated_sources(string $file): void
    {
        $data = json_decode(file_get_contents($file), true);

        $this->assertNotEmpty($data['sources']);
        foreach ($data['sources'] as $source) {
            $this->assertStringStartsWith('https://', $source['url']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $source['retrieved']);
        }
    }

    #[DataProvider('versionFiles')]
    public function test_required_items_can_be_made(string $file): void
    {
        $data = RecipeData::load(basename($file, '.json'));

        foreach (self::REQUIRED_CRAFTABLE as $item)
            $this->assertNotEmpty($data->recipesFor($item), "{$data->version()}: nothing makes {$item}");

        // all recipe kinds are covered
        $types = array_unique(array_column($data->recipes(), 'type'));
        sort($types);
        $this->assertSame(['blasting', 'crafting', 'smelting', 'smoking', 'trading'], $types);
    }

    public function test_required_versions_exist(): void
    {
        $this->assertSame(['26.3', '1.21'], RecipeData::versions());
        $this->assertFalse(RecipeData::exists('../26.3'));
    }

    public function test_known_recipes_and_version_differences(): void
    {
        $old = RecipeData::load('1.21');
        $new = RecipeData::load('26.3');

        // the chain became the iron chain in 1.21.9
        $this->assertTrue($old->hasItem('chain'));
        $this->assertFalse($old->hasItem('iron_chain'));
        $this->assertTrue($new->hasItem('iron_chain'));
        $this->assertSame('Iron Chain', $new->itemName('iron_chain'));

        foreach ([$old, $new] as $data) {
            $book = $data->recipe('book');
            $this->assertSame(['paper' => 3, 'leather' => 1], array_column($book['inputs'], 'count', 'item'));
            $this->assertSame(['gunpowder' => 5, 'sand' => 4], array_column($data->recipe('tnt')['inputs'], 'count', 'item'));
            $this->assertSame(10, $data->recipe('glass')['seconds']);
            $this->assertSame(5, $data->recipe('iron_ingot_from_blasting_raw_iron')['seconds']);

            $paperTrade = $data->recipe('trade_librarian_paper_emerald');
            $this->assertSame(24, $paperTrade['inputs'][0]['count']);
            $this->assertSame(16, $paperTrade['max_uses']);

            $this->assertEquals(8.0, $data->fuels()['coal']);
            $this->assertSame(['fuel' => 'coal', 'restocks_per_day' => 2, 'hopper_items_per_hour' => 9000], $data->defaults());
        }
    }

    public function test_validation_reports_unknown_items_and_missing_rates(): void
    {
        $data = $this->minimal();
        $data['recipes'][] = ['id' => 'bad_smelt', 'type' => 'smelting', 'output' => ['item' => 'b', 'count' => 1], 'inputs' => [['item' => 'nope', 'count' => 1]]];
        $data['recipes'][] = ['id' => 'bad_trade', 'type' => 'trading', 'output' => ['item' => 'b', 'count' => 1], 'inputs' => [['item' => 'a', 'count' => 1]], 'profession' => 'farmer', 'level' => 'novice'];
        $data['fuels'][] = ['item' => 'b', 'smelts' => 0];

        $errors = implode("\n", RecipeData::validate($data));

        $this->assertStringContainsString("recipes[bad_smelt].inputs[0] names unknown item 'nope'", $errors);
        $this->assertStringContainsString('recipes[bad_smelt]: missing field \'seconds\'', $errors);
        $this->assertStringContainsString('recipes[bad_smelt].seconds is missing', $errors);
        $this->assertStringContainsString('recipes[bad_trade].max_uses is missing', $errors);
        $this->assertStringContainsString('fuels[1].smelts is missing', $errors);
    }

    public function test_validation_reports_recipe_loops(): void
    {
        // c is only made from d and d only from c: no way down to a raw item
        $data = $this->minimal();
        $data['items'][] = ['id' => 'c', 'name' => 'C'];
        $data['items'][] = ['id' => 'd', 'name' => 'D'];
        $data['recipes'][] = ['id' => 'c_from_d', 'type' => 'crafting', 'output' => ['item' => 'c', 'count' => 1], 'inputs' => [['item' => 'd', 'count' => 1]]];
        $data['recipes'][] = ['id' => 'd_from_c', 'type' => 'crafting', 'output' => ['item' => 'd', 'count' => 1], 'inputs' => [['item' => 'c', 'count' => 1]]];

        $errors = RecipeData::validate($data);
        $this->assertContains("recipe loop: 'c' cannot be made without itself", $errors);
        $this->assertContains("recipe loop: 'd' cannot be made without itself", $errors);

        // a farm makes c: then the loop is fine
        $this->assertSame([], RecipeData::validate($data, ['c']));

        // a loop that multiplies items out of nothing
        $data = $this->minimal();
        $data['recipes'][] = ['id' => 'a_from_b', 'type' => 'crafting', 'output' => ['item' => 'a', 'count' => 9], 'inputs' => [['item' => 'b', 'count' => 1]]];
        $data['recipes'][0]['output']['count'] = 1;
        $data['recipes'][0]['inputs'][0]['count'] = 1;
        $errors = implode("\n", RecipeData::validate($data, ['a']));
        $this->assertStringContainsString('is made out of nothing', $errors);
    }

    public function test_real_loops_are_allowed(): void
    {
        // ingot to block and back, kelp smelted with dried kelp blocks
        $data = RecipeData::load('26.3');
        $this->assertNotNull($data->recipe('iron_ingot_from_iron_block'));
        $this->assertNotNull($data->recipe('iron_block'));
        $this->assertNotNull($data->recipe('dried_kelp'));
        $this->assertNotNull($data->recipe('dried_kelp_block'));
    }

    public function test_unknown_versions_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RecipeData::load('9.9');
    }

    /** Smallest valid file: a and b, b made from a. */
    private function minimal(): array
    {
        return [
            'version' => '1.21',
            'name' => 'Test',
            'updated' => '2026-10-15',
            'sources' => [['title' => 'Test', 'url' => 'https://minecraft.wiki/w/Crafting', 'retrieved' => '2026-10-15']],
            'defaults' => ['fuel' => 'a', 'restocks_per_day' => 2, 'hopper_items_per_hour' => 9000],
            'fuels' => [['item' => 'a', 'smelts' => 8]],
            'items' => [['id' => 'a', 'name' => 'A'], ['id' => 'b', 'name' => 'B']],
            'recipes' => [
                ['id' => 'b', 'type' => 'crafting', 'output' => ['item' => 'b', 'count' => 1], 'inputs' => [['item' => 'a', 'count' => 2]]],
            ],
        ];
    }
}
