<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryType;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Ordering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CategoryController extends AdminController
{
    /** @var array<string, string> */
    private const SORTS = [
        'order' => 'position',
        'name' => 'name',
        'updated' => 'updated_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'type' => $request->string('type')->toString(),
        ];

        $query = Category::query()
            ->withCount(['projects', 'insights'])
            ->search($filters['q'])
            ->ofType($filters['type'] !== '' ? $filters['type'] : null);

        [$sort, $direction] = $this->applySort(
            $query,
            self::SORTS,
            'order',
            $request->string('sort')->toString(),
            $request->string('direction')->toString() ?: 'asc',
        );

        return view('admin.categories.index', [
            'categories' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'types' => CategoryType::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Category::class);

        $type = CategoryType::tryFrom($request->string('type')->toString()) ?? CategoryType::Insight;

        return view('admin.categories.create', [
            'category' => new Category(['type' => $type, 'position' => 0]),
            'types' => CategoryType::cases(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $type = CategoryType::from((string) $request->validated('type'));

        $category = Category::query()->create([
            ...$request->validated(),
            'position' => Ordering::nextPosition(Category::query()->where('type', $type)),
        ]);

        // Back to the list filtered to the type just added to, which is
        // almost always where the next one belongs too.
        return $this->saved('admin.categories.index', "“{$category->name}” added.", ['type' => $type->value]);
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', [
            'category' => $category->loadCount(['projects', 'insights']),
            'types' => CategoryType::cases(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return $this->saved('admin.categories.index', "“{$category->name}” updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->loadCount(['projects', 'insights']);

        // The foreign keys null out rather than cascade, so deleting a used
        // category would quietly strip the label off live work instead of
        // failing. Refuse, and say what is still attached.
        if ($category->isInUse()) {
            return back()->withErrors([
                'category' => sprintf(
                    '“%s” is still used by %d project(s) and %d insight(s). Reassign them first.',
                    $category->name,
                    $category->projects_count,
                    $category->insights_count,
                ),
            ]);
        }

        $type = $category->type;
        $name = $category->name;
        $category->delete();

        Ordering::resequence(Category::query()->where('type', $type));

        return $this->saved('admin.categories.index', "“{$name}” deleted.");
    }

    public function move(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        $moved = Ordering::move($category, $direction, Category::query()->where('type', $category->type));

        return $this->savedBack($moved ? 'Order updated.' : 'Already at that end of the list.');
    }
}
