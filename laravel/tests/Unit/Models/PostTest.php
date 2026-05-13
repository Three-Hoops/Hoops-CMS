<?php

namespace Tests\Unit\Models;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_be_created_with_factory(): void
    {
        // Arrange + Act
        $post = Post::factory()->create();

        // Assert
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_post_status_is_cast_to_content_status_enum(): void
    {
        // Arrange + Act
        $post = Post::factory()->create(['status' => ContentStatus::Published]);

        // Assert
        $this->assertInstanceOf(ContentStatus::class, $post->status);
    }

    public function test_post_has_author_relationship(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->assertTrue($post->author->is($user));
    }

    public function test_post_has_category_relationship(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);

        // Act + Assert
        $this->assertTrue($post->category->is($category));
    }

    public function test_post_has_tags_relationship(): void
    {
        // Arrange
        $post = Post::factory()->create();
        $tags = Tag::factory()->count(3)->create();
        $post->tags()->attach($tags);

        // Act + Assert
        $this->assertCount(3, $post->tags);
    }

    public function test_scope_published_returns_only_published_posts(): void
    {
        // Arrange
        Post::factory()->create(['status' => ContentStatus::Published]);
        Post::factory()->create(['status' => ContentStatus::Draft]);

        // Act
        $results = Post::published()->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals(ContentStatus::Published, $results->first()->status);
    }

    public function test_post_is_soft_deleted(): void
    {
        // Arrange
        $post = Post::factory()->create();

        // Act
        $post->delete();

        // Assert
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_post_category_id_is_nullable(): void
    {
        // Arrange + Act
        $post = Post::factory()->create(['category_id' => null]);

        // Assert
        $this->assertNull($post->category_id);
    }
}
