<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    }

    public function test_dashboard_returns_post_counts(): void
    {
        // Arrange
        Post::factory()->count(3)->create(['status' => ContentStatus::Published, 'user_id' => $this->admin->id]);
        Post::factory()->count(2)->create(['status' => ContentStatus::Draft, 'user_id' => $this->admin->id]);

        // Act + Assert
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->where('stats.posts.total', 5)
                ->where('stats.posts.published', 3)
                ->where('stats.posts.draft', 2)
            );
    }

    public function test_dashboard_returns_page_counts(): void
    {
        // Arrange
        Page::factory()->count(2)->create(['status' => ContentStatus::Published, 'user_id' => $this->admin->id]);
        Page::factory()->count(1)->create(['status' => ContentStatus::Draft, 'user_id' => $this->admin->id]);

        // Act + Assert
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->where('stats.pages.total', 3)
                ->where('stats.pages.published', 2)
                ->where('stats.pages.draft', 1)
            );
    }

    public function test_dashboard_returns_category_and_tag_counts(): void
    {
        // Arrange
        Category::factory()->count(4)->create();
        Tag::factory()->count(6)->create();

        // Act + Assert
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->where('stats.categories', 4)
                ->where('stats.tags', 6)
            );
    }

    public function test_dashboard_returns_five_recent_posts(): void
    {
        // Arrange
        Post::factory()->count(7)->create(['user_id' => $this->admin->id]);

        // Act + Assert
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->has('stats.recent_posts', 5)
            );
    }

    public function test_dashboard_stats_do_not_count_soft_deleted_posts(): void
    {
        // Arrange
        $post = Post::factory()->create(['user_id' => $this->admin->id]);
        $post->delete();

        // Act + Assert
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->where('stats.posts.total', 0)
            );
    }

    public function test_dashboard_stats_do_not_count_soft_deleted_pages(): void
    {
        // Arrange
        $page = Page::factory()->create(['user_id' => $this->admin->id]);
        $page->delete();

        // Act + Assert
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertInertia(fn ($page) => $page
                ->where('stats.pages.total', 0)
            );
    }
}
