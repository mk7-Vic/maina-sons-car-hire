<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBranchRequest;
use App\Models\AuditLog;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::withCount([
            'pickupBookings',
            'returnBookings',
        ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->status, ['active', 'inactive'], true),
                fn ($query) => $query->where(
                    'is_active',
                    $request->status === 'active'
                )
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('admin.branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $branch = Branch::create($request->validated());

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'branch.created',
                'auditable_type' => Branch::class,
                'auditable_id' => $branch->id,
                'description' => "Branch {$branch->name} created.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch added successfully.');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(
        StoreBranchRequest $request,
        Branch $branch
    ): RedirectResponse {
        DB::transaction(function () use ($request, $branch) {
            $branch->update($request->validated());

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'branch.updated',
                'auditable_type' => Branch::class,
                'auditable_id' => $branch->id,
                'description' => "Branch {$branch->name} updated.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }
}
