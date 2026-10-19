<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Minecraft\ToolRegistry;
use App\Models\User;

class StackCalculatorTest extends TestCase
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

    public function test_calculator_is_registered_and_listed(): void
    {
        $this->assertNotNull(ToolRegistry::find('stack-calculator'));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertSee('data-minecraft-tool="stack-calculator"', false);
        $response->assertSee(route('minecraft.stack-calculator'), false);
    }

    public function test_page_renders_all_sections(): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.stack-calculator'));

        $response->assertOk();
        $response->assertSee('id="stack-calculator"', false);
        foreach (['stack-input', 'stack-result', 'stack-breakdown', 'stack-conversions'] as $section)
            $response->assertSee('data-testid="'.$section.'"', false);

        $this->assertSame(1, preg_match('#<script type="application/json" id="stack-calculator-config">(.*?)</script>#s', $response->getContent(), $m));
        $config = json_decode(trim($m[1]), true);
        $this->assertSame(
            ['item', 'stack', 'hopper', 'dispenser', 'chest', 'shulker', 'inventory', 'doubleChest', 'shulkerChest', 'shulkerDoubleChest'],
            array_keys($config['text']['units'])
        );
    }

    public function test_user_without_permission_is_refused(): void
    {
        $this->actingAs($this->user('unverified'))
            ->get(route('minecraft.stack-calculator'))
            ->assertForbidden();
    }

    public function test_guest_is_sent_to_the_login(): void
    {
        $this->get(route('minecraft.stack-calculator'))->assertRedirect();
    }
}
