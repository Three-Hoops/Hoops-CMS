<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FlashType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\StoreCategory;
use App\Http\Requests\Admin\UpdateCategory;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Category::class);

        $trash = request()->boolean('trash');

        $categories = Category::with('parent')
            ->when($trash, fn ($q) => $q->onlyTrashed())
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'trash'      => $trash,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Admin/Categories/Create', [
            'parents' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCategory $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = UniqueSlug::generate($validated['slug'] ?? $validated['name'], 'categories');

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with(FlashType::Success->value, 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category->load('parent'),
            'parents'  => Category::where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCategory $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = UniqueSlug::generate($validated['slug'] ?? $validated['name'], 'categories', $category->id);

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with(FlashType::Success->value, 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with(FlashType::Success->value, 'Category deleted successfully.');
    }

    public function restore(Category $category): RedirectResponse
    {
        $this->authorize('restore', $category);

        $category->restore();

        return redirect()
            ->route('admin.categories.index', ['trash' => 1])
            ->with(FlashType::Success->value, 'Category restored.');
    }

    public function forceDelete(Category $category): RedirectResponse
    {
        $this->authorize('forceDelete', $category);

        $category->forceDelete();

        return redirect()
            ->route('admin.categories.index', ['trash' => 1])
            ->with(FlashType::Success->value, 'Category permanently deleted.');
    }

    public function bulkAction(BulkActionRequest $request): RedirectResponse
    {
        $request->validate(['action' => ['required', Rule::in(['delete', 'restore'])]]);

        /** @var 'delete'|'restore' $action */
        $action     = (string) $request->input('action');
        $ids        = $request->input('ids');
        $policy     = $action === 'restore' ? 'restore' : 'delete';

        $categories = $action === 'restore'
            ? Category::withTrashed()->whereIn('id', $ids)->get()
            : Category::whereIn('id', $ids)->get();

        $categories = $categories->filter(fn ($c) => Gate::allows($policy, $c));
        $count      = $categories->count();

        match ($action) {
            'delete'  => $categories->each(fn ($c) => $c->delete()),
            'restore' => $categories->each(fn ($c) => $c->restore()),
        };

        $label = match ($action) {
            'delete'  => "Moved {$count} category(s) to trash.",
            'restore' => "Restored {$count} category(s).",
        };

        return redirect()->back()->with(FlashType::Success->value, $label);
    }
}
