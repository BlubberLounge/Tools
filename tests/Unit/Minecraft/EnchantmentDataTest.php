<?php

namespace Tests\Unit\Minecraft;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use App\Minecraft\EnchantmentData;

/**
 * Validates the hand-maintained version files in resources/minecraft/enchantments.
 */
class EnchantmentDataTest extends TestCase
{
    public static function versionFiles(): array
    {
        $files = [];
        foreach (glob(dirname(__DIR__, 3).'/resources/minecraft/enchantments/*.json') as $file)
            $files[basename($file)] = [$file];

        return $files;
    }

    #[DataProvider('versionFiles')]
    public function test_version_file_is_valid(string $file): void
    {
        $data = json_decode(file_get_contents($file), true);

        $this->assertIsArray($data, basename($file).' is not valid JSON');
        $this->assertSame([], EnchantmentData::validate($data), basename($file).' has validation errors');
        $this->assertSame(basename($file, '.json'), $data['version'], 'file name and version field differ');
    }

    public function test_required_versions_exist_and_latest_is_default(): void
    {
        $versions = EnchantmentData::versions();

        $this->assertContains('1.21', $versions);
        $this->assertContains('26.3', $versions);
        // 26.3 is the latest Java release (Minecraft Wiki, 2026-10-15)
        $this->assertSame('26.3', EnchantmentData::latestVersion());
        $this->assertSame('26.3', $versions[0], 'versions are sorted newest first');
    }

    public function test_every_file_names_its_wiki_source_and_date(): void
    {
        foreach (EnchantmentData::versions() as $version) {
            $data = json_decode(file_get_contents(EnchantmentData::directory()."/{$version}.json"), true);
            $this->assertNotEmpty($data['sources']);
            foreach ($data['sources'] as $source) {
                $this->assertStringStartsWith('https://minecraft.wiki/', $source['url']);
                $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $source['retrieved']);
            }
        }
    }

    public function test_version_differences(): void
    {
        $old = EnchantmentData::load('1.21');
        $new = EnchantmentData::load('26.3');

        // spears and Lunge arrived in 1.21.11, copper gear in 1.21.9
        $this->assertNull($old->enchantment('lunge'));
        $this->assertNull($old->item('diamond_spear'));
        $this->assertNull($old->item('copper_sword'));
        $this->assertNotNull($new->enchantment('lunge'));
        $this->assertTrue($new->itemAccepts('diamond_spear', 'lunge'));
        $this->assertTrue($new->itemAccepts('copper_chestplate', 'protection'));

        // the mace and its enchantments exist in both
        foreach ([$old, $new] as $data) {
            foreach (['density', 'breach', 'wind_burst', 'smite', 'fire_aspect', 'mending'] as $enchantment)
                $this->assertTrue($data->itemAccepts('mace', $enchantment), "{$data->version()}: mace accepts {$enchantment}");
            $this->assertFalse($data->itemAccepts('mace', 'sharpness'));
            $this->assertSame(39, $data->levelCap());
        }
    }

    public function test_compatibility_follows_exclusive_sets(): void
    {
        $data = EnchantmentData::load('26.3');

        $this->assertFalse($data->areCompatible('sharpness', 'smite'));
        $this->assertFalse($data->areCompatible('density', 'breach'));
        $this->assertFalse($data->areCompatible('infinity', 'mending'));
        $this->assertFalse($data->areCompatible('riptide', 'loyalty'));
        $this->assertFalse($data->areCompatible('riptide', 'channeling'));
        // Loyalty and Channeling only clash with Riptide, not with each other
        $this->assertTrue($data->areCompatible('loyalty', 'channeling'));
        $this->assertTrue($data->areCompatible('sharpness', 'looting'));
        $this->assertEqualsCanonicalizing(['smite', 'bane_of_arthropods', 'impaling', 'density', 'breach'], $data->incompatibleWith('sharpness'));
    }

    public function test_book_multiplier_is_half_the_item_multiplier(): void
    {
        foreach (EnchantmentData::versions() as $version) {
            foreach (EnchantmentData::load($version)->enchantments() as $enchantment)
                $this->assertSame(max(1, intdiv($enchantment['item_multiplier'], 2)), $enchantment['book_multiplier'], "{$version} {$enchantment['id']}");
        }
    }

    public function test_validation_rejects_broken_data(): void
    {
        $valid = json_decode(file_get_contents(EnchantmentData::directory().'/26.3.json'), true);

        $cases = [
            'unknown top-level field' => fn ($d) => $d + ['colour' => 'red'],
            'unknown enchantment field' => function ($d) { $d['enchantments'][0]['weight'] = 2; return $d; },
            'unknown enchantment on item' => function ($d) { $d['items'][0]['enchantments'][] = 'super_sharpness'; return $d; },
            'single-member exclusive set' => function ($d) { $d['exclusive_sets']['solo'] = ['mending']; return $d; },
            'unknown member in exclusive set' => function ($d) { $d['exclusive_sets']['bow'][] = 'unknown_enchantment'; return $d; },
            'unknown exclusive set reference' => function ($d) { $d['enchantments'][0]['exclusive_set'] = 'nope'; return $d; },
            'duplicate item id' => function ($d) { $d['items'][] = $d['items'][0]; return $d; },
            'missing anvil constants' => function ($d) { unset($d['anvil']['level_cap']); return $d; },
            'unsupported prior-work formula' => function ($d) { $d['anvil']['prior_work_penalty'] = 'n^2'; return $d; },
            'missing xp sources' => function ($d) { $d['xp_sources']['mobs'] = []; return $d; },
            'source outside the wiki' => function ($d) { $d['sources'][0]['url'] = 'https://example.com/'; return $d; },
            'zero max level' => function ($d) { $d['enchantments'][0]['max_level'] = 0; return $d; },
        ];

        $this->assertSame([], EnchantmentData::validate($valid));
        foreach ($cases as $name => $break)
            $this->assertNotEmpty(EnchantmentData::validate($break($valid)), "validation accepted: {$name}");
    }

    public function test_unknown_or_unsafe_version_is_rejected(): void
    {
        $this->assertFalse(EnchantmentData::exists('../../.env'));
        $this->assertFalse(EnchantmentData::exists('1.0'));

        $this->expectException(InvalidArgumentException::class);
        EnchantmentData::load('9.99');
    }
}
