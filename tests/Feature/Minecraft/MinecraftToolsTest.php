<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

use App\Minecraft\ToolRegistry;
use App\Models\User;

class MinecraftToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(?string $role = 'user'): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        if ($role !== null)
            $user->attachRole(config('roles.models.role')::where('slug', $role)->first());

        return $user->fresh();
    }

    public function test_user_role_sees_the_minecraft_section(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.index'));

        $response->assertOk();
        $response->assertSee('data-minecraft-tool="anvil-planner"', false);
        $response->assertSee(route('minecraft.anvil-planner'), false);
        // sidebar group with the tool entry
        $response->assertSee('fa-solid fa-cube', false);
        $response->assertSee('fa-solid fa-hammer', false);
    }

    public function test_anvil_planner_page_renders(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.anvil-planner'));

        $response->assertOk();
        $response->assertSee('id="anvil-planner"', false);
        $response->assertSee('anvil-planner-config', false);
        $response->assertSee('Java Edition 26.3');
        $response->assertSee('Java Edition 1.21');
    }

    public function test_users_without_permission_are_rejected(): void
    {
        $user = $this->user('unverified');

        $this->actingAs($user)->get(route('minecraft.index'))->assertForbidden();
        $this->actingAs($user)->get(route('minecraft.anvil-planner'))->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/minecraft/versions')->assertForbidden();
        $this->actingAs($user)->postJson('/api/v1/minecraft/anvil/plan', [])->assertForbidden();
    }

    public function test_sidebar_hides_the_section_without_permission(): void
    {
        $this->actingAs($this->user('unverified'));
        $html = view('includes.sidebar')->render();
        $this->assertStringNotContainsString(route('minecraft.anvil-planner'), $html);

        $this->actingAs($this->user());
        $html = view('includes.sidebar')->render();
        $this->assertStringContainsString(route('minecraft.anvil-planner'), $html);
        $this->assertStringContainsString(route('minecraft.index'), $html);
    }

    public function test_unbuilt_tools_do_not_appear(): void
    {
        config(['minecraft.tools' => array_merge(config('minecraft.tools'), [[
            'id' => 'unbuilt-test-tool',
            'title' => 'unbuilt test tool',
            'description' => 'not built yet',
            'icon' => 'fa-solid fa-flask',
            'route' => 'minecraft.unbuilt-test-tool',
            'permission' => 'view.minecraft',
        ]])]);

        $this->assertNotContains('unbuilt-test-tool', array_column(ToolRegistry::all(), 'id'));
        $this->assertNull(ToolRegistry::find('unbuilt-test-tool'));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertDontSee('unbuilt-test-tool', false);
    }

    public function test_api_lists_versions_and_version_data(): void
    {
        $user = $this->user();

        $versions = $this->actingAs($user)->getJson('/api/v1/minecraft/versions');
        $versions->assertOk();
        $this->assertSame(['26.3', '1.21'], array_column($versions->json('data'), 'id'));

        $data = $this->actingAs($user)->getJson('/api/v1/minecraft/versions/26.3');
        $data->assertOk();
        $data->assertJsonPath('data.version', '26.3');
        $items = array_column($data->json('data.items'), null, 'id');
        $this->assertArrayHasKey('netherite_spear', $items);
        $enchantments = array_column($data->json('data.enchantments'), null, 'id');
        $this->assertContains('smite', $enchantments['sharpness']['incompatible']);

        $this->actingAs($user)->getJson('/api/v1/minecraft/versions/9.9')->assertNotFound();
    }

    public function test_api_plans_a_full_sword(): void
    {
        $response = $this->actingAs($this->user())->postJson('/api/v1/minecraft/anvil/plan', [
            'version' => '26.3',
            'item' => 'diamond_sword',
            'enchantments' => ['sharpness' => 5, 'knockback' => 2, 'fire_aspect' => 2, 'looting' => 3, 'sweeping_edge' => 3, 'unbreaking' => 3],
            'present' => ['mending' => 1],
            'prior_work' => 0,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.possible', true);
        $response->assertJsonPath('data.books', 6);
        $this->assertCount(6, $response->json('data.steps'));
        $this->assertNotEmpty($response->json('data.xp_sources.mobs'));
    }

    public function test_api_reports_impossible_and_invalid_selections(): void
    {
        $user = $this->user();

        $impossible = $this->actingAs($user)->postJson('/api/v1/minecraft/anvil/plan', [
            'version' => '26.3',
            'item' => 'diamond_sword',
            'enchantments' => ['sharpness' => 5, 'looting' => 3],
            'prior_work' => 5,
        ]);
        $impossible->assertOk();
        $impossible->assertJsonPath('data.possible', false);
        $impossible->assertJsonPath('data.failing_step', 2);

        $this->actingAs($user)->postJson('/api/v1/minecraft/anvil/plan', [
            'version' => '26.3',
            'item' => 'diamond_sword',
            'enchantments' => ['sharpness' => 5, 'smite' => 5],
        ])->assertStatus(422);

        $this->actingAs($user)->postJson('/api/v1/minecraft/anvil/plan', [
            'version' => '1.21',
            'item' => 'diamond_spear',
            'enchantments' => ['lunge' => 3],
        ])->assertStatus(422);

        $this->actingAs($user)->postJson('/api/v1/minecraft/anvil/plan', [
            'version' => '../1.21',
            'item' => 'diamond_sword',
            'enchantments' => ['sharpness' => 5],
        ])->assertStatus(422);
    }

    public function test_api_routes_are_throttled(): void
    {
        foreach (['api.minecraft.versions', 'api.minecraft.version', 'api.minecraft.anvil.plan'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('throttle:60,1', $middleware, $name);
            $this->assertContains('auth:sanctum', $middleware, $name);
        }
    }
}
