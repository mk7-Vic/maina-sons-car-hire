<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVehicleCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_and_customer_cannot_manage_categories(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_categories(): void
    {
        $category = VehicleCategory::factory()->create(['name' => 'SUV']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('SUV');
    }

    public function test_admin_can_create_category_and_audit_it(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Executive Cars',
                'description' => 'Premium executive vehicles.',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = VehicleCategory::where('name', 'Executive Cars')->firstOrFail();

        $this->assertSame('executive-cars', $category->slug);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'vehicle_category.created',
            'auditable_id' => $category->id,
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = $this->admin();
        $category = VehicleCategory::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'New Name',
                'description' => 'Updated description.',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('vehicle_categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'vehicle_category.updated',
        ]);
    }

    public function test_category_with_assigned_vehicles_cannot_be_deleted(): void
    {
        $category = VehicleCategory::factory()->create();
        Vehicle::factory()->create(['vehicle_category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('vehicle_categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        $admin = $this->admin();
        $category = VehicleCategory::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('vehicle_categories', ['id' => $category->id]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'vehicle_category.deleted',
            'auditable_id' => $category->id,
        ]);
    }

    public function test_public_fleet_can_be_filtered_by_category(): void
    {
        $suv = VehicleCategory::factory()->create(['slug' => 'suv']);
        $sedan = VehicleCategory::factory()->create(['slug' => 'sedan']);

        $suvVehicle = Vehicle::factory()->create([
            'vehicle_category_id' => $suv->id,
            'status' => 'available',
            'registration_number' => 'KDA 111A',
            'make' => 'Toyota',
            'model' => 'RAV4',
        ]);

        $sedanVehicle = Vehicle::factory()->create([
            'vehicle_category_id' => $sedan->id,
            'status' => 'available',
            'registration_number' => 'KDB 222B',
            'make' => 'Nissan',
            'model' => 'Sunny',
        ]);

        $this->get(route('vehicles.index', ['category' => 'suv']))
            ->assertOk()
            ->assertSee('Toyota RAV4')
            ->assertDontSee('Nissan Sunny');
    }
}
