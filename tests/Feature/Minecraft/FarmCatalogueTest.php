<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

use App\Minecraft\ToolRegistry;
use App\Models\User;

class FarmCatalogueTest extends TestCase
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

    public function test_catalogue_is_registered_and_listed(): void
    {
        $this->assertNotNull(ToolRegistry::find('farm-catalogue'));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertSee('data-minecraft-tool="farm-catalogue"', false);
        $response->assertSee(route('minecraft.farm-catalogue'), false);
        $response->assertSee('fa-solid fa-wheat-awn', false);
    }

    public function test_catalogue_page_renders(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.farm-catalogue'));

        $response->assertOk();
        $response->assertSee('id="farm-catalogue"', false);
        $response->assertSee('farm-catalogue-config', false);
        $response->assertSee('Java Edition 26.3');
        $response->assertSee('Java Edition 1.21');
        $response->assertSee('rel="noopener"', false);
    }

    public function test_catalogue_page_is_translated(): void
    {
        $user = $this->user();
        app()->setLocale('de');

        $response = $this->actingAs($user)->get(route('minecraft.farm-catalogue'));
        $response->assertOk();
        $response->assertSee('Farm-Modul-Katalog');
    }

    public function test_users_without_permission_are_rejected(): void
    {
        $user = $this->user('unverified');

        $this->actingAs($user)->get(route('minecraft.farm-catalogue'))->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms')->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3')->assertForbidden();
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3/designs/booty-3-pod-iron-farm')->assertForbidden();

        $this->assertStringNotContainsString(route('minecraft.farm-catalogue'), $this->sidebarFor($user));
        $this->assertStringContainsString(route('minecraft.farm-catalogue'), $this->sidebarFor($this->user()));
    }

    private function sidebarFor(User $user): string
    {
        $this->actingAs($user);

        return view('includes.sidebar')->render();
    }

    public function test_guests_are_rejected(): void
    {
        $this->get(route('minecraft.farm-catalogue'))->assertRedirect();
        $this->getJson('/api/v1/minecraft/farms/26.3')->assertUnauthorized();
    }

    public function test_api_lists_versions(): void
    {
        $response = $this->actingAs($this->user())->getJson('/api/v1/minecraft/farms');

        $response->assertOk();
        $this->assertSame(['26.3', '1.21'], array_column($response->json('data'), 'id'));
        $this->assertGreaterThanOrEqual(25, $response->json('data.0.designs'));
    }

    public function test_api_lists_designs_with_filters_and_sorting(): void
    {
        $user = $this->user();

        $all = $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3');
        $all->assertOk();
        $all->assertJsonPath('data.version', '26.3');
        $this->assertSame($all->json('data.total'), count($all->json('data.designs')));
        $this->assertNotEmpty($all->json('data.farm_types'));
        $first = $all->json('data.designs.0');
        foreach (['id', 'name', 'type_name', 'listed_product', 'footprint', 'difficulty', 'source', 'materials', 'model'] as $key)
            $this->assertArrayHasKey($key, $first);

        $iron = $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3?item=iron+ingot');
        $iron->assertOk();
        $this->assertSame(
            ['fireblox-4-module-iron-farm', 'booty-3-pod-iron-farm', 'shulkercraft-reliable-iron-farm', 'saehan-sprinter-golem-iron-farm'],
            array_column($iron->json('data.designs'), 'id')
        );
        $this->assertLessThan($iron->json('data.total'), count($iron->json('data.designs')));

        $small = $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3?sort=footprint&max_footprint=10');
        $small->assertOk();
        $this->assertSame('holyham-bone-meal-kelp-243k', $small->json('data.designs.0.id'));
        foreach ($small->json('data.designs') as $design)
            $this->assertLessThanOrEqual(10, max($design['footprint']['x'], $design['footprint']['z']));

        $easyKelp = $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3?type=kelp&difficulty=easy&sort=name&direction=asc');
        $easyKelp->assertOk();
        $this->assertSame(['reytgood-afk-kelp-farm'], array_column($easyKelp->json('data.designs'), 'id'));
    }

    public function test_api_rejects_invalid_filters_and_versions(): void
    {
        $user = $this->user();

        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3?sort=colour')->assertStatus(422);
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3?type=lava')->assertStatus(422);
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3?max_footprint=0')->assertStatus(422);
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/9.9')->assertNotFound();
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/26.3/designs/no-such-farm')->assertNotFound();
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/9.9/designs/booty-3-pod-iron-farm')->assertNotFound();
    }

    public function test_api_returns_design_details_with_comparison(): void
    {
        $response = $this->actingAs($this->user())->getJson('/api/v1/minecraft/farms/26.3/designs/booty-3-pod-iron-farm');

        $response->assertOk();
        $response->assertJsonPath('data.design.id', 'booty-3-pod-iron-farm');
        $response->assertJsonPath('data.design.footprint', ['x' => 15, 'y' => 29, 'z' => 15]);
        $response->assertJsonPath('data.design.produces.0.name', 'Iron Ingot');
        $response->assertJsonPath('data.design.source.kind', 'schematic');
        $this->assertStringStartsWith('https://', $response->json('data.design.source.url'));
        $this->assertNotEmpty($response->json('data.design.materials'));
        $this->assertSame(
            ['fireblox-4-module-iron-farm', 'shulkercraft-reliable-iron-farm', 'saehan-sprinter-golem-iron-farm'],
            array_column($response->json('data.comparison'), 'id')
        );
    }

    public function test_older_version_has_its_own_designs(): void
    {
        $user = $this->user();

        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/1.21/designs/saehan-sprinter-golem-iron-farm')->assertNotFound();
        $this->actingAs($user)->getJson('/api/v1/minecraft/farms/1.21/designs/theysix-infinite-string-farm')->assertOk();
    }

    public function test_api_routes_are_throttled(): void
    {
        foreach (['api.minecraft.farms.versions', 'api.minecraft.farms.index', 'api.minecraft.farms.design'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('throttle:60,1', $middleware, $name);
            $this->assertContains('auth:sanctum', $middleware, $name);
            $this->assertContains('permission:view.minecraft.farm.catalogue', $middleware, $name);
        }
    }
}
