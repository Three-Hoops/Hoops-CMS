<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class PasswordComplexityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_defaults_reject_password_shorter_than_8_characters(): void
    {
        // Arrange
        $rule = Password::defaults();

        // Act
        $validator = validator(['password' => 'short'], ['password' => ['required', $rule]]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_password_defaults_accept_password_of_8_or_more_characters_in_non_production(): void
    {
        // Arrange
        $rule = Password::defaults();

        // Act
        $validator = validator(['password' => 'longenough'], ['password' => ['required', $rule]]);

        // Assert
        $this->assertFalse($validator->fails());
    }

    public function test_login_does_not_enforce_complexity_rules(): void
    {
        // Arrange
        $user = \App\Models\User::factory()->create();

        // Act
        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert — login succeeds with a simple password (complexity only enforced on creation)
        $this->assertNotNull($user->fresh()->last_login_at);
    }
}
