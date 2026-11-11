<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSeeText('Admin & Voting')
            ->assertSeeText('Juri Best Supporter')
            ->assertSeeText('Monitoring Langsung')
            ->assertSeeText('Layar Panggung & Proyektor')
            ->assertSee('href="/admin"', false)
            ->assertSee('href="/juri-supporter"', false)
            ->assertSee('href="/vote"', false);
    }
}
