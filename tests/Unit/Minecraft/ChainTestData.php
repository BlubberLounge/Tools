<?php

namespace Tests\Unit\Minecraft;

use App\Minecraft\FarmCatalog;
use App\Minecraft\RecipeData;

/**
 * Small synthetic recipe files and farm catalogues for the chain planner and
 * solver tests (the real files change when data is added).
 */
trait ChainTestData
{
    /**
     * @param array<int, array> $recipes
     * @param string[] $items
     */
    private function recipeData(array $recipes, array $items, array $fuels = [['item' => 'coal', 'smelts' => 8]]): RecipeData
    {
        $items = array_values(array_unique(array_merge($items, array_column($fuels, 'item'))));

        return RecipeData::fromArray([
            'version' => '1.21',
            'name' => 'Test data',
            'updated' => '2026-10-15',
            'sources' => [['title' => 'Test', 'url' => 'https://minecraft.wiki/w/Crafting', 'retrieved' => '2026-10-15']],
            'defaults' => ['fuel' => $fuels[0]['item'], 'restocks_per_day' => 2, 'hopper_items_per_hour' => 9000],
            'fuels' => $fuels,
            'items' => array_map(fn (string $id) => ['id' => $id, 'name' => ucfirst(str_replace('_', ' ', $id))], $items),
            'recipes' => $recipes,
        ]);
    }

    /**
     * @param array<string, float|null> $rates item => items per hour of one module (null = unknown)
     */
    private function farmCatalog(array $rates): FarmCatalog
    {
        $designs = [];
        foreach ($rates as $item => $rate) {
            $designs[] = [
                'id' => str_replace('_', '-', $item).'-farm',
                'name' => ucfirst($item).' farm',
                'type' => 'test',
                'produces' => [['item' => $item, 'per_hour' => $rate, 'basis' => $rate === null ? 'unknown' : 'designer']],
                'versions' => ['min' => '1.21', 'max' => null],
                'footprint' => null,
                'materials' => [['item' => $item, 'count' => null]],
                'difficulty' => 'easy',
                'condition' => 'test',
                'author' => 'test',
                'community' => null,
                'source' => ['url' => 'https://minecraft.wiki/w/Farm', 'title' => 'Test', 'kind' => 'wiki', 'retrieved' => '2026-10-15'],
            ];
        }

        return FarmCatalog::fromArray([
            'version' => '1.21',
            'name' => 'Test farms',
            'updated' => '2026-10-15',
            'sources' => [['title' => 'Test', 'url' => 'https://minecraft.wiki/w/Farm', 'retrieved' => '2026-10-15']],
            'farm_types' => [['id' => 'test', 'name' => 'Test']],
            'items' => array_map(fn (string $id) => ['id' => $id, 'name' => ucfirst($id)], array_keys($rates)),
            'designs' => $designs,
        ]);
    }

    private static function craft(string $id, string $output, int $count, array $inputs): array
    {
        return [
            'id' => $id,
            'type' => 'crafting',
            'output' => ['item' => $output, 'count' => $count],
            'inputs' => array_map(fn (string $item, int $n) => ['item' => $item, 'count' => $n], array_keys($inputs), $inputs),
        ];
    }
}
