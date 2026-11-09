<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)
            ->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_dashboard_statistics(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $vehicles = Vehicle::factory()->count(2)->create();

        Booking::factory()->create([
            'vehicle_id' => $vehicles[0]->id,
            'status' => 'pending',
        ]);

        Booking::factory()->create([
            'vehicle_id' => $vehicles[1]->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Bookings');
        $response->assertSee('Pending Bookings');
        $response->assertSee('Confirmed Bookings');
        $response->assertSee('Total Vehicles');

        $response->assertSee('2');
    }

    public function test_admin_can_log_in(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_admin_credentials_are_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $response = $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_customer_cannot_log_in_through_admin_login(): void
    {
        $customer = User::factory()->create();

        $response = $this->post(route('admin.login.store'), [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $response->assertForbidden();
        $this->assertGuest();
    }

    public function test_admin_can_log_out(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $response = $this->actingAs($admin)
            ->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
