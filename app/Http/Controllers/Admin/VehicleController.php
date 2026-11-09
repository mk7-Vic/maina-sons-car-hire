<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVehicleRequest;
use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('make')
            ->paginate(10)
            ->withQueryString();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        $categories = VehicleCategory::orderBy('name')->get();

        return view('admin.vehicles.create', compact('categories'));
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $vehicle = Vehicle::create($request->validated());

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'vehicle.created',
                'auditable_type' => Vehicle::class,
                'auditable_id' => $vehicle->id,
                'description' => "Vehicle {$vehicle->registration_number} created.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle added successfully.');
    }

    public function edit(Vehicle $vehicle): View
    {
        $categories = VehicleCategory::orderBy('name')->get();

        return view('admin.vehicles.edit', compact('vehicle', 'categories'));
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        DB::transaction(function () use ($request, $vehicle) {
            $vehicle->update($request->validated());

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'vehicle.updated',
                'auditable_type' => Vehicle::class,
                'auditable_id' => $vehicle->id,
                'description' => "Vehicle {$vehicle->registration_number} updated.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->bookings()->exists()) {
            return back()->with('error', 'Vehicles with booking history cannot be deleted. Set their status to unavailable instead.');
        }

        DB::transaction(function () use ($request, $vehicle) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'vehicle.deleted',
                'auditable_type' => Vehicle::class,
                'auditable_id' => $vehicle->id,
                'description' => "Vehicle {$vehicle->registration_number} deleted.",
                'ip_address' => $request->ip(),
            ]);

            $vehicle->delete();
        });

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }
}
