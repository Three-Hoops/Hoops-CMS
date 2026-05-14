<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── publish ─────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_publish_pages(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $pages = Page::factory()->count(3)->create(['user_id' => $user->id, 'status' => ContentStatus::Draft]);

        // Act
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => $pages->pluck('id')->toArray(),
            'action' => 'publish',
        ]);

        // Assert
        $this->assertCount(3, Page::where('status', ContentStatus::Published)->get());
    }

    public function test_editor_can_bulk_publish_own_pages(): void
    {
        // Arrange
        $editor  = User::factory()->create(['role' => UserRole::Editor]);
        $other   = User::factory()->create(['role' => UserRole::Editor]);
        $own     = Page::factory()->create(['user_id' => $editor->id, 'status' => ContentStatus::Draft]);
        $foreign = Page::factory()->create(['user_id' => $other->id, 'status' => ContentStatus::Draft]);

        // Act
        $this->actingAs($editor)->post('/admin/pages/bulk-action', [
            'ids'    => [$own->id, $foreign->id],
            'action' => 'publish',
        ]);

        // Assert — own page published, foreign page untouched
        $this->assertEquals(ContentStatus::Published, $own->fresh()->status);
        $this->assertEquals(ContentStatus::Draft, $foreign->fresh()->status);
    }

    public function test_bulk_publish_sets_published_at_when_null(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['user_id' => $user->id, 'status' => ContentStatus::Draft, 'published_at' => null]);

        // Act
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => [$page->id],
            'action' => 'publish',
        ]);

        // Assert
        $this->assertNotNull($page->fresh()->published_at);
    }

    // ─── draft ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_set_pages_to_draft(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $pages = Page::factory()->count(2)->create(['user_id' => $user->id, 'status' => ContentStatus::Published]);

        // Act
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => $pages->pluck('id')->toArray(),
            'action' => 'draft',
        ]);

        // Assert
        $this->assertCount(2, Page::where('status', ContentStatus::Draft)->get());
    }

    // ─── delete ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_delete_pages(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $pages = Page::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => $pages->pluck('id')->toArray(),
            'action' => 'delete',
        ]);

        // Assert — soft deleted
        $this->assertCount(0, Page::all());
        $this->assertCount(3, Page::onlyTrashed()->get());
    }

    public function test_editor_cannot_bulk_delete_other_users_pages(): void
    {
        // Arrange
        $editor  = User::factory()->create(['role' => UserRole::Editor]);
        $other   = User::factory()->create(['role' => UserRole::Editor]);
        $foreign = Page::factory()->create(['user_id' => $other->id]);

        // Act
        $this->actingAs($editor)->post('/admin/pages/bulk-action', [
            'ids'    => [$foreign->id],
            'action' => 'delete',
        ]);

        // Assert — foreign page still exists
        $this->assertNotNull(Page::find($foreign->id));
    }

    // ─── restore ─────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_restore_pages(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $pages = Page::factory()->count(2)->create(['user_id' => $user->id]);
        $pages->each->delete();

        // Act
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => $pages->pluck('id')->toArray(),
            'action' => 'restore',
        ]);

        // Assert
        $this->assertCount(2, Page::all());
    }

    // ─── authorisation / validation ──────────────────────────────────────────

    public function test_viewer_cannot_perform_bulk_actions(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $page   = Page::factory()->create(['user_id' => $viewer->id]);

        // Act + Assert
        $this->actingAs($viewer)->post('/admin/pages/bulk-action', [
            'ids'    => [$page->id],
            'action' => 'delete',
        ])->assertForbidden();
    }

    public function test_guest_cannot_perform_bulk_actions(): void
    {
        // Act + Assert
        $this->post('/admin/pages/bulk-action', [
            'ids'    => [1],
            'action' => 'delete',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_invalid_action_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = Page::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => [$page->id],
            'action' => 'nuke',
        ])->assertSessionHasErrors('action');
    }

    public function test_empty_ids_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)->post('/admin/pages/bulk-action', [
            'ids'    => [],
            'action' => 'delete',
        ])->assertSessionHasErrors('ids');
    }
}
