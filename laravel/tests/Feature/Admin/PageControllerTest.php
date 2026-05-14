<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_pages_index(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/pages')
            ->assertOk();
    }

    public function test_editor_can_view_pages_index(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/pages')->assertOk();
    }

    public function test_viewer_can_view_pages_index(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/pages')->assertOk();
    }

    public function test_viewer_only_sees_published_pages_in_index(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $author = User::factory()->create(['role' => UserRole::Editor]);
        Page::factory()->create(['user_id' => $author->id, 'status' => ContentStatus::Published]);
        Page::factory()->create(['user_id' => $author->id, 'status' => ContentStatus::Draft]);

        // Act + Assert
        $this->actingAs($viewer)
            ->get('/admin/pages')
            ->assertInertia(fn ($page) => $page->has('pages.data', 1));
    }

    public function test_guest_is_redirected_from_pages_index(): void
    {
        // Act + Assert
        $this->get('/admin/pages')->assertRedirect(route('admin.login'));
    }

    // ─── create ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_create_page_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/pages/create')
            ->assertOk();
    }

    public function test_viewer_cannot_view_create_page_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/pages/create')->assertForbidden();
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_super_admin_can_create_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $response = $this->actingAs($user)->post('/admin/pages', [
            'title'   => 'My Test Page',
            'content' => 'Some content here',
            'status'  => 'draft',
        ]);

        // Assert
        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'title'   => 'My Test Page',
            'slug'    => 'my-test-page',
            'user_id' => $user->id,
            'status'  => 'draft',
        ]);
    }

    public function test_editor_can_create_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', [
                'title'   => 'Editor Page',
                'content' => 'Content',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/pages');

        $this->assertDatabaseHas('pages', ['title' => 'Editor Page', 'user_id' => $user->id]);
    }

    public function test_viewer_cannot_create_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', [
                'title'   => 'Viewer Page',
                'content' => 'Content',
                'status'  => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_store_generates_slug_from_title_when_not_provided(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/pages', [
            'title'   => 'Hello World Page',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertDatabaseHas('pages', ['slug' => 'hello-world-page']);
    }

    public function test_store_uses_provided_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/pages', [
            'title'   => 'Hello World Page',
            'slug'    => 'custom-slug',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertDatabaseHas('pages', ['slug' => 'custom-slug']);
    }

    public function test_store_sets_published_at_when_publishing_for_first_time(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/pages', [
            'title'   => 'Published Page',
            'content' => 'Content',
            'status'  => 'published',
        ]);

        // Assert
        $this->assertNotNull(Page::first()->published_at);
    }

    public function test_store_does_not_override_explicit_published_at(): void
    {
        // Arrange
        $user        = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $publishedAt = '2025-01-15 10:00:00';

        // Act
        $this->actingAs($user)->post('/admin/pages', [
            'title'        => 'Scheduled Page',
            'content'      => 'Content',
            'status'       => 'published',
            'published_at' => $publishedAt,
        ]);

        // Assert
        $this->assertDatabaseHas('pages', ['published_at' => $publishedAt]);
    }

    public function test_store_requires_title(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', ['content' => 'Body', 'status' => 'draft'])
            ->assertSessionHasErrors('title');
    }

    public function test_store_requires_content(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', ['title' => 'Title', 'status' => 'draft'])
            ->assertSessionHasErrors('content');
    }

    public function test_store_requires_valid_status(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', ['title' => 'Title', 'content' => 'Body', 'status' => 'invalid'])
            ->assertSessionHasErrors('status');
    }

    public function test_store_auto_increments_duplicate_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Page::factory()->create(['slug' => 'existing-slug']);

        // Act
        $this->actingAs($user)
            ->post('/admin/pages', [
                'title'   => 'Title',
                'slug'    => 'existing-slug',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/pages');

        // Assert
        $this->assertDatabaseHas('pages', ['slug' => 'existing-slug-1']);
    }

    // ─── edit ─────────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_edit_page_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk();
    }

    public function test_editor_can_view_own_pages_edit_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)->get("/admin/pages/{$page->id}/edit")->assertOk();
    }

    public function test_editor_cannot_view_other_users_edit_form(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $page   = Page::factory()->create(['user_id' => $owner->id]);

        // Act + Assert
        $this->actingAs($editor)->get("/admin/pages/{$page->id}/edit")->assertForbidden();
    }

    public function test_viewer_cannot_view_edit_page_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $page = Page::factory()->create();

        // Act + Assert
        $this->actingAs($user)->get("/admin/pages/{$page->id}/edit")->assertForbidden();
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_update_any_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['title' => 'Old Title']);

        // Act
        $this->actingAs($user)->put("/admin/pages/{$page->id}", [
            'title'   => 'New Title',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'New Title']);
    }

    public function test_editor_can_update_own_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/pages/{$page->id}", [
                'title'   => 'Updated',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/pages');
    }

    public function test_editor_cannot_update_other_users_page(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $page   = Page::factory()->create(['user_id' => $owner->id]);

        // Act + Assert
        $this->actingAs($editor)
            ->put("/admin/pages/{$page->id}", [
                'title'   => 'Hack',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_update_ignores_own_slug_in_uniqueness_check(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['slug' => 'my-page']);

        // Act + Assert — submitting the page's own slug should not fail validation
        $this->actingAs($user)
            ->put("/admin/pages/{$page->id}", [
                'title'   => 'Updated Title',
                'slug'    => 'my-page',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertRedirect('/admin/pages');
    }

    public function test_update_does_not_reset_published_at_if_already_set(): void
    {
        // Arrange
        $user        = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $publishedAt = now()->subDay();
        $page        = Page::factory()->create([
            'status'       => ContentStatus::Published,
            'published_at' => $publishedAt,
        ]);

        // Act
        $this->actingAs($user)->put("/admin/pages/{$page->id}", [
            'title'   => 'Re-published',
            'content' => 'Body',
            'status'  => 'published',
        ]);

        // Assert — published_at should remain unchanged
        $this->assertDatabaseHas('pages', [
            'id'           => $page->id,
            'published_at' => $publishedAt->toDateTimeString(),
        ]);
    }

    public function test_update_sets_published_at_on_first_publish(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['status' => ContentStatus::Draft, 'published_at' => null]);

        // Act
        $this->actingAs($user)->put("/admin/pages/{$page->id}", [
            'title'   => 'Going Live',
            'content' => 'Body',
            'status'  => 'published',
        ]);

        // Assert
        $this->assertNotNull($page->fresh()->published_at);
    }

    // ─── autosave ─────────────────────────────────────────────────────────────

    public function test_autosave_stores_draft_without_touching_updated_at(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subHour()]);
        $before = $page->updated_at->toIso8601String();

        // Act
        $this->actingAs($user)
            ->postJson("/admin/pages/{$page->id}/autosave", ['content' => '<p>Draft content</p>'])
            ->assertOk()
            ->assertJsonStructure(['saved_at']);

        // Assert — content stored, timestamp untouched
        $fresh = $page->fresh();
        $this->assertEquals('<p>Draft content</p>', $fresh->autosave_json['content']);
        $this->assertSame($before, $fresh->updated_at->toIso8601String());
    }

    public function test_autosave_requires_content(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->postJson("/admin/pages/{$page->id}/autosave", [])
            ->assertUnprocessable();
    }

    public function test_viewer_cannot_autosave_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $page = Page::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->postJson("/admin/pages/{$page->id}/autosave", ['content' => '<p>Draft</p>'])
            ->assertForbidden();
    }

    public function test_update_clears_autosave_json(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();
        $page->forceFill(['autosave_json' => ['content' => '<p>Draft</p>']])->save();

        // Act
        $this->actingAs($user)->put("/admin/pages/{$page->id}", [
            'title'   => 'Updated',
            'content' => 'Body',
            'status'  => 'draft',
        ]);

        // Assert
        $this->assertNull($page->fresh()->autosave_json);
    }

    public function test_edit_passes_autosave_draft_prop_when_draft_exists(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();
        $page->forceFill(['autosave_json' => ['content' => '<p>Draft</p>']])->save();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/pages/{$page->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Pages/Edit')
                ->where('autosaveDraft', '<p>Draft</p>')
            );
    }

    public function test_edit_passes_null_autosave_draft_prop_when_no_draft(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/pages/{$page->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Pages/Edit')
                ->where('autosaveDraft', null)
            );
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_delete_any_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();

        // Act
        $this->actingAs($user)->delete("/admin/pages/{$page->id}");

        // Assert
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_editor_can_delete_own_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/pages/{$page->id}")
            ->assertRedirect('/admin/pages');

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_editor_cannot_delete_other_users_page(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $page   = Page::factory()->create(['user_id' => $owner->id]);

        // Act + Assert
        $this->actingAs($editor)->delete("/admin/pages/{$page->id}")->assertForbidden();
    }

    public function test_viewer_cannot_delete_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $page = Page::factory()->create();

        // Act + Assert
        $this->actingAs($user)->delete("/admin/pages/{$page->id}")->assertForbidden();
    }

    // ─── restore ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_restore_soft_deleted_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['user_id' => $user->id]);
        $page->delete();

        // Act
        $this->actingAs($user)->post("/admin/pages/{$page->id}/restore");

        // Assert
        $this->assertNotSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_editor_can_restore_own_soft_deleted_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id]);
        $page->delete();

        // Act + Assert
        $this->actingAs($user)
            ->post("/admin/pages/{$page->id}/restore")
            ->assertRedirect();

        $this->assertNotSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_editor_cannot_restore_another_users_soft_deleted_page(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $page   = Page::factory()->create(['user_id' => $owner->id]);
        $page->delete();

        // Act + Assert
        $this->actingAs($editor)
            ->post("/admin/pages/{$page->id}/restore")
            ->assertForbidden();
    }

    // ─── forceDelete ──────────────────────────────────────────────────────────

    public function test_super_admin_can_force_delete_soft_deleted_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['user_id' => $user->id]);
        $page->delete();

        // Act
        $this->actingAs($user)->delete("/admin/pages/{$page->id}/force-delete");

        // Assert
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_editor_cannot_force_delete_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id]);
        $page->delete();

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/pages/{$page->id}/force-delete")
            ->assertForbidden();
    }

    // ─── parent_id validation ─────────────────────────────────────────────────

    public function test_store_accepts_valid_parent_id(): void
    {
        // Arrange
        $user   = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $parent = Page::factory()->create();

        // Act
        $this->actingAs($user)->post('/admin/pages', [
            'title'     => 'Child Page',
            'content'   => 'Body',
            'status'    => 'draft',
            'parent_id' => $parent->id,
        ]);

        // Assert
        $this->assertDatabaseHas('pages', [
            'title'     => 'Child Page',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_store_rejects_non_existent_parent_id(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', [
                'title'     => 'Child Page',
                'content'   => 'Body',
                'status'    => 'draft',
                'parent_id' => 99999,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_update_accepts_valid_parent_id(): void
    {
        // Arrange
        $user   = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $parent = Page::factory()->create();
        $page   = Page::factory()->create();

        // Act
        $this->actingAs($user)->put("/admin/pages/{$page->id}", [
            'title'     => 'Updated',
            'content'   => 'Body',
            'status'    => 'draft',
            'parent_id' => $parent->id,
        ]);

        // Assert
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'parent_id' => $parent->id]);
    }

    public function test_update_rejects_assigning_page_as_its_own_parent(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/pages/{$page->id}", [
                'title'     => 'Title',
                'content'   => 'Body',
                'status'    => 'draft',
                'parent_id' => $page->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_update_rejects_assigning_descendant_as_parent(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page  = Page::factory()->create();
        $child = Page::factory()->create(['parent_id' => $page->id]);

        // Act + Assert — trying to make the ancestor a child of its own descendant
        $this->actingAs($user)
            ->put("/admin/pages/{$page->id}", [
                'title'     => 'Title',
                'content'   => 'Body',
                'status'    => 'draft',
                'parent_id' => $child->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    // ─── duplicate ────────────────────────────────────────────────────────────

    public function test_super_admin_can_duplicate_any_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['title' => 'Original Page', 'status' => ContentStatus::Published]);

        // Act
        $response = $this->actingAs($user)->post("/admin/pages/{$page->id}/duplicate");

        // Assert
        $copy = Page::where('title', 'Original Page (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(ContentStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
        $this->assertEquals($user->id, $copy->user_id);
        $response->assertRedirect("/admin/pages/{$copy->id}/edit");
    }

    public function test_editor_can_duplicate_own_page(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $page = Page::factory()->create(['user_id' => $user->id, 'title' => 'My Page']);

        // Act + Assert
        $this->actingAs($user)
            ->post("/admin/pages/{$page->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('pages', ['title' => 'My Page (Copy)', 'user_id' => $user->id]);
    }

    public function test_editor_can_duplicate_another_editors_page(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $owner  = User::factory()->create(['role' => UserRole::Editor]);
        $page   = Page::factory()->create(['user_id' => $owner->id, 'title' => 'Shared Page']);

        // Act + Assert — duplicate uses create permission, not ownership
        $this->actingAs($editor)
            ->post("/admin/pages/{$page->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('pages', ['title' => 'Shared Page (Copy)', 'user_id' => $editor->id]);
    }

    public function test_viewer_cannot_duplicate_page(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $page   = Page::factory()->create();

        // Act + Assert
        $this->actingAs($viewer)
            ->post("/admin/pages/{$page->id}/duplicate")
            ->assertForbidden();
    }

    public function test_duplicate_preserves_parent_id(): void
    {
        // Arrange
        $user   = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $parent = Page::factory()->create(['title' => 'Parent Page']);
        $page   = Page::factory()->create(['title' => 'Child Page', 'parent_id' => $parent->id]);

        // Act
        $this->actingAs($user)->post("/admin/pages/{$page->id}/duplicate");

        // Assert
        $copy = Page::where('title', 'Child Page (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertEquals($parent->id, $copy->parent_id);
    }

    public function test_duplicate_sets_draft_status_even_when_original_is_published(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create([
            'title'        => 'Live Page',
            'status'       => ContentStatus::Published,
            'published_at' => now(),
        ]);

        // Act
        $this->actingAs($user)->post("/admin/pages/{$page->id}/duplicate");

        // Assert
        $copy = Page::where('title', 'Live Page (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(ContentStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
    }
}
