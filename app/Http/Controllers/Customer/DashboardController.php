<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $bookings = $request->user()
            ->bookings()
            ->with(['vehicle', 'pickupBranch', 'returnBranch'])
            ->latest()
            ->paginate(10);

        return view('customer.dashboard', compact('bookings'));
    }
}
