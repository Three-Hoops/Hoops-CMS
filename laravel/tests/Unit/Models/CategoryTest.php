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
}
