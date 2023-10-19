<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserWebRouteAccessTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_app_availability(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_app_not_found_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }
}
