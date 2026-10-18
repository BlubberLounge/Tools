<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Minecraft\ToolRegistry;
use App\Models\MinecraftPortal;
use App\Models\User;

class PortalCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/v1/minecraft/portals';

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

    private function portal(User $user, array $attributes = []): MinecraftPortal
    {
        return MinecraftPortal::create($attributes + [
            'user_id' => $user->id,
            'name' => 'Base',
            'world' => null,
            'dimension' => 'overworld',
            'x' => 800,
            'y' => 64,
            'z' => -160,
        ]);
    }

    public function test_calculator_is_registered_and_listed(): void
    {
        $this->assertNotNull(ToolRegistry::find('portal-calculator'));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertSee('data-minecraft-tool="portal-calculator"', false);
        $response->assertSee(route('minecraft.portal-calculator'), false);
    }

    public function test_page_renders_all_sections(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.portal-calculator'));

        $response->assertOk();
        $response->assertSee('id="portal-calculator"', false);
        foreach (['converter', 'frame', 'distance', 'portal-list', 'portal-form'] as $section)
            $response->assertSee('data-testid="'.$section.'"', false);

        $this->assertSame(1, preg_match('#<script type="application/json" id="portal-calculator-config">(.*?)</script>#s', $response->getContent(), $m));
        $config = json_decode(trim($m[1]), true);
        $this->assertStringEndsWith('/api/v1/minecraft/portals', $config['endpoints']['portals']);
    }

    public function test_user_without_permission_is_refused(): void
    {
        $user = $this->user('unverified');

        $this->actingAs($user)->get(route('minecraft.portal-calculator'))->assertForbidden();
        $this->actingAs($user)->getJson(self::API)->assertForbidden();
        $this->actingAs($user)->postJson(self::API, [])->assertForbidden();
    }

    public function test_api_needs_a_login(): void
    {
        $this->getJson(self::API)->assertUnauthorized();
    }

    public function test_user_saves_and_lists_portals(): void
    {
        $user = $this->user();

        $this->actingAs($user)->postJson(self::API, [
            'name' => 'Base',
            'world' => 'Survival',
            'dimension' => 'nether',
            'x' => 100,
            'y' => 70,
            'z' => -20,
        ])->assertOk()->assertJsonPath('data.name', 'Base');

        $response = $this->actingAs($user)->getJson(self::API);
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.world', 'Survival');
        $response->assertJsonPath('data.0.dimension', 'nether');
        $response->assertJsonPath('data.0.x', 100);
        $response->assertJsonMissingPath('data.0.user_id');
    }

    public function test_user_only_sees_own_portals(): void
    {
        $owner = $this->user();
        $this->portal($owner);

        $this->actingAs($this->user())->getJson(self::API)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_user_updates_and_deletes_own_portal(): void
    {
        $user = $this->user();
        $portal = $this->portal($user);

        $this->actingAs($user)->putJson(self::API.'/'.$portal->id, [
            'name' => 'Farm',
            'dimension' => 'overworld',
            'x' => 1,
            'y' => 2,
            'z' => 3,
        ])->assertOk();
        $this->assertSame('Farm', $portal->fresh()->name);
        $this->assertSame(3, $portal->fresh()->z);

        $this->actingAs($user)->deleteJson(self::API.'/'.$portal->id)->assertOk();
        $this->assertNull($portal->fresh());
    }

    public function test_user_cannot_change_foreign_portals(): void
    {
        $portal = $this->portal($this->user());
        $other = $this->user();

        $this->actingAs($other)->putJson(self::API.'/'.$portal->id, [
            'name' => 'Stolen', 'dimension' => 'overworld', 'x' => 0, 'y' => 64, 'z' => 0,
        ])->assertNotFound();
        $this->actingAs($other)->deleteJson(self::API.'/'.$portal->id)->assertNotFound();

        $this->assertSame('Base', $portal->fresh()->name);
    }

    public function test_input_is_validated(): void
    {
        $user = $this->user();

        $this->actingAs($user)->postJson(self::API, [
            'name' => '',
            'dimension' => 'end',
            'x' => 'abc',
            'y' => 400,
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'dimension', 'x', 'y', 'z']);

        // the Nether world border is 1/8 of the Overworld one
        $this->actingAs($user)->postJson(self::API, [
            'name' => 'Far', 'dimension' => 'nether', 'x' => 4000000, 'y' => 64, 'z' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors(['x']);

        $this->actingAs($user)->postJson(self::API, [
            'name' => 'Far', 'dimension' => 'overworld', 'x' => 4000000, 'y' => 64, 'z' => 0,
        ])->assertOk();
    }
}
