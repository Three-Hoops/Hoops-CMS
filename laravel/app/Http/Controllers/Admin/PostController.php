<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\FlashType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePost;
use App\Http\Requests\Admin\UpdatePost;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Post::class);

        $posts = Post::with(['author', 'category', 'tags'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Post::class);

        return Inertia::render('Admin/Posts/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tags'       => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePost $request): RedirectResponse
    {
        $validated = $request->validated();
        $tagIds    = Arr::pull($validated, 'tag_ids', []);

        $validated['slug']         ??= Str::slug($validated['title']);
        $validated['content_json'] ??= [];
        $validated['user_id']        = $request->user()->id;

        if ($validated['status'] === ContentStatus::Published->value && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);
        $post->tags()->sync($tagIds);

        return redirect()
            ->route('admin.posts.index')
            ->with(FlashType::Success->value, 'Post created successfully.');
    }

    public function edit(Post $post): Response
    {
        $this->authorize('update', $post);

        return Inertia::render('Admin/Posts/Edit', [
            'post'       => $post->load(['author', 'category', 'tags']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tags'       => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePost $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();
        $tagIds    = Arr::pull($validated, 'tag_ids', []);

        $validated['slug']         ??= Str::slug($validated['title']);
        $validated['content_json'] ??= [];

        if (
            $validated['status'] === ContentStatus::Published->value
            && is_null($post->published_at)
            && empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        $post->update($validated);
        $post->tags()->sync($tagIds);

        return redirect()
            ->route('admin.posts.index')
            ->with(FlashType::Success->value, 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with(FlashType::Success->value, 'Post deleted successfully.');
    }
}
