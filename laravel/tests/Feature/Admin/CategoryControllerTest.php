<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_all_roles_can_view_categories_index(): void
    {
        // Arrange

        foreach ([UserRole::SuperAdmin, UserRole::Editor, UserRole::Viewer] as $role) {
            $user = User::factory()->create(['role' => $role]);

            // Act + Assert
            $this->actingAs($user)->get('/admin/categories')->assertOk();
        }
    }

    public function test_guest_is_redirected_from_categories_index(): void
    {
        // Act + Assert
        $this->get('/admin/categories')->assertRedirect(route('admin.login'));
    }

    // ─── create ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_create_category_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->get('/admin/categories/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Categories/Create'));
    }

    public function test_editor_can_view_create_category_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/categories/create')->assertOk();
    }

    public function test_viewer_cannot_view_create_category_form(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)->get('/admin/categories/create')->assertForbidden();
    }

    public function test_guest_is_redirected_from_create_category_form(): void
    {
        $this->get('/admin/categories/create')->assertRedirect(route('admin.login'));
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function test_super_admin_can_create_category(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $response = $this->actingAs($user)->post('/admin/categories', [
            'name' => 'Technology',
        ]);

        // Assert
        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Technology', 'slug' => 'technology']);
    }

    public function test_editor_can_create_category(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Editor]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/categories', ['name' => 'Science'])
            ->assertRedirect('/admin/categories');
    }

    public function test_viewer_cannot_create_category(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/categories', ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_store_generates_slug_from_name(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act
        $this->actingAs($user)->post('/admin/categories', ['name' => 'Web Development']);

        // Assert
        $this->assertDatabaseHas('categories', ['slug' => 'web-development']);
    }

    public function test_store_accepts_valid_parent_id(): void
    {
        // Arrange
        $user   = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $parent = Category::factory()->create();

        // Act
        $this->actingAs($user)->post('/admin/categories', [
            'name'      => 'Child Category',
            'parent_id' => $parent->id,
        ]);

        // Assert
        $this->assertDatabaseHas('categories', ['name' => 'Child Category', 'parent_id' => $parent->id]);
    }

    public function test_store_rejects_invalid_parent_id(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/categories', ['name' => 'Child', 'parent_id' => 99999])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        Category::factory()->create(['slug' => 'existing']);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/categories', ['name' => 'Whatever', 'slug' => 'existing'])
            ->assertSessionHasErrors('slug');
    }

    public function test_store_requires_name(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // Act + Assert
        $this->actingAs($user)
            ->post('/admin/categories', [])
            ->assertSessionHasErrors('name');
    }

    // ─── edit ─────────────────────────────────────────────────────────────────

    public function test_super_admin_can_view_edit_category_form(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->get("/admin/categories/{$category->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Categories/Edit')
                ->has('category')
                ->has('parents')
            );
    }

    public function test_editor_can_view_edit_category_form(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Editor]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)->get("/admin/categories/{$category->id}/edit")->assertOk();
    }

    public function test_viewer_cannot_view_edit_category_form(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Viewer]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)->get("/admin/categories/{$category->id}/edit")->assertForbidden();
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_update_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create(['name' => 'Old Name']);

        // Act
        $this->actingAs($user)->put("/admin/categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        // Assert
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_editor_can_update_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Editor]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/categories/{$category->id}", ['name' => 'Updated'])
            ->assertRedirect('/admin/categories');
    }

    public function test_viewer_cannot_update_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Viewer]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/categories/{$category->id}", ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_update_ignores_own_slug_in_uniqueness_check(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create(['slug' => 'my-category']);

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/categories/{$category->id}", [
                'name' => 'My Category',
                'slug' => 'my-category',
            ])
            ->assertRedirect('/admin/categories');
    }

    public function test_update_rejects_self_as_parent(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->put("/admin/categories/{$category->id}", [
                'name'      => 'Self Parent',
                'parent_id' => $category->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_delete_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();

        // Act
        $this->actingAs($user)->delete("/admin/categories/{$category->id}");

        // Assert
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_editor_can_delete_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Editor]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_viewer_cannot_delete_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Viewer]);
        $category = Category::factory()->create();

        // Act + Assert
        $this->actingAs($user)->delete("/admin/categories/{$category->id}")->assertForbidden();
    }

    // ─── restore ──────────────────────────────────────────────────────────────

    public function test_super_admin_can_restore_soft_deleted_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();
        $category->delete();

        // Act
        $this->actingAs($user)->post("/admin/categories/{$category->id}/restore");

        // Assert
        $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_editor_can_restore_soft_deleted_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Editor]);
        $category = Category::factory()->create();
        $category->delete();

        // Act + Assert
        $this->actingAs($user)
            ->post("/admin/categories/{$category->id}/restore")
            ->assertRedirect();

        $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_viewer_cannot_restore_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Viewer]);
        $category = Category::factory()->create();
        $category->delete();

        // Act + Assert
        $this->actingAs($user)
            ->post("/admin/categories/{$category->id}/restore")
            ->assertForbidden();
    }

    // ─── forceDelete ──────────────────────────────────────────────────────────

    public function test_super_admin_can_force_delete_soft_deleted_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = Category::factory()->create();
        $category->delete();

        // Act
        $this->actingAs($user)->delete("/admin/categories/{$category->id}/force-delete");

        // Assert
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_editor_cannot_force_delete_category(): void
    {
        // Arrange
        $user     = User::factory()->create(['role' => UserRole::Editor]);
        $category = Category::factory()->create();
        $category->delete();

        // Act + Assert
        $this->actingAs($user)
            ->delete("/admin/categories/{$category->id}/force-delete")
            ->assertForbidden();
    }
}
