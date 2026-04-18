<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_defaults_to_en(): void
    {
        // Arrange / Act
        $user = User::factory()->create();

        // Assert
        $this->assertSame('en', $user->locale);
    }

    public function test_locale_is_shared_in_inertia_auth_prop(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['locale' => 'fr']);

        // Act
        $response = $this->actingAs($user)->get('/admin');

        // Assert
        $response->assertInertia(fn ($page) => $page->where('auth.locale', 'fr'));
    }
}
