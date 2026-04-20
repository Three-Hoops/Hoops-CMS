<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_can_be_created_with_factory(): void
    {
        // Arrange + Act
        $tag = Tag::factory()->create();

        // Assert
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_tag_has_posts_relationship(): void
    {
        // Arrange
        $tag = Tag::factory()->create();
        $post = Post::factory()->create();
        $post->tags()->attach($tag);

        // Act + Assert
        $this->assertTrue($tag->posts->contains($post));
    }
}
