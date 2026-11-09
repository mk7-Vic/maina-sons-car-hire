<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEnquiryRequest;
use App\Models\AuditLog;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = ['new', 'in_progress', 'resolved', 'closed'];

        $enquiries = Enquiry::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->status, $statuses, true),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.enquiries.index', compact('enquiries', 'statuses'));
    }

    public function show(Enquiry $enquiry): View
    {
        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function update(
        UpdateEnquiryRequest $request,
        Enquiry $enquiry
    ): RedirectResponse {
        DB::transaction(function () use ($request, $enquiry) {
            $enquiry->update($request->validated());

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'enquiry.updated',
                'auditable_type' => Enquiry::class,
                'auditable_id' => $enquiry->id,
                'description' => "Enquiry #{$enquiry->id} updated to {$enquiry->status}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.enquiries.show', $enquiry)
            ->with('success', 'Enquiry updated successfully.');
    }
}
