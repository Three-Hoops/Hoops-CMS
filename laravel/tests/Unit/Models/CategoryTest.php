<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created_with_factory(): void
    {
        // Arrange + Act
        $category = Category::factory()->create();

        // Assert
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_has_parent_relationship(): void
    {
        // Arrange
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        // Act + Assert
        $this->assertTrue($child->parent->is($parent));
    }

    public function test_category_has_children_relationship(): void
    {
        // Arrange
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        // Act + Assert
        $this->assertTrue($parent->children->contains($child));
    }

    public function test_category_parent_id_is_nullable(): void
    {
        // Arrange + Act
        $category = Category::factory()->create(['parent_id' => null]);

        // Assert
        $this->assertNull($category->parent_id);
    }

    public function test_category_has_posts_relationship(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $posts = Post::factory()->count(2)->create(['category_id' => $category->id]);

        // Act
        $related = $category->posts;

        // Assert
        $this->assertCount(2, $related);
        $this->assertTrue($related->contains($posts->first()));
        $this->assertTrue($related->contains($posts->last()));
    }

    public function test_category_posts_returns_empty_when_no_posts(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act + Assert
        $this->assertCount(0, $category->posts);
    }
}
