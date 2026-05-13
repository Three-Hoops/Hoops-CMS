<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_all_roles_can_view_tags_index(): void
    {
        // Arrange

        foreach ([UserRole::SuperAdmin, UserRole::Editor, UserRole::Viewer] as $role) {
            $user = User::factory()->create(['role' => $role]);

            // Act + Assert
            $this->actingAs($user)->get('/admin/tags')->assertOk();
        }
    }

    public function test_guest_is_redirected_from_tags_index(): void
    {
        // Act + Assert
        $this->get('/admin/tags')->assertRedirect(route('admin.login'));
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_super_admin_can_create_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $response = $this->actingAs($user)->post('/admin/tags', ['name' => 'Laravel']);

        // Assert
        $response->assertRedirect('/admin/tags');
        $this->assertDatabaseHas('tags', ['name' => 'Laravel', 'slug' => 'laravel']);
    }

    public function test_editor_can_create_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/tags', ['name' => 'Vue'])
            ->assertRedirect('/admin/tags');
    }

    public function test_viewer_cannot_create_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/tags', ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_store_generates_slug_from_name(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/tags', ['name' => 'Open Source']);

        // Assert
        $this->assertDatabaseHas('tags', ['slug' => 'open-source']);
    }

    public function test_store_requires_name(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/tags', [])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Tag::factory()->create(['slug' => 'php']);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/tags', ['name' => 'Anything', 'slug' => 'php'])
            ->assertSessionHasErrors('slug');
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_update_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag  = Tag::factory()->create(['name' => 'Old']);

        // Act
        $this->actingAs($user)->put("/admin/tags/{$tag->id}", ['name' => 'New']);

        // Assert
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'New']);
    }

    public function test_editor_can_update_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $tag  = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", ['name' => 'Updated'])
            ->assertRedirect('/admin/tags');
    }

    public function test_viewer_cannot_update_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $tag  = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_update_requires_name(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag  = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [])
            ->assertSessionHasErrors('name');
    }

    public function test_update_ignores_own_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag  = Tag::factory()->create(['slug' => 'my-tag']);

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/tags/{$tag->id}", ['name' => 'My Tag', 'slug' => 'my-tag'])
            ->assertRedirect('/admin/tags');
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_delete_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $tag  = Tag::factory()->create();

        // Act
        $this->actingAs($user)->delete("/admin/tags/{$tag->id}");

        // Assert
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_editor_can_delete_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $tag  = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/tags/{$tag->id}")
            ->assertRedirect('/admin/tags');
    }

    public function test_viewer_cannot_delete_tag(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);
        $tag  = Tag::factory()->create();

        // Act + Assert
        $this->actingAs($user)->delete("/admin/tags/{$tag->id}")->assertForbidden();
    }
}
