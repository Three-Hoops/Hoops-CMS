<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'posts' => [
                    'total'     => Post::count(),
                    'published' => Post::where('status', ContentStatus::Published)->count(),
                    'draft'     => Post::where('status', ContentStatus::Draft)->count(),
                ],
                'pages' => [
                    'total'     => Page::count(),
                    'published' => Page::where('status', ContentStatus::Published)->count(),
                    'draft'     => Page::where('status', ContentStatus::Draft)->count(),
                ],
                'categories'   => Category::count(),
                'tags'         => Tag::count(),
                'recent_posts' => Post::with('author')
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'title', 'status', 'published_at', 'created_at', 'user_id']),
            ],
        ]);
    }
}
