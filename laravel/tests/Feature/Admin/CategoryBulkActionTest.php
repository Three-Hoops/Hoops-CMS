<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── delete ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_delete_categories(): void
    {
        // Arrange
        $user       = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $categories = Category::factory()->count(3)->create();

        // Act
        $this->actingAs($user)->post('/admin/categories/bulk-action', [
            'ids'    => $categories->pluck('id')->toArray(),
            'action' => 'delete',
        ]);

        // Assert — soft deleted
        $this->assertCount(0, Category::all());
        $this->assertCount(3, Category::onlyTrashed()->get());
    }

    public function test_editor_can_bulk_delete_categories(): void
    {
        // Arrange
        $editor     = User::factory()->create(['role' => UserRole::Editor]);
        $categories = Category::factory()->count(2)->create();

        // Act
        $this->actingAs($editor)->post('/admin/categories/bulk-action', [
            'ids'    => $categories->pluck('id')->toArray(),
            'action' => 'delete',
        ]);

        // Assert
        $this->assertCount(0, Category::all());
    }

    // ─── restore ─────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_restore_categories(): void
    {
        // Arrange
        $user       = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $categories = Category::factory()->count(2)->create();
        $categories->each->delete();

        // Act
        $this->actingAs($user)->post('/admin/categories/bulk-action', [
            'ids'    => $categories->pluck('id')->toArray(),
            'action' => 'restore',
        ]);

        // Assert
        $this->assertCount(2, Category::all());
    }

    // ─── authorisation / validation ──────────────────────────────────────────

    public function test_viewer_cannot_perform_bulk_actions(): void
    {
        // Arrange
        $viewer   = User::factory()->create(['role' => UserRole::Viewer]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($viewer)->post('/admin/categories/bulk-action', [
            'ids'    => [$category->id],
            'action' => 'delete',
        ])->assertForbidden();
    }

    public function test_guest_cannot_perform_bulk_actions(): void
    {
        // Act + Assert
        $this->post('/admin/categories/bulk-action', [
            'ids'    => [1],
            'action' => 'delete',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_invalid_action_returns_validation_error(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)->post('/admin/categories/bulk-action', [
            'ids'    => [$category->id],
            'action' => 'publish',
        ])->assertSessionHasErrors('action');
    }

    public function test_empty_ids_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)->post('/admin/categories/bulk-action', [
            'ids'    => [],
            'action' => 'delete',
        ])->assertSessionHasErrors('ids');
    }
}
