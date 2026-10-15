<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

use App\Minecraft\ToolRegistry;
use App\Models\User;

class ChainPlannerPageTest extends TestCase
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

    public function test_planner_is_registered_and_listed(): void
    {
        $this->assertNotNull(ToolRegistry::find('chain-planner'));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertSee('data-minecraft-tool="chain-planner"', false);
        $response->assertSee(route('minecraft.chain-planner'), false);
        $response->assertSee('fa-solid fa-diagram-project', false);
    }

    public function test_planner_page_renders(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.chain-planner'));

        $response->assertOk();
        $response->assertSee('id="chain-planner"', false);
        $response->assertSee('chain-planner-config', false);
        $response->assertSee('Java Edition 26.3');
        $response->assertSee('Java Edition 1.21');
        // the graph tab is hidden on phones, list and tree are not
        $response->assertSee('class="tab hidden md:inline-flex"', false);
        $response->assertSee('data-testid="tab-tree"', false);
    }

    public function test_planner_page_is_translated(): void
    {
        $user = $this->user();
        app()->setLocale('de');

        $response = $this->actingAs($user)->get(route('minecraft.chain-planner'));
        $response->assertOk();
        $response->assertSee('Farm-Kettenplaner');
        $response->assertSee('Rohstoffe');
    }

    public function test_users_without_permission_are_rejected(): void
    {
        $user = $this->user('unverified');

        $this->actingAs($user)->get(route('minecraft.chain-planner'))->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/minecraft/chains')->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/minecraft/chains/26.3')->assertForbidden();
        $this->actingAs($user)->postJson('/api/v1/minecraft/chains/plan', [])->assertForbidden();

        $this->actingAs($user);
        $this->assertStringNotContainsString(route('minecraft.chain-planner'), view('includes.sidebar')->render());
        $this->actingAs($this->user());
        $this->assertStringContainsString(route('minecraft.chain-planner'), view('includes.sidebar')->render());
    }

    public function test_guests_are_rejected(): void
    {
        $this->get(route('minecraft.chain-planner'))->assertRedirect();
        $this->postJson('/api/v1/minecraft/chains/plan', ['version' => '26.3', 'item' => 'book', 'rate' => 1000])->assertUnauthorized();
    }

    public function test_api_lists_versions_and_version_data(): void
    {
        $user = $this->user();

        $versions = $this->actingAs($user)->getJson('/api/v1/minecraft/chains');
        $versions->assertOk();
        $this->assertSame(['26.3', '1.21'], array_column($versions->json('data'), 'id'));

        $data = $this->actingAs($user)->getJson('/api/v1/minecraft/chains/26.3');
        $data->assertOk();
        $data->assertJsonPath('data.version', '26.3');
        $data->assertJsonPath('data.defaults.fuel', 'coal');
        $data->assertJsonPath('data.furnace_rates.smelting', 360);
        $items = array_column($data->json('data.items'), null, 'id');
        $this->assertTrue($items['book']['craftable']);
        $this->assertFalse($items['sugar_cane']['craftable']);
        $this->assertArrayHasKey('iron_chain', $items);

        $this->actingAs($user)->getJson('/api/v1/minecraft/chains/9.9')->assertNotFound();
    }

    public function test_api_plans_a_chain(): void
    {
        $response = $this->actingAs($this->user())->postJson('/api/v1/minecraft/chains/plan', [
            'version' => '1.21',
            'item' => 'book',
            'rate' => 1000,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.target.item', 'book');
        $sources = array_column($response->json('data.sources'), null, 'item');
        $this->assertSame(2, $sources['sugar_cane']['modules']);
        $this->assertSame(1, $sources['leather']['modules']);
        $this->assertNotEmpty($response->json('data.edges'));
        $this->assertSame('book', $response->json('data.tree.item'));

        // overrides and automatic mode
        $auto = $this->actingAs($this->user())->postJson('/api/v1/minecraft/chains/plan', [
            'version' => '26.3',
            'item' => 'hopper',
            'rate' => 100,
            'mode' => 'auto',
            'module_rates' => ['iron_ingot' => 400],
            'furnace_rates' => ['smelting' => 300],
            'restocks_per_day' => 3,
        ]);
        $auto->assertOk();
        $auto->assertJsonPath('data.solver.status', 'optimal');
        $iron = array_column($auto->json('data.sources'), null, 'item')['iron_ingot'];
        $this->assertSame(2, $iron['modules']);
        $this->assertTrue($iron['module_rate_overridden']);
    }

    public function test_api_reports_loops_and_invalid_input(): void
    {
        $user = $this->user();

        $loop = $this->actingAs($user)->postJson('/api/v1/minecraft/chains/plan', [
            'version' => '26.3',
            'item' => 'iron_block',
            'rate' => 10,
            'choices' => ['iron_ingot' => 'iron_ingot_from_iron_block'],
        ]);
        $loop->assertStatus(422);
        $loop->assertJsonPath('success', false);
        $this->assertEqualsCanonicalizing(['iron_ingot', 'iron_block'], $loop->json('data.loop'));

        foreach ([
            ['version' => '26.3', 'item' => 'book', 'rate' => 0],
            ['version' => '26.3', 'item' => 'nope', 'rate' => 10],
            ['version' => '9.9', 'item' => 'book', 'rate' => 10],
            ['version' => '../26.3', 'item' => 'book', 'rate' => 10],
            ['version' => '26.3', 'item' => 'book', 'rate' => 10, 'mode' => 'magic'],
            ['version' => '26.3', 'item' => 'book', 'rate' => 10, 'choices' => ['book' => 'tnt']],
            ['version' => '26.3', 'item' => 'book', 'rate' => 10, 'fuel' => 'book'],
        ] as $payload)
            $this->actingAs($user)->postJson('/api/v1/minecraft/chains/plan', $payload)->assertStatus(422);
    }

    public function test_api_routes_are_throttled(): void
    {
        foreach (['api.minecraft.chains.versions', 'api.minecraft.chains.version', 'api.minecraft.chains.plan'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('throttle:60,1', $middleware, $name);
            $this->assertContains('auth:sanctum', $middleware, $name);
            $this->assertContains('permission:view.minecraft.chain.planner', $middleware, $name);
        }
    }
}
