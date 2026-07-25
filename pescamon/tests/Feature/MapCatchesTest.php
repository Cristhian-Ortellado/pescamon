<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapCatchesTest extends TestCase
{
    use RefreshDatabase;

    private const BBOX = [
        'south' => -30,
        'north' => -20,
        'west' => -60,
        'east' => -50,
    ];

    public function test_guests_cannot_load_map_catches(): void
    {
        $this->get(route('map.catches'))->assertRedirect(route('login'));
    }

    public function test_it_returns_only_verified_catches_inside_the_bounding_box(): void
    {
        $this->actingAs(User::factory()->create());
        $species = Species::factory()->create();
        $owner = User::factory()->create(['location_privacy' => 'exact']);

        $inside = FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => $species->id,
            'latitude' => -25,
            'longitude' => -55,
        ]);

        // Out of the box by latitude.
        FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => $species->id,
            'latitude' => -10,
            'longitude' => -55,
        ]);

        // Out of the box by longitude.
        FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => $species->id,
            'latitude' => -25,
            'longitude' => -70,
        ]);

        $ids = collect($this->getJson(route('map.catches', self::BBOX))->assertOk()->json('catches'))
            ->pluck('id');

        $this->assertTrue($ids->contains($inside->id));
        $this->assertCount(1, $ids);
    }

    public function test_it_excludes_pending_catches(): void
    {
        $this->actingAs(User::factory()->create());
        $owner = User::factory()->create(['location_privacy' => 'exact']);

        FishCatch::factory()->pending()->create([
            'user_id' => $owner->id,
            'species_id' => Species::factory(),
            'latitude' => -25,
            'longitude' => -55,
        ]);

        $this->getJson(route('map.catches', self::BBOX))
            ->assertOk()
            ->assertJsonCount(0, 'catches');
    }

    public function test_it_excludes_catches_from_users_who_hid_their_location(): void
    {
        $this->actingAs(User::factory()->create());
        $hidden = User::factory()->create(['location_privacy' => 'hidden']);

        FishCatch::factory()->create([
            'user_id' => $hidden->id,
            'species_id' => Species::factory(),
            'latitude' => -25,
            'longitude' => -55,
        ]);

        $this->getJson(route('map.catches', self::BBOX))
            ->assertOk()
            ->assertJsonCount(0, 'catches');
    }

    public function test_exact_location_coordinates_are_returned_with_precision(): void
    {
        $this->actingAs(User::factory()->create());
        $owner = User::factory()->create(['location_privacy' => 'exact']);

        FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => Species::factory(),
            'latitude' => -25.1234567,
            'longitude' => -55.7654321,
        ]);

        $catch = $this->getJson(route('map.catches', self::BBOX))
            ->assertOk()
            ->json('catches.0');

        // Exact privacy → rounded to 5 decimals, not blurred.
        $this->assertSame(-25.12346, $catch['latitude']);
        $this->assertSame(-55.76543, $catch['longitude']);
    }

    public function test_it_caps_the_number_of_markers_per_request(): void
    {
        $this->actingAs(User::factory()->create());
        $owner = User::factory()->create(['location_privacy' => 'exact']);
        $species = Species::factory()->create();

        FishCatch::factory()->count(410)->create([
            'user_id' => $owner->id,
            'species_id' => $species->id,
        ]);

        // No bounding box → returns the most recent catches up to the cap.
        $this->getJson(route('map.catches'))
            ->assertOk()
            ->assertJsonCount(400, 'catches');
    }
}
