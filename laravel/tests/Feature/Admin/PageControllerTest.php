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

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_pages_index(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/pages')
            ->assertOk();
    }

    public function test_editor_can_view_pages_index(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/pages')->assertOk();
    }

    public function test_viewer_can_view_pages_index(): void
    {
        // Arrange
        $this->withoutVite();
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/pages')->assertOk();
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
        $this->withoutVite();
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

    public function test_store_rejects_duplicate_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Page::factory()->create(['slug' => 'existing-slug']);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/pages', [
                'title'   => 'Title',
                'slug'    => 'existing-slug',
                'content' => 'Body',
                'status'  => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    // ─── edit ─────────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_edit_page_form(): void
    {
        // Arrange
        $this->withoutVite();
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
        $this->withoutVite();
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
}
