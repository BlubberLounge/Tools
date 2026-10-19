<?php

namespace Tests\Feature\Minecraft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use App\Minecraft\ToolRegistry;
use App\Models\User;

/**
 * Browser-only Minecraft calculators: each one is a single page without an API.
 */
class CalculatorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Tool id => sections (data-testid) the page must render.
     */
    public static function tools(): array
    {
        return [
            'material calculator' => ['material-calculator', ['material-parts', 'material-result']],
        ];
    }

    private function user(?string $role = 'user'): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        if ($role !== null)
            $user->attachRole(config('roles.models.role')::where('slug', $role)->first());

        return $user->fresh();
    }

    #[DataProvider('tools')]
    public function test_tool_is_registered_and_listed(string $id, array $sections): void
    {
        $this->assertNotNull(ToolRegistry::find($id));

        $response = $this->actingAs($this->user())->get(route('minecraft.index'));
        $response->assertOk();
        $response->assertSee('data-minecraft-tool="'.$id.'"', false);
        $response->assertSee(route('minecraft.'.$id), false);
    }

    #[DataProvider('tools')]
    public function test_page_renders_all_sections(string $id, array $sections): void
    {
        $response = $this->actingAs($this->user())->get(route('minecraft.'.$id));

        $response->assertOk();
        $response->assertSee('id="'.$id.'"', false);
        foreach ($sections as $section)
            $response->assertSee('data-testid="'.$section.'"', false);

        $this->assertSame(1, preg_match('#<script type="application/json" id="'.$id.'-config">(.*?)</script>#s', $response->getContent(), $m));
        $this->assertIsArray(json_decode(trim($m[1]), true));
    }

    #[DataProvider('tools')]
    public function test_user_without_permission_is_refused(string $id, array $sections): void
    {
        $this->actingAs($this->user('unverified'))
            ->get(route('minecraft.'.$id))
            ->assertForbidden();
    }
}
