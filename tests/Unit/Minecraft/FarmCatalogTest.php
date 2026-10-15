<?php

namespace Tests\Unit\Minecraft;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use App\Minecraft\FarmCatalog;

/**
 * Validates the hand-maintained farm catalogues in resources/minecraft/farms
 * and pins the filter and sort rules of the catalogue page.
 */
class FarmCatalogTest extends TestCase
{
    /** Farm types the chain planner needs most (PF-8). */
    private const REQUIRED_TYPES = [
        'iron', 'gold', 'sugar_cane', 'bamboo', 'kelp', 'cactus', 'bone', 'gunpowder', 'string',
        'slime', 'wool', 'leather', 'wither_skeleton', 'enderman', 'tree', 'crops', 'trading_hall',
    ];

    public static function versionFiles(): array
    {
        $files = [];
        foreach (glob(dirname(__DIR__, 3).'/resources/minecraft/farms/*.json') as $file)
            $files[basename($file)] = [$file];

        return $files;
    }

    #[DataProvider('versionFiles')]
    public function test_version_file_is_valid(string $file): void
    {
        $data = json_decode(file_get_contents($file), true);

        $this->assertIsArray($data, basename($file).' is not valid JSON');
        $this->assertSame([], FarmCatalog::validate($data), basename($file).' has validation errors');
        $this->assertSame(basename($file, '.json'), $data['version'], 'file name and version field differ');
    }

    #[DataProvider('versionFiles')]
    public function test_every_design_names_its_source_and_author(string $file): void
    {
        $data = json_decode(file_get_contents($file), true);

        foreach ($data['designs'] as $design) {
            $this->assertStringStartsWith('https://', $design['source']['url'], $design['id']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $design['source']['retrieved'], $design['id']);
            $this->assertNotSame('', trim($design['author']), $design['id']);
            $this->assertNotSame('', trim($design['condition']), $design['id']);
            $this->assertArrayHasKey('model', $design, $design['id'].' keeps the model slot for the 3D viewer');
        }
    }

    public function test_required_versions_exist_and_latest_is_default(): void
    {
        $versions = FarmCatalog::versions();

        $this->assertContains('1.21', $versions);
        $this->assertContains('26.3', $versions);
        // 26.3 is the latest Java release (Minecraft Wiki, 2026-10-15)
        $this->assertSame('26.3', FarmCatalog::latestVersion());
        $this->assertSame('26.3', $versions[0], 'versions are sorted newest first');
    }

    public function test_latest_version_covers_the_required_farm_types(): void
    {
        $catalog = FarmCatalog::load(FarmCatalog::latestVersion());

        $this->assertGreaterThanOrEqual(25, count($catalog->designs()));
        $types = array_unique(array_column($catalog->designs(), 'type'));
        foreach (self::REQUIRED_TYPES as $type)
            $this->assertContains($type, $types, "no design of type '{$type}'");
    }

    public function test_versions_differ_where_the_game_differs(): void
    {
        $old = FarmCatalog::load('1.21');
        $new = FarmCatalog::load('26.3');

        // 26.3-only design and a design that stopped at 1.21.10
        $this->assertNull($old->design('saehan-sprinter-golem-iron-farm'));
        $this->assertNotNull($new->design('saehan-sprinter-golem-iron-farm'));
        $this->assertNotNull($old->design('theysix-infinite-string-farm'));
        $this->assertNull($new->design('theysix-infinite-string-farm'));

        // gold farms dropped XP in 1.21.5
        $this->assertSame(115000.0, $old->rate('ilmango-gold-xp-farm', 'experience'));
        $this->assertNull($new->rate('ilmango-gold-xp-farm', 'experience'));

        // chain was renamed to iron_chain in 1.21.9
        $this->assertNotNull($old->item('chain'));
        $this->assertNull($old->item('iron_chain'));
        $this->assertNotNull($new->item('iron_chain'));
    }

    public function test_measured_rates_are_marked_as_max_measurements(): void
    {
        $catalog = FarmCatalog::load('26.3');
        $cactus = $catalog->design('cuyiswild-expandable-cactus-farm');

        $this->assertSame('measured', $cactus['produces'][0]['basis']);
        $this->assertSame(2150.0, $catalog->rate('cuyiswild-expandable-cactus-farm', 'cactus'));
        $this->assertStringContainsString('Measured by Max', $cactus['produces'][0]['note']);
    }

    public function test_unknown_rates_stay_null(): void
    {
        $catalog = FarmCatalog::load('26.3');

        $this->assertNull($catalog->rate('saehan-sprinter-golem-iron-farm', 'iron_ingot'));
        $this->assertSame('unknown', $catalog->design('saehan-sprinter-golem-iron-farm')['produces'][0]['basis']);
        // a design that does not make the item at all
        $this->assertNull($catalog->rate('booty-3-pod-iron-farm', 'cactus'));
    }

    // ---- validator ----

    private function validData(): array
    {
        return json_decode(file_get_contents(FarmCatalog::directory().'/26.3.json'), true);
    }

    private function assertHasError(array $data, string $needle): void
    {
        $errors = FarmCatalog::validate($data);
        foreach ($errors as $error)
            if (str_contains($error, $needle)) {
                $this->addToAssertionCount(1);
                return;
            }

        $this->fail("No error containing '{$needle}'. Errors: ".implode(' | ', $errors));
    }

    public function test_validator_rejects_unknown_item_ids(): void
    {
        $data = $this->validData();
        $data['designs'][0]['produces'][0]['item'] = 'unobtainium';
        $this->assertHasError($data, "unknown item 'unobtainium'");

        $data = $this->validData();
        $data['designs'][0]['materials'][0]['item'] = 'mithril_block';
        $this->assertHasError($data, "unknown item 'mithril_block'");
    }

    public function test_validator_rejects_missing_rates(): void
    {
        $data = $this->validData();
        unset($data['designs'][0]['produces'][0]['per_hour']);
        $this->assertHasError($data, "missing field 'per_hour'");

        $data = $this->validData();
        $data['designs'][0]['produces'][0]['per_hour'] = null;
        $data['designs'][0]['produces'][0]['basis'] = 'designer';
        $this->assertHasError($data, 'per_hour is missing');

        $data = $this->validData();
        $data['designs'][0]['produces'][0]['per_hour'] = 500;
        $data['designs'][0]['produces'][0]['basis'] = 'unknown';
        $this->assertHasError($data, "must be null when the basis is 'unknown'");

        $data = $this->validData();
        $data['designs'][0]['produces'] = [];
        $this->assertHasError($data, 'produces must list at least one item');
    }

    public function test_validator_rejects_missing_sources(): void
    {
        $data = $this->validData();
        unset($data['designs'][0]['source']);
        $this->assertHasError($data, "missing field 'source'");

        $data = $this->validData();
        $data['designs'][0]['source']['url'] = 'http://example.com/farm';
        $this->assertHasError($data, 'source.url must be an https URL');

        $data = $this->validData();
        $data['sources'] = [];
        $this->assertHasError($data, 'sources must name at least one source');
    }

    public function test_validator_rejects_duplicate_ids(): void
    {
        $data = $this->validData();
        $data['designs'][1]['id'] = $data['designs'][0]['id'];
        $this->assertHasError($data, "duplicate design id '{$data['designs'][0]['id']}'");

        $data = $this->validData();
        $data['items'][] = $data['items'][0];
        $this->assertHasError($data, "duplicate item id '{$data['items'][0]['id']}'");
    }

    public function test_validator_checks_version_range_and_measurements(): void
    {
        $data = $this->validData();
        $data['designs'][0]['versions'] = ['min' => '27.1', 'max' => null];
        $this->assertHasError($data, 'does not work in version 26.3');

        $data = $this->validData();
        $data['designs'][0]['produces'][0]['basis'] = 'measured';
        unset($data['designs'][0]['produces'][0]['note']);
        $this->assertHasError($data, 'the note must say who measured it');

        $data = $this->validData();
        $data['designs'][0]['difficulty'] = 'insane';
        $this->assertHasError($data, 'difficulty must be one of');
    }

    public function test_load_rejects_unknown_and_malformed_versions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FarmCatalog::load('../26.3');
    }

    // ---- filtering and sorting ----

    public function test_filter_by_item_search(): void
    {
        $catalog = FarmCatalog::load('26.3');

        $ids = array_column($catalog->filter(['item' => 'Iron Ingot']), 'id');
        $this->assertContains('booty-3-pod-iron-farm', $ids);
        $this->assertContains('fireblox-4-module-iron-farm', $ids);
        $this->assertNotContains('cuyiswild-expandable-cactus-farm', $ids);

        // also by id and by a secondary product
        $this->assertSame($ids, array_column($catalog->filter(['item' => 'iron_ingot']), 'id'));
        $this->assertContains('lazyperfection-detection-cane-bamboo', array_column($catalog->filter(['item' => 'bamboo']), 'id'));
        $this->assertSame([], $catalog->filter(['item' => 'netherite']));
    }

    public function test_filter_by_type_difficulty_and_footprint(): void
    {
        $catalog = FarmCatalog::load('26.3');

        foreach ($catalog->filter(['type' => 'kelp']) as $design)
            $this->assertSame('kelp', $design['type']);
        foreach ($catalog->filter(['difficulty' => 'easy']) as $design)
            $this->assertSame('easy', $design['difficulty']);

        $small = $catalog->filter(['max_footprint' => 15]);
        $this->assertNotEmpty($small);
        foreach ($small as $design) {
            $this->assertNotNull($design['footprint'], 'unknown footprints do not pass a size limit');
            $this->assertLessThanOrEqual(15, max($design['footprint']['x'], $design['footprint']['z']));
        }
        $this->assertContains('booty-3-pod-iron-farm', array_column($small, 'id'));

        // combined
        $ids = array_column($catalog->filter(['type' => 'iron', 'difficulty' => 'easy', 'max_footprint' => 20]), 'id');
        $this->assertSame(['booty-3-pod-iron-farm'], $ids);
    }

    public function test_sort_by_rate_puts_unknown_rates_last(): void
    {
        $catalog = FarmCatalog::load('26.3');

        $sorted = $catalog->sort($catalog->filter(['type' => 'iron']), 'rate');
        $this->assertSame(
            ['fireblox-4-module-iron-farm', 'booty-3-pod-iron-farm', 'shulkercraft-reliable-iron-farm', 'saehan-sprinter-golem-iron-farm'],
            array_column($sorted, 'id')
        );

        $ascending = $catalog->sort($catalog->filter(['type' => 'iron']), 'rate', 'asc');
        $this->assertSame(
            ['shulkercraft-reliable-iron-farm', 'booty-3-pod-iron-farm', 'fireblox-4-module-iron-farm', 'saehan-sprinter-golem-iron-farm'],
            array_column($ascending, 'id')
        );
    }

    public function test_sort_by_rate_uses_the_searched_item(): void
    {
        $catalog = FarmCatalog::load('26.3');

        // LazyPerfection's module makes 90 sugar cane but 480 bamboo per hour
        $sorted = $catalog->sort($catalog->filter(['item' => 'bamboo']), 'rate', 'default', 'bamboo');
        $rates = array_map(fn ($d) => $catalog->listedProduct($d, 'bamboo')['per_hour'], $sorted);
        $known = array_values(array_filter($rates, fn ($r) => $r !== null));
        $this->assertSame($known, array_slice($rates, 0, count($known)), 'unknown rates come last');
        $expected = $known;
        rsort($expected);
        $this->assertSame($expected, $known);

        $module = current(array_filter($sorted, fn ($d) => $d['id'] === 'lazyperfection-detection-cane-bamboo'));
        $this->assertSame(480, $catalog->listedProduct($module, 'bamboo')['per_hour']);
        $this->assertSame('sugar_cane', $catalog->listedProduct($module)['item']);
    }

    public function test_sort_by_footprint_and_name(): void
    {
        $catalog = FarmCatalog::load('26.3');

        $byFootprint = $catalog->sort($catalog->filter([]), 'footprint');
        $areas = array_map(fn ($d) => FarmCatalog::area($d), $byFootprint);
        $known = array_values(array_filter($areas, fn ($a) => $a !== null));
        $this->assertSame($known, array_slice($areas, 0, count($known)), 'unknown footprints come last');
        $sorted = $known;
        sort($sorted);
        $this->assertSame($sorted, $known);
        $this->assertSame('holyham-bone-meal-kelp-243k', $byFootprint[0]['id']);

        $byName = array_column($catalog->sort($catalog->filter([]), 'name'), 'name');
        $expected = $byName;
        usort($expected, 'strcasecmp');
        $this->assertSame($expected, $byName);
        $this->assertSame(array_reverse($byName), array_column($catalog->sort($catalog->filter([]), 'name', 'desc'), 'name'));
    }

    public function test_sort_rejects_unknown_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FarmCatalog::load('26.3')->sort([], 'colour');
    }

    public function test_comparison_lists_other_designs_of_the_same_item_by_rate(): void
    {
        $catalog = FarmCatalog::load('26.3');

        $rows = $catalog->comparison('booty-3-pod-iron-farm');
        $this->assertSame(
            ['fireblox-4-module-iron-farm', 'shulkercraft-reliable-iron-farm', 'saehan-sprinter-golem-iron-farm'],
            array_column($rows, 'id')
        );
        $this->assertSame([], $catalog->comparison('no-such-design'));

        // designsProducing is the shared lookup for the chain planner
        $this->assertSame('fireblox-4-module-iron-farm', $catalog->designsProducing('iron_ingot')[0]['id']);
    }
}
