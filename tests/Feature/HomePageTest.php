<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_the_main_sections_and_available_vehicles(): void
    {
        Vehicle::factory()->create([
            'make' => 'Toyota',
            'model' => 'Vitz',
            'status' => 'available',
        ]);

        Vehicle::factory()->create([
            'make' => 'Nissan',
            'model' => 'X-Trail',
            'status' => 'maintenance',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Drive in Comfort')
            ->assertSee('Explore Our Fleet')
            ->assertSee('Toyota')
            ->assertSee('Vitz')
            ->assertSee('Why Choose Us?')
            ->assertSee('Our Services')
            ->assertSee('How It Works')
            ->assertDontSee('X-Trail');
    }

    public function test_homepage_shows_an_empty_fleet_message_when_no_vehicles_are_available(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Our fleet is being updated');
    }
}
