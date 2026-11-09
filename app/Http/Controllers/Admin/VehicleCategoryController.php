<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleCategoryController extends Controller
{
    public function index(): View
    {
        $categories = VehicleCategory::withCount('vehicles')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new VehicleCategory,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vehicle_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = VehicleCategory::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name']),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'vehicle_category.created',
            'auditable_type' => VehicleCategory::class,
            'auditable_id' => $category->id,
            'description' => "Created vehicle category: {$category->name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Vehicle category created successfully.');
    }

    public function edit(VehicleCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, VehicleCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $category->update([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], $category->id),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'vehicle_category.updated',
            'auditable_type' => VehicleCategory::class,
            'auditable_id' => $category->id,
            'description' => "Updated vehicle category: {$category->name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Vehicle category updated successfully.');
    }

    public function destroy(Request $request, VehicleCategory $category): RedirectResponse
    {
        if ($category->vehicles()->exists()) {
            return back()->with('error', 'This category cannot be deleted while vehicles are assigned to it.');
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'vehicle_category.deleted',
            'auditable_type' => VehicleCategory::class,
            'auditable_id' => $category->id,
            'description' => "Deleted vehicle category: {$category->name}",
            'ip_address' => $request->ip(),
        ]);

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Vehicle category deleted successfully.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            VehicleCategory::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
