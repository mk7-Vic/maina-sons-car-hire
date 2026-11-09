<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;

class HomeController extends Controller
{
    public function index()
    {
        $featuredVehicles = Vehicle::with('category')
            ->where('status', 'available')
            ->orderBy('daily_rate')
            ->limit(4)
            ->get();

        return view('welcome', compact('featuredVehicles'));
    }
}
