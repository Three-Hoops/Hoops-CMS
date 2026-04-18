<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_login_at_is_set_on_successful_login(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->last_login_at);

        $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_last_login_at_is_not_set_on_failed_login(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_last_login_at_is_updated_on_subsequent_login(): void
    {
        $user = User::factory()->create(['last_login_at' => now()->subDay()]);
        $previous = $user->last_login_at;

        $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertTrue($user->fresh()->last_login_at->isAfter($previous));
    }

    public function test_last_login_at_is_shared_in_inertia_auth_prop(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->has('auth.last_login_at')
            );
    }
}
