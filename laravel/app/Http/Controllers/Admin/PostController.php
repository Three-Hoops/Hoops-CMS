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
use App\Support\UniqueSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Post::class);

        $trash = request()->boolean('trash');

        $posts = Post::with(['author', 'category', 'tags'])
            ->when($trash, fn ($q) => $q->onlyTrashed())
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'trash' => $trash,
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

        $validated['slug']         = UniqueSlug::generate($validated['slug'] ?? $validated['title'], 'posts');
        $validated['content_json'] ??= [];
        $validated['user_id']        = $request->user()->id;

        if ($validated['status'] === ContentStatus::Published->value && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = DB::transaction(function () use ($validated, $tagIds) {
            $post = Post::create($validated);
            $post->tags()->sync($tagIds);

            return $post;
        });

        return redirect()
            ->route('admin.posts.index')
            ->with(FlashType::Success->value, 'Post created successfully.');
    }

    public function edit(Post $post): Response
    {
        $this->authorize('update', $post);

        return Inertia::render('Admin/Posts/Edit', [
            'post'          => $post->load(['author', 'category', 'tags']),
            'categories'    => Category::orderBy('name')->get(['id', 'name']),
            'tags'          => Tag::orderBy('name')->get(['id', 'name']),
            'autosaveDraft' => data_get($post->autosave_json, 'content'),
        ]);
    }

    public function update(UpdatePost $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();
        $tagIds    = Arr::pull($validated, 'tag_ids', []);

        $validated['slug']         = UniqueSlug::generate($validated['slug'] ?? $validated['title'], 'posts', $post->id);
        $validated['content_json'] ??= [];

        if (
            $validated['status'] === ContentStatus::Published->value
            && is_null($post->published_at)
            && empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        DB::transaction(function () use ($post, $validated, $tagIds) {
            $post->update($validated);
            $post->tags()->sync($tagIds);
            Post::withoutTimestamps(fn () => $post->forceFill(['autosave_json' => null])->save());
        });

        return redirect()
            ->route('admin.posts.index')
            ->with(FlashType::Success->value, 'Post updated successfully.');
    }

    public function autosave(Request $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $request->validate(['content' => ['required', 'string']]);

        Post::withoutTimestamps(
            fn () => $post->forceFill(['autosave_json' => ['content' => $request->input('content')]])->save()
        );

        return response()->json(['saved_at' => now()->toISOString()]);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with(FlashType::Success->value, 'Post deleted successfully.');
    }

    public function restore(Post $post): RedirectResponse
    {
        $this->authorize('restore', $post);

        $post->restore();

        return redirect()
            ->route('admin.posts.index', ['trash' => 1])
            ->with(FlashType::Success->value, 'Post restored.');
    }

    public function forceDelete(Post $post): RedirectResponse
    {
        $this->authorize('forceDelete', $post);

        $post->forceDelete();

        return redirect()
            ->route('admin.posts.index', ['trash' => 1])
            ->with(FlashType::Success->value, 'Post permanently deleted.');
    }
}
