<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_relationships_work_correctly(): void
    {
        $user = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $category = VehicleCategory::create([
            'name' => 'SUV',
            'slug' => 'suv',
            'description' => 'Sport utility vehicles',
        ]);

        $pickupBranch = Branch::create([
            'name' => 'Nairobi Branch',
            'location' => 'Nairobi',
        ]);

        $returnBranch = Branch::create([
            'name' => 'Mombasa Branch',
            'location' => 'Mombasa',
        ]);

        $vehicle = Vehicle::create([
            'vehicle_category_id' => $category->id,
            'registration_number' => 'KDA 123A',
            'make' => 'Toyota',
            'model' => 'RAV4',
            'year' => 2024,
            'seats' => 5,
            'daily_rate' => 8000,
        ]);

        $booking = Booking::create([
            'booking_reference' => 'MS-TEST-001',
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'pickup_branch_id' => $pickupBranch->id,
            'return_branch_id' => $returnBranch->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0712345678',
            'pickup_at' => now()->addDays(1),
            'return_at' => now()->addDays(4),
            'rental_days' => 3,
            'daily_rate' => 8000,
            'total_amount' => 24000,
        ]);

        $guestBooking = Booking::create([
            'booking_reference' => 'MS-TEST-002',
            'user_id' => null,
            'vehicle_id' => $vehicle->id,
            'pickup_branch_id' => $pickupBranch->id,
            'return_branch_id' => $returnBranch->id,
            'customer_name' => 'Guest Customer',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '0798765432',
            'pickup_at' => now()->addDays(5),
            'return_at' => now()->addDays(7),
            'rental_days' => 2,
            'daily_rate' => 8000,
            'total_amount' => 16000,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 24000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Great service.',
            'is_published' => true,
        ]);

        $enquiry = Enquiry::create([
            'name' => 'Test Enquirer',
            'email' => 'enquiry@example.com',
            'subject' => 'General enquiry',
            'message' => 'I would like more information.',
        ]);

        $auditLog = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'booking.created',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'description' => 'Test booking created.',
        ]);

        // User relationships
        $this->assertCount(1, $user->bookings);
        $this->assertSame($user->id, $booking->user->id);

        // Guest booking
        $this->assertNull($guestBooking->user_id);
        $this->assertNull($guestBooking->user);

        // Vehicle relationships
        $this->assertSame($category->id, $vehicle->category->id);
        $this->assertCount(2, $vehicle->bookings);
        $this->assertCount(1, $category->vehicles);

        // Branch relationships
        $this->assertSame($pickupBranch->id, $booking->pickupBranch->id);
        $this->assertSame($returnBranch->id, $booking->returnBranch->id);
        $this->assertCount(2, $pickupBranch->pickupBookings);
        $this->assertCount(2, $returnBranch->returnBookings);

        // Payment and review relationships
        $this->assertSame($booking->id, $payment->booking->id);
        $this->assertCount(1, $booking->payments);
        $this->assertSame($review->id, $booking->review->id);

        // Enquiry
        $this->assertSame('new', $enquiry->fresh()->status);

        // Audit log relationships
        $this->assertSame($user->id, $auditLog->user->id);
        $this->assertSame($booking->id, $auditLog->auditable->id);
    }
}
