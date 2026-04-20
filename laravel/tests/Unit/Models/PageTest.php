<?php

namespace Tests\Unit\Models;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_can_be_created_with_factory(): void
    {
        // Arrange + Act
        $page = Page::factory()->create();

        // Assert
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function test_page_status_is_cast_to_content_status_enum(): void
    {
        // Arrange + Act
        $page = Page::factory()->create(['status' => ContentStatus::Draft]);

        // Assert
        $this->assertInstanceOf(ContentStatus::class, $page->status);
    }

    public function test_page_status_defaults_to_draft(): void
    {
        // Arrange + Act
        $page = Page::factory()->create(['status' => ContentStatus::Draft]);

        // Assert
        $this->assertEquals(ContentStatus::Draft, $page->status);
    }

    public function test_page_has_author_relationship(): void
    {
        // Arrange
        $user = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->assertTrue($page->author->is($user));
    }

    public function test_scope_published_returns_only_published_pages(): void
    {
        // Arrange
        Page::factory()->create(['status' => ContentStatus::Published]);
        Page::factory()->create(['status' => ContentStatus::Draft]);

        // Act
        $results = Page::published()->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals(ContentStatus::Published, $results->first()->status);
    }

    public function test_page_is_soft_deleted(): void
    {
        // Arrange
        $page = Page::factory()->create();

        // Act
        $page->delete();

        // Assert
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }
}
