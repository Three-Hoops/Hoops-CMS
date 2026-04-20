<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_renders_inertia_error_page(): void
    {
        // Arrange
        $this->withoutVite();

        // Act
        $response = $this->get('/non-existent-route');

        // Assert
        $response->assertStatus(404);
        $response->assertInertia(fn ($page) => $page
            ->component('Error', shouldExist: false)
            ->where('status', 404)
        );
    }

    public function test_error_page_http_status_matches_status_prop(): void
    {
        // Arrange
        $this->withoutVite();

        // Act
        $response = $this->get('/non-existent-route');

        // Assert — HTTP status code and Inertia prop are in sync
        $response->assertStatus(404);
        $response->assertInertia(fn ($page) => $page->where('status', 404));
    }
}
