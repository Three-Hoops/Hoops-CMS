<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\FlashType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\StorePage;
use App\Http\Requests\Admin\UpdatePage;
use App\Models\Page;
use App\Support\UniqueSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Page::class);

        $trash = request()->boolean('trash');
        $user  = request()->user();

        $pages = Page::with(['author', 'parent'])
            ->when($trash, fn ($q) => $q->onlyTrashed())
            ->when($user->role === UserRole::Viewer, fn ($q) => $q->where('status', ContentStatus::Published))
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'trash' => $trash,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Page::class);

        return Inertia::render('Admin/Pages/Create', [
            'pages' => Page::select('id', 'title', 'slug')->orderBy('title')->get(),
        ]);
    }

    public function store(StorePage $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug']         = UniqueSlug::generate($validated['slug'] ?? $validated['title'], 'pages');
        $validated['content_json'] ??= [];
        $validated['user_id']        = $request->user()->id;

        if ($validated['status'] === ContentStatus::Published->value && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        Page::create($validated);

        return redirect()
            ->route('admin.pages.index')
            ->with(FlashType::Success->value, 'Page created successfully.');
    }

    public function edit(Page $page): Response
    {
        $this->authorize('update', $page);

        return Inertia::render('Admin/Pages/Edit', [
            'page'          => $page->load(['author', 'parent']),
            'pages'         => Page::select('id', 'title', 'slug')->where('id', '!=', $page->id)->orderBy('title')->get(),
            'autosaveDraft' => data_get($page->autosave_json, 'content'),
        ]);
    }

    public function update(UpdatePage $request, Page $page): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug']         = UniqueSlug::generate($validated['slug'] ?? $validated['title'], 'pages', $page->id);
        $validated['content_json'] ??= [];

        if (
            $validated['status'] === ContentStatus::Published->value
            && is_null($page->published_at)
            && empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        $page->update($validated);
        Page::withoutTimestamps(fn () => $page->forceFill(['autosave_json' => null])->save());

        return redirect()
            ->route('admin.pages.index')
            ->with(FlashType::Success->value, 'Page updated successfully.');
    }

    public function autosave(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $request->validate(['content' => ['required', 'string']]);

        Page::withoutTimestamps(
            fn () => $page->forceFill(['autosave_json' => ['content' => $request->input('content')]])->save()
        );

        return response()->json(['saved_at' => now()->toISOString()]);
    }

    public function duplicate(Page $page): RedirectResponse
    {
        $this->authorize('duplicate', $page);

        $new = Page::create([
            'title'            => $page->title . ' (Copy)',
            'slug'             => UniqueSlug::generate($page->title . ' Copy', 'pages'),
            'content'          => $page->content,
            'content_json'     => $page->content_json ?? [],
            'excerpt'          => $page->excerpt,
            'status'           => ContentStatus::Draft,
            'meta_title'       => $page->meta_title,
            'meta_description' => $page->meta_description,
            'meta_keywords'    => $page->meta_keywords,
            'parent_id'        => $page->parent_id,
            'user_id'          => auth()->id(),
            'published_at'     => null,
        ]);

        return redirect()
            ->route('admin.pages.edit', $new)
            ->with(FlashType::Success->value, 'Page duplicated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with(FlashType::Success->value, 'Page deleted successfully.');
    }

    public function restore(Page $page): RedirectResponse
    {
        $this->authorize('restore', $page);

        $page->restore();

        return redirect()
            ->route('admin.pages.index', ['trash' => 1])
            ->with(FlashType::Success->value, 'Page restored.');
    }

    public function forceDelete(Page $page): RedirectResponse
    {
        $this->authorize('forceDelete', $page);

        $page->forceDelete();

        return redirect()
            ->route('admin.pages.index', ['trash' => 1])
            ->with(FlashType::Success->value, 'Page permanently deleted.');
    }

    public function bulkAction(BulkActionRequest $request): RedirectResponse
    {
        $request->validate(['action' => ['required', Rule::in(['publish', 'draft', 'delete', 'restore'])]]);

        $action = $request->input('action');
        $ids    = $request->input('ids');

        $query = $action === 'restore'
            ? Page::withTrashed()->whereIn('id', $ids)
            : Page::whereIn('id', $ids);

        $policyMethod = match ($action) {
            'publish', 'draft' => 'update',
            'delete'           => 'delete',
            'restore'          => 'restore',
        };

        $pages = $query->get()->filter(fn ($page) => Gate::allows($policyMethod, $page));
        $count = $pages->count();

        match ($action) {
            'publish' => $pages->each(fn ($p) => $p->update([
                'status'       => ContentStatus::Published,
                'published_at' => $p->published_at ?? now(),
            ])),
            'draft'   => $pages->each(fn ($p) => $p->update(['status' => ContentStatus::Draft])),
            'delete'  => $pages->each(fn ($p) => $p->delete()),
            'restore' => $pages->each(fn ($p) => $p->restore()),
        };

        $label = match ($action) {
            'publish' => "Published {$count} page(s).",
            'draft'   => "Set {$count} page(s) to draft.",
            'delete'  => "Moved {$count} page(s) to trash.",
            'restore' => "Restored {$count} page(s).",
        };

        return redirect()->back()->with(FlashType::Success->value, $label);
    }
}
