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

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_page_with_paginated_posts(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Post::factory()->count(3)->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/posts')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Posts/Index')
                ->has('posts.data', 3)
            );
    }

    public function test_super_admin_can_view_posts_index(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/posts')
            ->assertOk();
    }

    public function test_viewer_can_view_posts_index(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/posts')->assertOk();
    }

    public function test_viewer_only_sees_published_posts_in_index(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $author = User::factory()->create(['role' => UserRole::Editor]);
        Post::factory()->create(['user_id' => $author->id, 'status' => ContentStatus::Published]);
        Post::factory()->create(['user_id' => $author->id, 'status' => ContentStatus::Draft]);

        // Act + Assert
        $this->actingAs($viewer)
            ->get('/admin/posts')
            ->assertInertia(fn ($page) => $page->has('posts.data', 1));
    }

    public function test_guest_is_redirected_from_posts_index(): void
    {
        // Act + Assert
        $this->get('/admin/posts')->assertRedirect(route('admin.login'));
    }

    public function test_posts_index_eager_loads_relationships(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();
        $post     = Post::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);
        $tag      = Tag::factory()->create();
        $post->tags()->attach($tag);

        // Act + Assert — would throw LazyLoadingViolationException if not eager-loaded
        $this->actingAs($user)->get('/admin/posts')->assertOk();
    }

    public function test_trash_index_shows_only_soft_deleted_posts(): void
    {
        // Arrange
        $user    = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $active  = Post::factory()->create(['user_id' => $user->id]);
        $deleted = Post::factory()->create(['user_id' => $user->id]);
        $deleted->delete();

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/posts?trash=1')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Posts/Index')
                ->where('trash', true)
                ->has('posts.data', 1)
                ->where('posts.data.0.id', $deleted->id)
            );
    }

    // ─── create ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_create_post_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/posts/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Posts/Create')
                ->has('categories')
                ->has('tags')
            );
    }

    public function test_editor_can_view_create_post_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/posts/create')->assertOk();
    }

    public function test_viewer_cannot_view_create_post_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/posts/create')->assertForbidden();
    }

    public function test_guest_is_redirected_from_create_post_form(): void
    {
        $this->get('/admin/posts/create')->assertRedirect(route('admin.login'));
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

    public function test_store_requires_title(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', ['content' => 'Body', 'status' => 'draft'])
            ->assertSessionHasErrors('title');
    }

    public function test_store_requires_content(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', ['title' => 'Title', 'status' => 'draft'])
            ->assertSessionHasErrors('content');
    }

    public function test_store_requires_valid_status(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', ['title' => 'Title', 'content' => 'Body', 'status' => 'invalid'])
            ->assertSessionHasErrors('status');
    }

    public function test_store_auto_increments_duplicate_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Post::factory()->create(['slug' => 'existing-slug']);

        // Act
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'   => 'Title',
                'slug'    => 'existing-slug',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');

        // Assert
        $this->assertDatabaseHas('posts', ['slug' => 'existing-slug-1']);
    }

    public function test_store_auto_increments_slug_derived_from_title_on_collision(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Post::factory()->create(['slug' => 'duplicate-title']);
        Post::factory()->create(['slug' => 'duplicate-title-1']);

        // Act
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'   => 'Duplicate Title',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');

        // Assert
        $this->assertDatabaseHas('posts', ['slug' => 'duplicate-title-2']);
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

    // ─── is_featured ─────────────────────────────────────────────────────────

    public function test_store_saves_is_featured_when_true(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'       => 'Featured Post',
            'content'     => 'Body',
            'status'      => 'draft',
            'is_featured' => true,
        ]);

        // Assert
        $this->assertDatabaseHas('posts', ['title' => 'Featured Post', 'is_featured' => true]);
    }

    public function test_store_defaults_is_featured_to_false_when_omitted(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'   => 'Regular Post',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertDatabaseHas('posts', ['title' => 'Regular Post', 'is_featured' => false]);
    }

    public function test_update_can_toggle_is_featured_on(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['user_id' => $user->id, 'is_featured' => false]);

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'       => $post->title,
            'content'     => $post->content,
            'status'      => 'draft',
            'is_featured' => true,
        ]);

        // Assert
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_featured' => true]);
    }

    public function test_update_can_toggle_is_featured_off(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['user_id' => $user->id, 'is_featured' => true]);

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'       => $post->title,
            'content'     => $post->content,
            'status'      => 'draft',
            'is_featured' => false,
        ]);

        // Assert
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_featured' => false]);
    }

    // ─── edit ─────────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_post_edit_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/posts/{$post->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Posts/Edit')
                ->has('post')
                ->has('categories')
                ->has('tags')
            );
    }

    public function test_editor_can_view_own_post_edit_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)->get("/admin/posts/{$post->id}/edit")->assertOk();
    }

    public function test_editor_cannot_view_other_users_post_edit_form(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id]);

        // Act + Assert
        $this->actingAs($editor)->get("/admin/posts/{$post->id}/edit")->assertForbidden();
    }

    public function test_viewer_cannot_view_post_edit_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $post = Post::factory()->create();

        // Act + Assert
        $this->actingAs($user)->get("/admin/posts/{$post->id}/edit")->assertForbidden();
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

    public function test_update_ignores_own_slug_in_uniqueness_check(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['slug' => 'my-post']);

        // Act + Assert — re-submitting the post's own slug must not fail validation
        $this->actingAs($user)
            ->put("/admin/posts/{$post->id}", [
                'title'   => 'My Post',
                'slug'    => 'my-post',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');
    }

    public function test_update_auto_increments_slug_already_used_by_another_post(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $other = Post::factory()->create(['slug' => 'taken-slug']);
        $post  = Post::factory()->create(['slug' => 'my-post']);

        // Act
        $this->actingAs($user)
            ->put("/admin/posts/{$post->id}", [
                'title'   => 'My Post',
                'slug'    => 'taken-slug',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');

        // Assert
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'slug' => 'taken-slug-1']);
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

    // ─── autosave ─────────────────────────────────────────────────────────────

    public function test_autosave_stores_draft_without_touching_updated_at(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subHour()]);
        $before = $post->updated_at->toIso8601String();

        // Act
        $this->actingAs($user)
            ->postJson("/admin/posts/{$post->id}/autosave", ['content' => '<p>Draft content</p>'])
            ->assertOk()
            ->assertJsonStructure(['saved_at']);

        // Assert — content stored, timestamp untouched
        $fresh = $post->fresh();
        $this->assertEquals('<p>Draft content</p>', $fresh->autosave_json['content']);
        $this->assertSame($before, $fresh->updated_at->toIso8601String());
    }

    public function test_autosave_requires_content(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->postJson("/admin/posts/{$post->id}/autosave", [])
            ->assertUnprocessable();
    }

    public function test_viewer_cannot_autosave_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $post = Post::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->postJson("/admin/posts/{$post->id}/autosave", ['content' => '<p>Draft</p>'])
            ->assertForbidden();
    }

    public function test_update_clears_autosave_json(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create();
        $post->forceFill(['autosave_json' => ['content' => '<p>Draft</p>']])->save();

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'   => 'Updated',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertNull($post->fresh()->autosave_json);
    }

    public function test_edit_passes_autosave_draft_prop_when_draft_exists(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create();
        $post->forceFill(['autosave_json' => ['content' => '<p>Draft</p>']])->save();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/posts/{$post->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Posts/Edit')
                ->where('autosaveDraft', '<p>Draft</p>')
            );
    }

    public function test_edit_passes_null_autosave_draft_prop_when_no_draft(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/posts/{$post->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Posts/Edit')
                ->where('autosaveDraft', null)
            );
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

    // ─── restore ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_restore_soft_deleted_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $post->delete();

        // Act
        $this->actingAs($user)->post("/admin/posts/{$post->id}/restore");

        // Assert
        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_editor_can_restore_own_soft_deleted_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $post->delete();

        // Act + Assert
        $this->actingAs($user)
            ->post("/admin/posts/{$post->id}/restore")
            ->assertRedirect();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_editor_cannot_restore_another_users_soft_deleted_post(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id]);
        $post->delete();

        // Act + Assert
        $this->actingAs($editor)
            ->post("/admin/posts/{$post->id}/restore")
            ->assertForbidden();
    }

    public function test_viewer_cannot_restore_post(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id]);
        $post->delete();

        // Act + Assert
        $this->actingAs($viewer)
            ->post("/admin/posts/{$post->id}/restore")
            ->assertForbidden();
    }

    // ─── forceDelete ──────────────────────────────────────────────────────────

    public function test_super_admin_can_force_delete_soft_deleted_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $post->delete();

        // Act
        $this->actingAs($user)->delete("/admin/posts/{$post->id}/force-delete");

        // Assert
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_editor_cannot_force_delete_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $post->delete();

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/posts/{$post->id}/force-delete")
            ->assertForbidden();
    }

    public function test_viewer_cannot_force_delete_post(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id]);
        $post->delete();

        // Act + Assert
        $this->actingAs($viewer)
            ->delete("/admin/posts/{$post->id}/force-delete")
            ->assertForbidden();
    }

    // ─── duplicate ────────────────────────────────────────────────────────────

    public function test_super_admin_can_duplicate_any_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['title' => 'Original Post', 'status' => ContentStatus::Published]);

        // Act
        $response = $this->actingAs($user)->post("/admin/posts/{$post->id}/duplicate");

        // Assert
        $copy = Post::where('title', 'Original Post (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(ContentStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
        $this->assertEquals($user->id, $copy->user_id);
        $response->assertRedirect("/admin/posts/{$copy->id}/edit");
    }

    public function test_editor_can_duplicate_own_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $post = Post::factory()->create(['user_id' => $user->id, 'title' => 'My Post']);

        // Act + Assert
        $this->actingAs($user)
            ->post("/admin/posts/{$post->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('posts', ['title' => 'My Post (Copy)', 'user_id' => $user->id]);
    }

    public function test_editor_can_duplicate_another_editors_post(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $post   = Post::factory()->create(['user_id' => $owner->id, 'title' => 'Shared Post']);

        // Act + Assert — duplicate uses create permission, not ownership
        $this->actingAs($editor)
            ->post("/admin/posts/{$post->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('posts', ['title' => 'Shared Post (Copy)', 'user_id' => $editor->id]);
    }

    public function test_viewer_cannot_duplicate_post(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $post   = Post::factory()->create();

        // Act + Assert
        $this->actingAs($viewer)
            ->post("/admin/posts/{$post->id}/duplicate")
            ->assertForbidden();
    }

    public function test_duplicate_copies_tags(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tags = Tag::factory()->count(2)->create();
        $post = Post::factory()->create(['title' => 'Tagged Post']);
        $post->tags()->sync($tags->pluck('id'));

        // Act
        $this->actingAs($user)->post("/admin/posts/{$post->id}/duplicate");

        // Assert
        $copy = Post::where('title', 'Tagged Post (Copy)')->with('tags')->first();
        $this->assertNotNull($copy);
        $this->assertEqualsCanonicalizing($tags->pluck('id')->toArray(), $copy->tags->pluck('id')->toArray());
    }

    public function test_duplicate_sets_draft_status_even_when_original_is_published(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create([
            'title'        => 'Live Post',
            'status'       => ContentStatus::Published,
            'published_at' => now(),
        ]);

        // Act
        $this->actingAs($user)->post("/admin/posts/{$post->id}/duplicate");

        // Assert
        $copy = Post::where('title', 'Live Post (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(ContentStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
    }

    // ─── og fields ────────────────────────────────────────────────────────────

    public function test_store_saves_og_fields(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/posts', [
            'title'          => 'OG Post',
            'content'        => 'Body',
            'status'         => 'draft',
            'og_title'       => 'Custom OG Title',
            'og_description' => 'Custom OG description for social sharing.',
        ]);

        // Assert
        $this->assertDatabaseHas('posts', [
            'og_title'       => 'Custom OG Title',
            'og_description' => 'Custom OG description for social sharing.',
        ]);
    }

    public function test_store_accepts_null_og_fields(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert — OG fields are optional
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'   => 'No OG Post',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/posts');

        $this->assertDatabaseHas('posts', ['og_title' => null, 'og_description' => null]);
    }

    public function test_store_rejects_og_title_exceeding_max_length(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'    => 'Post',
                'content'  => 'Body',
                'status'   => 'draft',
                'og_title' => str_repeat('a', 256),
            ])
            ->assertSessionHasErrors('og_title');
    }

    public function test_store_rejects_og_description_exceeding_max_length(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/posts', [
                'title'          => 'Post',
                'content'        => 'Body',
                'status'         => 'draft',
                'og_description' => str_repeat('a', 501),
            ])
            ->assertSessionHasErrors('og_description');
    }

    public function test_update_saves_og_fields(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create();

        // Act
        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'title'          => 'Updated',
            'content'        => 'Body',
            'status'         => 'draft',
            'og_title'       => 'Updated OG Title',
            'og_description' => 'Updated OG description.',
        ]);

        // Assert
        $this->assertDatabaseHas('posts', [
            'id'             => $post->id,
            'og_title'       => 'Updated OG Title',
            'og_description' => 'Updated OG description.',
        ]);
    }
}
