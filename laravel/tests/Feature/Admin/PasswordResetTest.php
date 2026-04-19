<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads(): void
    {
        // Arrange
        $this->withoutVite();

        // Act
        $response = $this->get('/admin/forgot-password');

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Auth/ForgotPassword'));
    }

    public function test_reset_link_is_sent_for_valid_email(): void
    {
        // Arrange
        Notification::fake();
        $user = User::factory()->create();

        // Act
        $response = $this->post('/admin/forgot-password', ['email' => $user->email]);

        // Assert
        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_same_success_message_shown_for_invalid_email(): void
    {
        // Arrange
        Notification::fake();

        // Act
        $response = $this->post('/admin/forgot-password', ['email' => 'nonexistent@example.com']);

        // Assert — same response to prevent email enumeration
        $response->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_reset_password_page_loads_with_token(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Act
        $response = $this->get("/admin/reset-password/{$token}?email={$user->email}");

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Auth/ResetPassword')
            ->where('token', $token)
            ->where('email', $user->email)
        );
    }

    public function test_password_is_reset_with_valid_token(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Act
        $response = $this->post('/admin/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        // Assert
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('success');
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->post('/admin/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        // Assert
        $response->assertSessionHasErrors('email');
    }

    public function test_forgot_password_post_is_rate_limited(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act — exceed the 3 attempts per minute limit
        for ($i = 0; $i < 3; $i++) {
            $this->post('/admin/forgot-password', ['email' => $user->email]);
        }
        $response = $this->post('/admin/forgot-password', ['email' => $user->email]);

        // Assert
        $response->assertStatus(429);
    }
}
