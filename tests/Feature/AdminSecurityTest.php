<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['admin.password_hash' => Hash::make('test-password')]);
    }

    public function test_guests_cannot_open_admin_pages_or_export(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
        $this->get('/admin/votes')->assertRedirect(route('admin.login'));
        $this->get('/admin/export-excel')->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_regenerates_the_session_and_grants_access(): void
    {
        $this->post('/admin/login', ['password' => 'test-password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(session('is_admin'));
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_admin_logout_requires_post_and_invalidates_access(): void
    {
        $this->withSession(['is_admin' => true]);

        $this->get('/admin/logout')->assertStatus(405);
        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }
}
