<?php

namespace Tests\Unit\Minecraft;

use Tests\TestCase;

use App\Minecraft\FarmModels;

/**
 * Validates every committed farm model (public/minecraft/models/*.json)
 * against the format and the viewer's block table.
 */
class FarmModelsTest extends TestCase
{
    private const SHAPES = [
        'cube', 'none', 'cross', 'slab', 'stairs', 'trapdoor', 'hopper', 'path', 'carpet', 'snow',
        'pressure_plate', 'button', 'lever', 'torch', 'wall_torch', 'diode', 'daylight_detector', 'rail',
        'wire', 'flat', 'fence', 'fence_gate', 'wall', 'pane', 'chest', 'bed', 'door', 'ladder', 'vine',
        'sign', 'wall_sign', 'cactus', 'bamboo', 'lantern', 'chain', 'rod', 'campfire', 'piston_head',
    ];

    protected function tearDown(): void
    {
        FarmModels::flush();
        parent::tearDown();
    }

    private function validModel(): array
    {
        return [
            'format' => 1,
            'id' => 'tiny',
            'design' => null,
            'name' => 'Tiny',
            'size' => [2, 1, 2],
            'palette' => [['name' => 'minecraft:air'], ['name' => 'minecraft:stone']],
            'blocks' => [3, 1, 1, 0],
            'source' => ['url' => 'https://example.org/tiny.litematic', 'license' => 'CC0-1.0'],
        ];
    }

    public function test_at_least_three_models_are_committed(): void
    {
        $this->assertGreaterThanOrEqual(3, count(FarmModels::files()));
    }

    public function test_every_committed_model_is_valid(): void
    {
        $known = FarmModels::knownBlocks();
        $ids = [];
        foreach (FarmModels::files() as $file) {
            $model = json_decode((string) file_get_contents($file), true);
            $name = basename($file);

            $this->assertSame([], FarmModels::validate($model, $known), $name);
            $this->assertSame($model['id'] . '.json', $name, "$name: file name must be the model id");
            $this->assertNotContains($model['id'], $ids, "$name: duplicate id");
            $ids[] = $model['id'];
            foreach ($model['size'] as $n)
                $this->assertLessThanOrEqual(FarmModels::MAX_SIZE, $n, $name);
        }
    }

    public function test_designs_are_unique(): void
    {
        $designs = array_filter(array_column(FarmModels::all(), 'design'));
        $this->assertSame(count($designs), count(array_unique($designs)));
    }

    public function test_block_table_is_complete(): void
    {
        $table = json_decode((string) file_get_contents(FarmModels::blockTablePath()), true);
        $this->assertArrayHasKey('minecraft:air', $table['blocks']);

        foreach ($table['blocks'] as $id => $def) {
            $this->assertMatchesRegularExpression('/^minecraft:[a-z0-9_]+$/', $id);
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $def['color'], $id);
            $this->assertContains($def['shape'] ?? 'cube', self::SHAPES, $id);
            $this->assertContains($def['layer'] ?? 'solid', ['solid', 'cutout', 'translucent'], $id);
            if (isset($def['item']) && $def['item'] !== false)
                $this->assertMatchesRegularExpression('/^minecraft:[a-z0-9_]+$/', $def['item'], $id);
        }
    }

    public function test_validation_catches_broken_models(): void
    {
        $known = FarmModels::knownBlocks();
        $this->assertSame([], FarmModels::validate($this->validModel(), $known));

        $model = $this->validModel();
        $model['palette'][1]['name'] = 'minecraft:not_a_block';
        $this->assertContains('unknown block id minecraft:not_a_block', FarmModels::validate($model, $known));

        $model = $this->validModel();
        $model['size'] = [2, 1, 129];
        $this->assertContains('size must be [x, y, z] with 1..128 each', FarmModels::validate($model, $known));

        $model = $this->validModel();
        $model['blocks'] = [2, 1, 1, 0];
        $this->assertContains('blocks cover 3 cells, the size needs 4', FarmModels::validate($model, $known));

        $model = $this->validModel();
        $model['blocks'] = [3, 7, 1, 0];
        $this->assertSame(['palette index 7 out of range'], FarmModels::validate($model, $known));

        $model = $this->validModel();
        unset($model['source']['url']);
        $model['design'] = 'Not Kebab';
        $errors = FarmModels::validate($model, $known);
        $this->assertContains('source.url is missing', $errors);
        $this->assertContains('design must be a kebab-case catalogue id or null', $errors);
    }

    public function test_models_are_found_by_design_or_id(): void
    {
        $first = FarmModels::all()[0];
        $this->assertSame($first['id'], FarmModels::find($first['id'])['id']);
        $this->assertNull(FarmModels::find('no-such-design'));
        $this->assertStringEndsWith('minecraft/models/' . $first['id'] . '.json', $first['url']);
    }
}
