<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_posts_index(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Posts/Index'));
    }

    public function test_viewer_can_view_posts_index(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/posts')->assertOk();
    }

    public function test_guest_is_redirected_from_posts_index(): void
    {
        // Act + Assert
        $this->get('/admin/posts')->assertRedirect(route('admin.login'));
    }

    public function test_posts_index_eager_loads_relationships(): void
    {
        // Arrange
        $this->withoutVite();
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();
        $post     = Post::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);
        $tag      = Tag::factory()->create();
        $post->tags()->attach($tag);

        // Act + Assert — would throw LazyLoadingViolationException if not eager-loaded
        $this->actingAs($user)->get('/admin/posts')->assertOk();
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_super_admin_can_create_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $response = $this->actingAs($user)->post('/admin/posts', [
            'title'   => 'My Test Post',
            'content' => 'Post content here',
            'status'  => 'draft',
        ]);

        // Assert
        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', [
            'title'   => 'My Test Post',
            'slug'    => 'my-test-post',
            'user_id' => $user->id,
        ]);
    }

    public function test_editor_can_create_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'   => 'Editor Post',
                'content' => 'Content',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');
    }

    public function test_viewer_cannot_create_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'   => 'Viewer Post',
                'content' => 'Content',
                'status'  => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_store_generates_slug_from_title(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'   => 'Hello World Post',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertDatabaseHas('posts', ['slug' => 'hello-world-post']);
    }

    public function test_store_syncs_tags(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'   => 'Tagged Post',
            'content' => 'Body',
            'status'  => 'draft',
            'tag_ids' => [$tag1->id, $tag2->id],
        ]);

        // Assert
        $post = Post::first();
        $this->assertCount(2, $post->tags);
        $this->assertTrue($post->tags->contains($tag1));
        $this->assertTrue($post->tags->contains($tag2));
    }

    public function test_store_with_no_tags_results_in_empty_pivot(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'   => 'Untagged Post',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertCount(0, Post::first()->tags);
    }

    public function test_store_sets_published_at_when_status_is_published(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'   => 'Live Post',
            'content' => 'Content',
            'status'  => 'published',
        ]);

        // Assert
        $this->assertNotNull(Post::first()->published_at);
    }

    public function test_store_validates_category_exists(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'       => 'Post',
                'content'     => 'Body',
                'status'      => 'draft',
                'category_id' => 99999,
            ])
            ->assertSessionHasErrors('category_id');
    }

    public function test_store_validates_tag_ids_exist(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'   => 'Post',
                'content' => 'Body',
                'status'  => 'draft',
                'tag_ids' => [99999],
            ])
            ->assertSessionHasErrors('tag_ids.0');
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_update_any_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['title' => 'Old Title']);

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'   => 'New Title',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'New Title']);
    }

    public function test_editor_can_update_own_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/posts/{$post->id}", [
                'title'   => 'Updated',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');
    }

    public function test_editor_cannot_update_other_users_post(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id]);

        // Act + Assert
        $this->actingAs($editor)
            ->put("/admin/posts/{$post->id}", [
                'title'   => 'Hack',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_update_syncs_tags_replacing_previous_tags(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $oldTag   = Tag::factory()->create();
        $newTag   = Tag::factory()->create();
        $post     = Post::factory()->create(['user_id' => $user->id]);
        $post->tags()->attach($oldTag);

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'   => 'Updated',
            'content' => 'Body',
            'status'  => 'draft',
            'tag_ids' => [$newTag->id],
        ]);

        // Assert
        $post->refresh();
        $this->assertCount(1, $post->tags);
        $this->assertTrue($post->tags->contains($newTag));
        $this->assertFalse($post->tags->contains($oldTag));
    }

    public function test_update_with_empty_tag_ids_detaches_all_tags(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag  = Tag::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $post->tags()->attach($tag);

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'   => 'Updated',
            'content' => 'Body',
            'status'  => 'draft',
            'tag_ids' => [],
        ]);

        // Assert
        $this->assertCount(0, $post->fresh()->tags);
    }

    public function test_update_does_not_reset_published_at(): void
    {
        // Arrange
        $user        = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $publishedAt = now()->subDay();
        $post        = Post::factory()->create([
            'status'       => ContentStatus::Published,
            'published_at' => $publishedAt,
        ]);

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'   => 'Re-save',
            'content' => 'Body',
            'status'  => 'published',
        ]);

        // Assert
        $this->assertDatabaseHas('posts', [
            'id'           => $post->id,
            'published_at' => $publishedAt->toDateTimeString(),
        ]);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_delete_any_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create();

        // Act
        $this->actingAs($user)->delete("/admin/posts/{$post->id}");

        // Assert
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_editor_can_delete_own_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/posts/{$post->id}")
            ->assertRedirect('/admin/posts');

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_editor_cannot_delete_other_users_post(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id]);

        // Act + Assert
        $this->actingAs($editor)->delete("/admin/posts/{$post->id}")->assertForbidden();
    }
}
