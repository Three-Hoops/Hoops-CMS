<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FlashType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTag;
use App\Http\Requests\Admin\UpdateTag;
use App\Models\Tag;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Tag::class);

        $tags = Tag::orderBy('name')->paginate(15);

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function store(StoreTag $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = UniqueSlug::generate($validated['slug'] ?? $validated['name'], 'tags');

        Tag::create($validated);

        return redirect()
            ->route('admin.tags.index')
            ->with(FlashType::Success->value, 'Tag created successfully.');
    }

    public function update(UpdateTag $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = UniqueSlug::generate($validated['slug'] ?? $validated['name'], 'tags', $tag->id);

        $tag->update($validated);

        return redirect()
            ->route('admin.tags.index')
            ->with(FlashType::Success->value, 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with(FlashType::Success->value, 'Tag deleted successfully.');
    }
}
