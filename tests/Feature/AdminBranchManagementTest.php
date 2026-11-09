<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminBranchManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function branchData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nairobi Branch',
            'location' => 'Westlands, Nairobi',
            'phone' => '0712345678',
            'email' => 'nairobi@mainasons.com',
            'is_active' => 1,
        ], $overrides);
    }

    public function test_guest_cannot_access_branch_management(): void
    {
        $this->get(route('admin.branches.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.branches.create'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_branch_management(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.branches.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_branches_and_create_form(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.branches.index'))
            ->assertOk()
            ->assertSee($branch->name);

        $this->get(route('admin.branches.create'))
            ->assertOk()
            ->assertSee('Add Branch');
    }

    public function test_admin_can_create_branch_and_audit_log_is_recorded(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(
                route('admin.branches.store'),
                $this->branchData()
            )
            ->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('email', 'nairobi@mainasons.com')
            ->firstOrFail();

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Nairobi Branch',
            'location' => 'Westlands, Nairobi',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'branch.created',
            'auditable_type' => Branch::class,
            'auditable_id' => $branch->id,
        ]);
    }

    public function test_branch_creation_requires_name_and_location(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.branches.store'), [
                'name' => '',
                'location' => '',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors(['name', 'location']);

        $this->assertDatabaseCount('branches', 0);
    }

    public function test_invalid_branch_email_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(
                route('admin.branches.store'),
                $this->branchData([
                    'email' => 'not-an-email',
                ])
            )
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('branches', 0);
    }

    public function test_admin_can_update_branch_and_audit_log_is_recorded(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();

        $this->actingAs($admin)
            ->put(
                route('admin.branches.update', $branch),
                $this->branchData([
                    'name' => 'Mombasa Branch',
                    'location' => 'Nyali, Mombasa',
                    'is_active' => 0,
                ])
            )
            ->assertRedirect(route('admin.branches.index'));

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Mombasa Branch',
            'location' => 'Nyali, Mombasa',
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'branch.updated',
            'auditable_type' => Branch::class,
            'auditable_id' => $branch->id,
        ]);
    }

    public function test_admin_can_search_branches_by_name(): void
    {
        $admin = $this->admin();

        $matching = Branch::factory()->create([
            'name' => 'Nairobi Main Branch',
        ]);

        $other = Branch::factory()->create([
            'name' => 'Kisumu Branch',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.branches.index', [
                'search' => 'Nairobi Main',
            ]))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee($other->name);
    }

    public function test_admin_can_filter_branches_by_status(): void
    {
        $admin = $this->admin();

        $active = Branch::factory()->create([
            'name' => 'Active Nairobi Branch',
            'is_active' => true,
        ]);

        $inactive = Branch::factory()->create([
            'name' => 'Inactive Mombasa Branch',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.branches.index', [
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    public function test_branches_have_no_delete_route(): void
    {
        $this->assertFalse(
            collect(Route::getRoutes())
                ->contains(function ($route) {
                    return $route->getName() === 'admin.branches.destroy';
                })
        );
    }
}
