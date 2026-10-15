<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Minecraft\FarmModels;
use App\Minecraft\ToolRegistry;
use App\Models\User;

class FarmViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        FarmModels::flush();
    }

    private function user(?string $role = 'user'): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        if ($role !== null)
            $user->attachRole(config('roles.models.role')::where('slug', $role)->first());

        return $user->fresh();
    }

    private function config(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/json" id="farm-viewer-config">(.*?)</script>#s', $html, $m));

        return json_decode(trim($m[1]), true);
    }

    public function test_viewer_is_registered_and_listed(): void
    {
        $this->assertNotNull(ToolRegistry::find('farm-viewer'));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertSee('data-minecraft-tool="farm-viewer"', false);
        $response->assertSee(route('minecraft.farm-viewer'), false);
    }

    public function test_page_renders_with_the_model_list(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.farm-viewer'));

        $response->assertOk();
        $response->assertSee('id="farm-viewer"', false);
        $response->assertSee('data-testid="design-picker"', false);
        $response->assertSee('data-testid="viewer-help"', false);
        $response->assertSee('data-testid="texture-credit"', false);
        $response->assertDontSee('data-testid="design-not-found"', false);

        $config = $this->config($response->getContent());
        $ids = array_column($config['models'], 'id');
        $this->assertContains('example-sugar-cane-module', $ids);
        $this->assertContains('example-iron-farm-pod', $ids);
        $this->assertSame($config['models'][0]['id'], $config['initial']);
        $this->assertStringEndsWith('minecraft/atlas/atlas.json', $config['atlasUrl']);
        // block data is fetched by the page, not embedded
        $this->assertArrayNotHasKey('blocks', $config['models'][0]);
    }

    public function test_design_parameter_selects_the_model(): void
    {
        $response = $this->actingAs($this->user())
            ->get(route('minecraft.farm-viewer', ['design' => 'example-kelp-column']));

        $response->assertOk();
        $this->assertSame('example-kelp-column', $this->config($response->getContent())['initial']);
    }

    public function test_unknown_design_shows_a_notice(): void
    {
        $response = $this->actingAs($this->user())
            ->get(route('minecraft.farm-viewer', ['design' => 'no-such-design']));

        $response->assertOk();
        $response->assertSee('data-testid="design-not-found"', false);
        $this->assertNotNull($this->config($response->getContent())['initial']);
    }

    public function test_models_are_served_as_static_files(): void
    {
        foreach (FarmModels::all() as $model)
            $this->assertFileExists(public_path('minecraft/models/' . $model['id'] . '.json'));
    }

    public function test_users_without_permission_are_rejected(): void
    {
        $this->actingAs($this->user('unverified'))
            ->get(route('minecraft.farm-viewer'))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('minecraft.farm-viewer'))->assertRedirect();
    }

    public function test_sidebar_links_the_viewer_for_users(): void
    {
        $this->actingAs($this->user());
        $html = view('includes.sidebar')->render();
        $this->assertStringContainsString(route('minecraft.farm-viewer'), $html);

        $this->actingAs($this->user('unverified'));
        $html = view('includes.sidebar')->render();
        $this->assertStringNotContainsString(route('minecraft.farm-viewer'), $html);
    }
}
