<?php

namespace Tests\Feature\Admin;

use App\Enums\FlashType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_admin(): void
    {
        // Arrange
        $user = User::factory()->create(['is_active' => true]);

        // Act
        $response = $this->actingAs($user)->get('/admin');

        // Assert
        $response->assertOk();
    }

    public function test_inactive_user_is_redirected_to_login(): void
    {
        // Arrange
        $user = User::factory()->create(['is_active' => false]);

        // Act
        $response = $this->actingAs($user)->get('/admin');

        // Assert
        $response->assertRedirect(route('admin.login'));
    }

    public function test_inactive_user_is_logged_out(): void
    {
        // Arrange
        $user = User::factory()->create(['is_active' => false]);

        // Act
        $this->actingAs($user)->get('/admin');

        // Assert
        $this->assertGuest();
    }

    public function test_inactive_user_sees_error_flash_message(): void
    {
        // Arrange
        $user = User::factory()->create(['is_active' => false]);

        // Act
        $response = $this->actingAs($user)->get('/admin');

        // Assert
        $response->assertSessionHas(FlashType::Error->value);
    }
}
