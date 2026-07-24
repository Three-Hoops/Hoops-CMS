<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── publish ─────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_publish_posts(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id, 'status' => ContentStatus::Draft]);

        // Act
        $response = $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => $posts->pluck('id')->toArray(),
            'action' => 'publish',
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertCount(3, Post::where('status', ContentStatus::Published)->get());
    }

    public function test_editor_can_bulk_publish_own_posts(): void
    {
        // Arrange
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $other  = User::factory()->create(['role' => UserRole::Editor]);
        $own    = Post::factory()->create(['user_id' => $editor->id, 'status' => ContentStatus::Draft]);
        $foreign = Post::factory()->create(['user_id' => $other->id, 'status' => ContentStatus::Draft]);

        // Act
        $this->actingAs($editor)->post('/admin/posts/bulk-action', [
            'ids'    => [$own->id, $foreign->id],
            'action' => 'publish',
        ]);

        // Assert — own post published, foreign post untouched
        $this->assertEquals(ContentStatus::Published, $own->fresh()->status);
        $this->assertEquals(ContentStatus::Draft, $foreign->fresh()->status);
    }

    public function test_bulk_publish_sets_published_at_when_null(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['user_id' => $user->id, 'status' => ContentStatus::Draft, 'published_at' => null]);

        // Act
        $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => [$post->id],
            'action' => 'publish',
        ]);

        // Assert
        $this->assertNotNull($post->fresh()->published_at);
    }

    // ─── draft ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_set_posts_to_draft(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $posts = Post::factory()->count(2)->create(['user_id' => $user->id, 'status' => ContentStatus::Published]);

        // Act
        $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => $posts->pluck('id')->toArray(),
            'action' => 'draft',
        ]);

        // Assert
        $this->assertCount(2, Post::where('status', ContentStatus::Draft)->get());
    }

    // ─── delete ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_delete_posts(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => $posts->pluck('id')->toArray(),
            'action' => 'delete',
        ]);

        // Assert — soft deleted
        $this->assertCount(0, Post::all());
        $this->assertCount(3, Post::onlyTrashed()->get());
    }

    public function test_editor_cannot_bulk_delete_other_users_posts(): void
    {
        // Arrange
        $editor  = User::factory()->create(['role' => UserRole::Editor]);
        $other   = User::factory()->create(['role' => UserRole::Editor]);
        $foreign = Post::factory()->create(['user_id' => $other->id]);

        // Act
        $this->actingAs($editor)->post('/admin/posts/bulk-action', [
            'ids'    => [$foreign->id],
            'action' => 'delete',
        ]);

        // Assert — foreign post still exists
        $this->assertNotNull(Post::find($foreign->id));
    }

    // ─── restore ─────────────────────────────────────────────────────────────

    public function test_super_admin_can_bulk_restore_posts(): void
    {
        // Arrange
        $user  = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $posts = Post::factory()->count(2)->create(['user_id' => $user->id]);
        $posts->each->delete();

        // Act
        $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => $posts->pluck('id')->toArray(),
            'action' => 'restore',
        ]);

        // Assert
        $this->assertCount(2, Post::all());
    }

    // ─── authorisation / validation ──────────────────────────────────────────

    public function test_viewer_cannot_perform_bulk_actions(): void
    {
        // Arrange
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $post   = Post::factory()->create(['user_id' => $viewer->id]);

        // Act + Assert
        $this->actingAs($viewer)->post('/admin/posts/bulk-action', [
            'ids'    => [$post->id],
            'action' => 'delete',
        ])->assertForbidden();
    }

    public function test_guest_cannot_perform_bulk_actions(): void
    {
        // Act + Assert
        $this->post('/admin/posts/bulk-action', [
            'ids'    => [1],
            'action' => 'delete',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_invalid_action_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act + Assert
        $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => [$post->id],
            'action' => 'nuke',
        ])->assertSessionHasErrors('action');
    }

    public function test_empty_ids_returns_validation_error(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)->post('/admin/posts/bulk-action', [
            'ids'    => [],
            'action' => 'delete',
        ])->assertSessionHasErrors('ids');
    }
}
