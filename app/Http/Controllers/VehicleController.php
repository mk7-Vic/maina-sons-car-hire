<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('category')
            ->where('status', 'available')
            ->orderBy('daily_rate')
            ->get();

        return view('vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle)
    {
        abort_unless($vehicle->status === 'available', 404);

        $vehicle->load('category');

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('vehicles.show', compact('vehicle', 'branches'));
    }
}
