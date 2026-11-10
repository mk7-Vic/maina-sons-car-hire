<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminVehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function vehicleData(array $overrides = []): array
    {
        return array_merge([
            'vehicle_category_id' => VehicleCategory::factory()->create()->id,
            'registration_number' => 'KDA 123A',
            'make' => 'Toyota',
            'model' => 'RAV4',
            'year' => 2024,
            'color' => 'Black',
            'transmission' => 'Automatic',
            'fuel_type' => 'Petrol',
            'seats' => 5,
            'daily_rate' => 8000,
            'description' => 'Reliable family vehicle.',
            'status' => 'available',
        ], $overrides);
    }

    public function test_guest_cannot_access_vehicle_management(): void
    {
        $this->get(route('admin.vehicles.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.vehicles.create'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_vehicle_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('admin.vehicles.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_vehicles_and_create_form(): void
    {
        $admin = $this->admin();
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.vehicles.index'))
            ->assertOk()
            ->assertSee($vehicle->registration_number);

        $this->get(route('admin.vehicles.create'))
            ->assertOk()
            ->assertSee('Add Vehicle');
    }

    public function test_admin_can_create_vehicle_and_audit_log_is_recorded(): void
    {
        $admin = $this->admin();
        $data = $this->vehicleData();

        $this->actingAs($admin)
            ->post(route('admin.vehicles.store'), $data)
            ->assertRedirect(route('admin.vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'KDA 123A',
            'make' => 'Toyota',
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'vehicle.created',
        ]);
    }

    public function test_duplicate_registration_number_is_rejected(): void
    {
        $admin = $this->admin();
        $vehicle = Vehicle::factory()->create([
            'registration_number' => 'KDA 123A',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.vehicles.store'), $this->vehicleData())
            ->assertSessionHasErrors('registration_number');

        $this->assertSame(
            1,
            Vehicle::where('registration_number', 'KDA 123A')->count()
        );
    }

    public function test_admin_can_update_vehicle_and_audit_log_is_recorded(): void
    {
        $admin = $this->admin();
        $vehicle = Vehicle::factory()->create();

        $data = $this->vehicleData([
            'vehicle_category_id' => $vehicle->vehicle_category_id,
            'registration_number' => $vehicle->registration_number,
            'make' => 'Nissan',
            'model' => 'X-Trail',
            'status' => 'maintenance',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.vehicles.update', $vehicle), $data)
            ->assertRedirect(route('admin.vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'make' => 'Nissan',
            'model' => 'X-Trail',
            'status' => 'maintenance',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'vehicle.updated',
            'auditable_id' => $vehicle->id,
        ]);
    }

    public function test_vehicle_with_booking_history_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $vehicle = Vehicle::factory()->create();

        Booking::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.vehicles.destroy', $vehicle))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_admin_can_delete_vehicle_without_booking_history(): void
    {
        $admin = $this->admin();
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.vehicles.destroy', $vehicle))
            ->assertRedirect(route('admin.vehicles.index'));

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'vehicle.deleted',
            'auditable_id' => $vehicle->id,
        ]);
    }

    public function test_admin_can_upload_a_vehicle_photo_when_creating_a_vehicle(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $data = $this->vehicleData([
            'image' => UploadedFile::fake()->create('vehicle.jpg', 10, 'image/jpeg'),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.vehicles.store'), $data)
            ->assertRedirect(route('admin.vehicles.index'));

        $vehicle = Vehicle::where('registration_number', 'KDA 123A')->firstOrFail();

        $this->assertNotNull($vehicle->image_path);
        Storage::disk('public')->assertExists($vehicle->image_path);
    }

    public function test_admin_can_replace_an_existing_vehicle_photo(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        Storage::disk('public')->put('vehicles/old-photo.jpg', 'old photo');

        $vehicle = Vehicle::factory()->create([
            'image_path' => 'vehicles/old-photo.jpg',
        ]);

        $data = $this->vehicleData([
            'vehicle_category_id' => $vehicle->vehicle_category_id,
            'registration_number' => $vehicle->registration_number,
            'image' => UploadedFile::fake()->create('replacement.jpg', 10, 'image/jpeg'),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.vehicles.update', $vehicle), $data)
            ->assertRedirect(route('admin.vehicles.index'));

        $vehicle->refresh();

        $this->assertNotSame('vehicles/old-photo.jpg', $vehicle->image_path);
        Storage::disk('public')->assertMissing('vehicles/old-photo.jpg');
        Storage::disk('public')->assertExists($vehicle->image_path);
    }

    public function test_admin_can_remove_an_existing_vehicle_photo(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        Storage::disk('public')->put('vehicles/remove-me.jpg', 'old photo');

        $vehicle = Vehicle::factory()->create([
            'image_path' => 'vehicles/remove-me.jpg',
        ]);

        $data = $this->vehicleData([
            'vehicle_category_id' => $vehicle->vehicle_category_id,
            'registration_number' => $vehicle->registration_number,
            'remove_image' => 1,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.vehicles.update', $vehicle), $data)
            ->assertRedirect(route('admin.vehicles.index'));

        $this->assertNull($vehicle->fresh()->image_path);
        Storage::disk('public')->assertMissing('vehicles/remove-me.jpg');
    }
}
