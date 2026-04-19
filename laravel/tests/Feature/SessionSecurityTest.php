<?php

namespace Tests\Feature;

use App\Http\Middleware\AbsoluteSessionTimeout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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
        $response = $this->get('/admin');

        // Assert
        $response->assertRedirect('/admin/login');
    }

    public function test_absolute_session_timeout_redirects_expired_session(): void
    {
        // Arrange
        $request = Request::create('/admin', 'GET');
        $session = app('session')->driver('array');
        $session->start();
        $session->put('session_started_at', now()->subHours(9));
        $request->setLaravelSession($session);

        // Act
        $middleware = new AbsoluteSessionTimeout();
        $response = $middleware->handle($request, fn ($req) => response('OK'));

        // Assert
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_absolute_session_timeout_allows_active_session(): void
    {
        // Arrange
        $request = Request::create('/admin', 'GET');
        $session = app('session')->driver('array');
        $session->start();
        $session->put('session_started_at', now()->subHours(1));
        $request->setLaravelSession($session);

        // Act
        $middleware = new AbsoluteSessionTimeout();
        $response = $middleware->handle($request, fn ($req) => response('OK'));

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_absolute_session_timeout_stamps_session_on_first_request(): void
    {
        // Arrange
        $request = Request::create('/admin', 'GET');
        $session = app('session')->driver('array');
        $session->start();
        $request->setLaravelSession($session);

        // Act
        $middleware = new AbsoluteSessionTimeout();
        $middleware->handle($request, fn ($req) => response('OK'));

        // Assert
        $this->assertTrue($session->has('session_started_at'));
    }
}
