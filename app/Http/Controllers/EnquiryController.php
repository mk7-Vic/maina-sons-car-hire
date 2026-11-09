<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        Enquiry::create($request->validated() + ['status' => 'new']);

        return redirect()->route('contact')
            ->with('success', 'Thank you. Your enquiry has been submitted successfully.');
    }
}
