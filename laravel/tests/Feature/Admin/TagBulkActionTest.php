<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── delete ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_delete_tags(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tags = Tag::factory()->count(3)->create();

        // Act
        $this->actingAs($user)->post('/admin/tags/bulk-action', [
            'ids'    => $tags->pluck('id')->toArray(),
            'action' => 'delete',
        ]);

        // Assert
        $this->assertCount(0, Tag::all());
    }

    public function test_editor_can_bulk_delete_tags(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $tags   = Tag::factory()->count(2)->create();

        // Act
        $this->actingAs($editor)->post('/admin/tags/bulk-action', [
            'ids'    => $tags->pluck('id')->toArray(),
            'action' => 'delete',
        ]);

        // Assert
        $this->assertCount(0, Tag::all());
    }

    // ─── authorisation / validation ──────────────────────────────────────────

    public function test_viewer_cannot_perform_bulk_actions(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $tag    = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($viewer)->post('/admin/tags/bulk-action', [
            'ids'    => [$tag->id],
            'action' => 'delete',
        ])->assertForbidden();
    }

    public function test_guest_cannot_perform_bulk_actions(): void
    {
        // Act + Assert
        $this->post('/admin/tags/bulk-action', [
            'ids'    => [1],
            'action' => 'delete',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_invalid_action_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag  = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($user)->post('/admin/tags/bulk-action', [
            'ids'    => [$tag->id],
            'action' => 'restore',
        ])->assertSessionHasErrors('action');
    }

    public function test_empty_ids_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)->post('/admin/tags/bulk-action', [
            'ids'    => [],
            'action' => 'delete',
        ])->assertSessionHasErrors('ids');
    }
}
