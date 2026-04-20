<?php

namespace Tests\Feature\Admin;

use App\Enums\ThemeMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_theme_mode(): void
    {
        // Arrange
        $user = User::factory()->create(['theme_mode' => ThemeMode::System]);

        // Act
        $response = $this->actingAs($user)->put('/admin/preferences/theme', [
            'theme_mode' => 'dark',
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'theme_mode' => 'dark',
        ]);
    }

    public function test_theme_mode_update_rejects_invalid_value(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->put('/admin/preferences/theme', [
            'theme_mode' => 'invalid',
        ]);

        // Assert
        $response->assertSessionHasErrors('theme_mode');
    }

    public function test_guest_cannot_update_theme_mode(): void
    {
        // Act
        $response = $this->put('/admin/preferences/theme', [
            'theme_mode' => 'dark',
        ]);

        // Assert
        $response->assertRedirect(route('admin.login'));
    }

    public function test_all_valid_theme_modes_are_accepted(): void
    {
        // Arrange
        $user = User::factory()->create();

        foreach (ThemeMode::cases() as $mode) {
            // Act
            $response = $this->actingAs($user)->put('/admin/preferences/theme', [
                'theme_mode' => $mode->value,
            ]);

            // Assert
            $response->assertRedirect();
            $this->assertDatabaseHas('users', [
                'id'         => $user->id,
                'theme_mode' => $mode->value,
            ]);
        }
    }

    public function test_authenticated_user_can_update_timezone(): void
    {
        // Arrange
        $user = User::factory()->create(['timezone' => 'UTC']);

        // Act
        $response = $this->actingAs($user)->put('/admin/preferences/timezone', [
            'timezone' => 'Europe/Brussels',
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'timezone' => 'Europe/Brussels',
        ]);
    }

    public function test_timezone_update_rejects_invalid_value(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->put('/admin/preferences/timezone', [
            'timezone' => 'Not/ATimezone',
        ]);

        // Assert
        $response->assertSessionHasErrors('timezone');
    }

    public function test_guest_cannot_update_timezone(): void
    {
        // Act
        $response = $this->put('/admin/preferences/timezone', [
            'timezone' => 'Europe/Brussels',
        ]);

        // Assert
        $response->assertRedirect(route('admin.login'));
    }

    public function test_timezone_is_shared_in_inertia_auth_prop(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['timezone' => 'America/New_York']);

        // Act
        $response = $this->actingAs($user)->get('/admin');

        // Assert
        $response->assertInertia(fn ($page) => $page->where('auth.timezone', 'America/New_York'));
    }
}
