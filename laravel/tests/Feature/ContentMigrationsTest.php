<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_table_has_expected_columns(): void
    {
        // Arrange + Act + Assert
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumns('categories', [
            'id',
            'name',
            'slug',
            'description',
            'parent_id',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_tags_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('tags'));
        $this->assertTrue(Schema::hasColumns('tags', [
            'id',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_pages_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('pages'));
        $this->assertTrue(Schema::hasColumns('pages', [
            'id',
            'title',
            'slug',
            'content',
            'content_json',
            'excerpt',
            'status',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'user_id',
            'published_at',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_posts_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasColumns('posts', [
            'id',
            'title',
            'slug',
            'content',
            'content_json',
            'excerpt',
            'status',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'user_id',
            'featured_image',
            'category_id',
            'published_at',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_post_tag_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('post_tag'));
        $this->assertTrue(Schema::hasColumns('post_tag', [
            'post_id',
            'tag_id',
        ]));
    }

    public function test_pages_status_defaults_to_draft(): void
    {
        // Arrange
        $column = Schema::getColumns('pages');
        $status = collect($column)->firstWhere('name', 'status');

        // Assert
        $this->assertStringContainsString('draft', $status['default']);
    }

    public function test_posts_status_defaults_to_draft(): void
    {
        // Arrange
        $column = Schema::getColumns('posts');
        $status = collect($column)->firstWhere('name', 'status');

        // Assert
        $this->assertStringContainsString('draft', $status['default']);
    }

    public function test_categories_parent_id_is_nullable(): void
    {
        // Arrange
        $columns = Schema::getColumns('categories');
        $parentId = collect($columns)->firstWhere('name', 'parent_id');

        // Assert
        $this->assertTrue($parentId['nullable']);
    }

    public function test_pages_soft_deletes_column_is_nullable(): void
    {
        // Arrange
        $columns = Schema::getColumns('pages');
        $deletedAt = collect($columns)->firstWhere('name', 'deleted_at');

        // Assert
        $this->assertTrue($deletedAt['nullable']);
    }

    public function test_posts_soft_deletes_column_is_nullable(): void
    {
        // Arrange
        $columns = Schema::getColumns('posts');
        $deletedAt = collect($columns)->firstWhere('name', 'deleted_at');

        // Assert
        $this->assertTrue($deletedAt['nullable']);
    }
}
