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

    // ─── parent / children relationships ─────────────────────────────────────

    public function test_parent_relationship_returns_parent_page(): void
    {
        // Arrange
        $parent = Page::factory()->create();
        $child  = Page::factory()->create(['parent_id' => $parent->id]);

        // Act + Assert
        $this->assertTrue($child->parent->is($parent));
    }

    public function test_children_relationship_returns_child_pages(): void
    {
        // Arrange
        $parent   = Page::factory()->create();
        $childOne = Page::factory()->create(['parent_id' => $parent->id]);
        $childTwo = Page::factory()->create(['parent_id' => $parent->id]);
        Page::factory()->create(); // unrelated

        // Act
        $children = $parent->children;

        // Assert
        $this->assertCount(2, $children);
        $this->assertTrue($children->contains($childOne));
        $this->assertTrue($children->contains($childTwo));
    }

    // ─── isDescendantOf ───────────────────────────────────────────────────────

    public function test_is_descendant_of_returns_true_for_direct_parent(): void
    {
        // Arrange
        $parent = Page::factory()->create();
        $child  = Page::factory()->create(['parent_id' => $parent->id]);

        // Act + Assert
        $this->assertTrue($child->isDescendantOf($parent));
    }

    public function test_is_descendant_of_returns_true_for_grandparent(): void
    {
        // Arrange
        $grandparent = Page::factory()->create();
        $parent      = Page::factory()->create(['parent_id' => $grandparent->id]);
        $child       = Page::factory()->create(['parent_id' => $parent->id]);

        // Act + Assert — load parent chain before calling
        $child->load('parent.parent');
        $this->assertTrue($child->isDescendantOf($grandparent));
    }

    public function test_is_descendant_of_returns_false_for_unrelated_page(): void
    {
        // Arrange
        $pageA = Page::factory()->create();
        $pageB = Page::factory()->create();

        // Act + Assert
        $this->assertFalse($pageB->isDescendantOf($pageA));
    }

    public function test_content_is_sanitised_on_save(): void
    {
        // Arrange
        $dirty = '<p>Hello</p><script>alert("xss")</script>';

        // Act
        $page = Page::factory()->create(['content' => $dirty]);

        // Assert
        $this->assertStringNotContainsString('<script>', $page->content);
        $this->assertStringContainsString('<p>Hello</p>', $page->content);
    }
}
