<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'exists:vehicle_categories,slug'],
        ]);

        $vehicles = Vehicle::with('category')
            ->where('status', 'available')
            ->when(
                $validated['category'] ?? null,
                fn ($query, $slug) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $slug)
                )
            )
            ->orderBy('daily_rate')
            ->get();

        $categories = VehicleCategory::orderBy('name')->get();

        return view('vehicles.index', compact('vehicles', 'categories'));
    }

    public function show(Vehicle $vehicle): View
    {
        abort_unless($vehicle->status === 'available', 404);

        $vehicle->load('category');

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('vehicles.show', compact('vehicle', 'branches'));
    }
}
