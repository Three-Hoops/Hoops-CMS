<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_base_slug_when_no_collision(): void
    {
        // Arrange + Act
        $slug = UniqueSlug::generate('My Post', 'posts');

        // Assert
        $this->assertSame('my-post', $slug);
    }

    public function test_increments_to_1_when_base_slug_exists(): void
    {
        // Arrange
        Post::factory()->create(['slug' => 'my-post']);

        // Act
        $slug = UniqueSlug::generate('My Post', 'posts');

        // Assert
        $this->assertSame('my-post-1', $slug);
    }

    public function test_increments_to_2_when_base_and_1_both_exist(): void
    {
        // Arrange
        Post::factory()->create(['slug' => 'my-post']);
        Post::factory()->create(['slug' => 'my-post-1']);

        // Act
        $slug = UniqueSlug::generate('My Post', 'posts');

        // Assert
        $this->assertSame('my-post-2', $slug);
    }

    public function test_ignore_id_allows_keeping_own_slug(): void
    {
        // Arrange
        $post = Post::factory()->create(['slug' => 'my-post']);

        // Act
        $slug = UniqueSlug::generate('my-post', 'posts', $post->id);

        // Assert
        $this->assertSame('my-post', $slug);
    }

    public function test_ignore_id_still_increments_when_another_record_has_slug(): void
    {
        // Arrange
        $post        = Post::factory()->create(['slug' => 'my-post']);
        $otherPost   = Post::factory()->create(['slug' => 'other-post']);

        // Act — trying to claim 'other-post' but it belongs to a different record
        $slug = UniqueSlug::generate('other-post', 'posts', $post->id);

        // Assert
        $this->assertSame('other-post-1', $slug);
    }

    public function test_slugifies_input_title(): void
    {
        // Arrange + Act
        $slug = UniqueSlug::generate('Hello World! This is a Test', 'posts');

        // Assert
        $this->assertSame('hello-world-this-is-a-test', $slug);
    }
}
