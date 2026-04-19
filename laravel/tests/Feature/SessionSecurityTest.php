<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_id_is_regenerated_on_login(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create();
        $sessionBefore = session()->getId();

        // Act
        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $this->assertNotEquals($sessionBefore, session()->getId());
    }

    public function test_protected_routes_redirect_to_login_when_unauthenticated(): void
    {
        // Arrange
        $this->withoutVite();

        // Act
        $response = $this->get('/admin/admin');

        // Assert
        $response->assertRedirect('/admin/login');
    }

    public function test_absolute_session_timeout_logs_out_user_after_expiry(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create();

        $this->actingAs($user)->withSession([
            'session_started_at' => now()->subHours(9),
        ]);

        // Act
        $response = $this->actingAs($user)
            ->withSession(['session_started_at' => now()->subHours(9)])
            ->get('/admin/admin');

        // Assert
        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_active_session_is_not_timed_out(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->withSession(['session_started_at' => now()->subHours(1)])
            ->get('/admin/admin');

        // Assert
        $response->assertRedirect();
        $this->assertAuthenticated();
    }
}
