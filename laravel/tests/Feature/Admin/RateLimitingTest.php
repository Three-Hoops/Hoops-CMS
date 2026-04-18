<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
    }

    public function test_login_succeeds_within_rate_limit(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create();

        // Act
        $response = $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_is_blocked_after_too_many_attempts(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act — exhaust the 3 allowed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/admin/login', [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertSessionHasErrors('throttle');
    }

    public function test_correct_credentials_are_blocked_once_rate_limited(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act — exhaust the 3 allowed attempts with wrong password
        for ($i = 0; $i < 3; $i++) {
            $this->post('/admin/login', [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // Attempt with correct credentials after lockout
        $response = $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertSessionHasErrors('throttle');
    }

    public function test_get_login_page_is_not_rate_limited(): void
    {
        // Arrange
        $this->withoutVite();

        // Act
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/admin/login');
        }

        // Assert
        $response->assertOk();
    }
}
