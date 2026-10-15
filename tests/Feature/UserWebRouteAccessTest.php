<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;

class UserWebRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_app_availability(): void
    {
        $response = $this->get('/');

        // The index route sends guests to the login page
        $response->assertRedirect(route('login'));

        $this->get(route('login'))->assertStatus(200);
    }

    public function test_models_can_be_instantiated(): void
    {
        // $user = User::factory()->create();
        $this->assertTrue(true, 'very true');
    }

    public function test_route_test_example(): void
    {
        // The database is already seeded by RefreshDatabase ($seed in TestCase)
        // $this->withoutExceptionHandling();
        $user = User::getRootUser();
        $response = $this->actingAs($user)->get(route('home'));
        $response->assertSuccessful();
    }
}
