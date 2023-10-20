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

        $response->assertStatus(200);
    }

    public function test_models_can_be_instantiated(): void
    {
        // $user = User::factory()->create();
        $this->assertTrue(true, 'very true');
    }

    public function test_route_test_example(): void
    {
        $this->seed();
        // $this->withoutExceptionHandling();
        $user = User::getRootUser();
        $response = $this->actingAs($user)->get('/abc');
        $response->assertSuccessful();
    }
}
