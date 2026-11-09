<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEnquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function enquiry(array $overrides = []): Enquiry
    {
        return Enquiry::create(array_merge([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'subject' => 'Vehicle availability',
            'message' => 'I would like to know which vehicles are available.',
            'status' => 'new',
        ], $overrides));
    }

    public function test_guest_cannot_access_enquiry_management(): void
    {
        $this->get(route('admin.enquiries.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_enquiry_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('admin.enquiries.index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_and_view_enquiries(): void
    {
        $admin = $this->admin();
        $enquiry = $this->enquiry();

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSee($enquiry->subject);

        $this->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee($enquiry->message);
    }

    public function test_admin_can_search_enquiries(): void
    {
        $admin = $this->admin();
        $matching = $this->enquiry(['subject' => 'Nairobi vehicle question']);
        $other = $this->enquiry([
            'name' => 'Other Customer',
            'email' => 'other@example.com',
            'subject' => 'Mombasa branch question',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['search' => 'Nairobi']))
            ->assertOk()
            ->assertSee($matching->subject)
            ->assertDontSee($other->subject);
    }

    public function test_admin_can_filter_enquiries_by_status(): void
    {
        $admin = $this->admin();
        $new = $this->enquiry(['subject' => 'New customer enquiry']);
        $resolved = $this->enquiry([
            'subject' => 'Resolved customer enquiry',
            'status' => 'resolved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index', ['status' => 'new']))
            ->assertOk()
            ->assertSee($new->subject)
            ->assertDontSee($resolved->subject);
    }

    public function test_admin_can_update_status_and_notes_with_audit_log(): void
    {
        $admin = $this->admin();
        $enquiry = $this->enquiry();

        $this->actingAs($admin)
            ->patch(route('admin.enquiries.update', $enquiry), [
                'status' => 'in_progress',
                'admin_notes' => 'Contacted customer. Awaiting response.',
            ])
            ->assertRedirect(route('admin.enquiries.show', $enquiry))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enquiries', [
            'id' => $enquiry->id,
            'status' => 'in_progress',
            'admin_notes' => 'Contacted customer. Awaiting response.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'enquiry.updated',
            'auditable_type' => Enquiry::class,
            'auditable_id' => $enquiry->id,
        ]);
    }

    public function test_admin_cannot_set_an_invalid_enquiry_status(): void
    {
        $admin = $this->admin();
        $enquiry = $this->enquiry();

        $this->actingAs($admin)
            ->patch(route('admin.enquiries.update', $enquiry), [
                'status' => 'fake_status',
                'admin_notes' => 'Test',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('enquiries', [
            'id' => $enquiry->id,
            'status' => 'new',
        ]);
    }
}
