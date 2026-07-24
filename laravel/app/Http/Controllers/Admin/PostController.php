<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\FlashType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Post::class);

        $trash = request()->boolean('trash');
        $user  = request()->user();

        $posts = Post::with(['author', 'category', 'tags'])
            ->when($trash, fn ($q) => $q->onlyTrashed())
            ->when($user->role === UserRole::Viewer, fn ($q) => $q->where('status', ContentStatus::Published))
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

    public function duplicate(Post $post): RedirectResponse
    {
        $this->authorize('duplicate', $post);

        $copy = DB::transaction(function () use ($post) {
            $new = Post::create([
                'title'            => $post->title . ' (Copy)',
                'slug'             => UniqueSlug::generate($post->title . ' Copy', 'posts'),
                'content'          => $post->content,
                'content_json'     => $post->content_json ?? [],
                'excerpt'          => $post->excerpt,
                'status'           => ContentStatus::Draft,
                'meta_title'       => $post->meta_title,
                'meta_description' => $post->meta_description,
                'meta_keywords'    => $post->meta_keywords,
                'featured_image'   => $post->featured_image,
                'category_id'      => $post->category_id,
                'user_id'          => auth()->id(),
                'published_at'     => null,
            ]);

            $new->tags()->sync($post->tags->pluck('id'));

            return $new;
        });

        return redirect()
            ->route('admin.posts.edit', $copy)
            ->with(FlashType::Success->value, 'Post duplicated successfully.');
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

    public function bulkAction(BulkActionRequest $request): RedirectResponse
    {
        $request->validate(['action' => ['required', Rule::in(['publish', 'draft', 'delete', 'restore'])]]);

        /** @var 'publish'|'draft'|'delete'|'restore' $action */
        $action = (string) $request->input('action');
        $ids    = $request->input('ids');

        $query = $action === 'restore'
            ? Post::withTrashed()->whereIn('id', $ids)
            : Post::whereIn('id', $ids);

        $policyMethod = match ($action) {
            'publish', 'draft' => 'update',
            'delete'           => 'delete',
            'restore'          => 'restore',
        };

        $posts = $query->get()->filter(fn ($post) => Gate::allows($policyMethod, $post));
        $count = $posts->count();

        match ($action) {
            'publish' => $posts->each(fn ($p) => $p->update([
                'status'       => ContentStatus::Published,
                'published_at' => $p->published_at ?? now(),
            ])),
            'draft'   => $posts->each(fn ($p) => $p->update(['status' => ContentStatus::Draft])),
            'delete'  => $posts->each(fn ($p) => $p->delete()),
            'restore' => $posts->each(fn ($p) => $p->restore()),
        };

        $label = match ($action) {
            'publish' => "Published {$count} post(s).",
            'draft'   => "Set {$count} post(s) to draft.",
            'delete'  => "Moved {$count} post(s) to trash.",
            'restore' => "Restored {$count} post(s).",
        };

        return redirect()->back()->with(FlashType::Success->value, $label);
    }
}
