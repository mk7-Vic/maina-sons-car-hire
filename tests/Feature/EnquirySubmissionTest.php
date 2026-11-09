<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquirySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_available(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Contact Maina & Sons');
    }

    public function test_customer_can_submit_an_enquiry(): void
    {
        $this->post(route('enquiries.store'), [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'subject' => 'Vehicle availability',
            'message' => 'I would like to enquire about vehicle availability.',
        ])->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enquiries', [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'subject' => 'Vehicle availability',
            'status' => 'new',
        ]);
    }

    public function test_enquiry_submission_validates_required_fields(): void
    {
        $this->post(route('enquiries.store'), [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => '',
        ])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_public_submission_cannot_set_admin_fields(): void
    {
        $this->post(route('enquiries.store'), [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'subject' => 'Question',
            'message' => 'A customer question.',
            'status' => 'resolved',
            'admin_notes' => 'Forged internal note',
        ])->assertRedirect(route('contact'));

        $enquiry = Enquiry::firstOrFail();

        $this->assertSame('new', $enquiry->status);
        $this->assertNull($enquiry->admin_notes);
    }
}
