<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_only_admin_can_view_audit_logs(): void
    {
        $this->get(route('admin.audit-logs.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = $this->admin();

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'vehicle.created',
            'description' => 'Created a test vehicle.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Audit Logs')
            ->assertSee('vehicle.created')
            ->assertSee('Created a test vehicle.');
    }

    public function test_admin_can_filter_audit_logs_by_action_and_user(): void
    {
        $admin = $this->admin();
        $otherUser = User::factory()->create(['role' => 'admin']);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'vehicle.created',
            'description' => 'Matching record.',
        ]);

        AuditLog::create([
            'user_id' => $otherUser->id,
            'action' => 'branch.created',
            'description' => 'Other record.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', [
                'action' => 'vehicle.created',
                'user_id' => $admin->id,
            ]))
            ->assertOk()
            ->assertSee('Matching record.')
            ->assertDontSee('Other record.');
    }

    public function test_admin_can_search_audit_log_descriptions(): void
    {
        $admin = $this->admin();

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'payment.created',
            'description' => 'Unique payment audit phrase.',
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'vehicle.created',
            'description' => 'Unrelated audit phrase.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', ['search' => 'Unique payment']))
            ->assertOk()
            ->assertSee('Unique payment audit phrase.')
            ->assertDontSee('Unrelated audit phrase.');
    }
}
