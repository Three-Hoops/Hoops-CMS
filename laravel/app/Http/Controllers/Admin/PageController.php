<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\FlashType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePage;
use App\Http\Requests\Admin\UpdatePage;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Page::class);

        $pages = Page::with('author')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Page::class);

        return Inertia::render('Admin/Pages/Create');
    }

    public function store(StorePage $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug']         ??= Str::slug($validated['title']);
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
            'page' => $page->load('author'),
        ]);
    }

    public function update(UpdatePage $request, Page $page): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug']         ??= Str::slug($validated['title']);
        $validated['content_json'] ??= [];

        if (
            $validated['status'] === ContentStatus::Published->value
            && is_null($page->published_at)
            && empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        $page->update($validated);

        return redirect()
            ->route('admin.pages.index')
            ->with(FlashType::Success->value, 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with(FlashType::Success->value, 'Page deleted successfully.');
    }
}
