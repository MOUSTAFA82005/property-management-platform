<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_route_identifies_the_api(): void
    {
        // The Blade login screen that used to live here was removed; the
        // backend is API-only and the root route now just identifies it.
        $this->getJson('/')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['name', 'api', 'status']);
    }
}
