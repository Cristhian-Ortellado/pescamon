<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditCatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_the_edit_page(): void
    {
        $owner = User::factory()->create();
        $catch = FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => Species::factory()->create(['is_active' => true]),
        ]);

        $this->actingAs($owner)
            ->get(route('catches.edit', $catch))
            ->assertOk();
    }

    public function test_non_owner_cannot_open_the_edit_page(): void
    {
        $catch = FishCatch::factory()->create([
            'species_id' => Species::factory()->create(['is_active' => true]),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('catches.edit', $catch))
            ->assertForbidden();
    }

    public function test_owner_can_update_their_catch(): void
    {
        $owner = User::factory()->create();
        $species = Species::factory()->create(['is_active' => true, 'average_weight_kg' => 5]);
        $newSpecies = Species::factory()->create(['is_active' => true, 'average_weight_kg' => 5]);

        $catch = FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => $species->id,
            'weight_kg' => 2,
            'notes' => 'antes',
        ]);

        $this->actingAs($owner)
            ->put(route('catches.update', $catch), [
                'species_id' => $newSpecies->id,
                'weight_kg' => 3.5,
                'caught_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'method' => 'fly',
                'water_body_type' => 'lake',
                'notes' => 'después',
            ])
            ->assertRedirect(route('catches.show', $catch));

        $this->assertDatabaseHas('catches', [
            'id' => $catch->id,
            'species_id' => $newSpecies->id,
            'weight_kg' => 3.5,
            'method' => 'fly',
            'water_body_type' => 'lake',
            'notes' => 'después',
        ]);
    }

    public function test_non_owner_cannot_update_the_catch(): void
    {
        $species = Species::factory()->create(['is_active' => true]);
        $catch = FishCatch::factory()->create([
            'species_id' => $species->id,
            'weight_kg' => 2,
        ]);

        $this->actingAs(User::factory()->create())
            ->put(route('catches.update', $catch), [
                'species_id' => $species->id,
                'weight_kg' => 9,
                'caught_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'method' => 'fly',
                'water_body_type' => 'lake',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('catches', [
            'id' => $catch->id,
            'weight_kg' => 2,
        ]);
    }

    public function test_update_does_not_re_award_xp(): void
    {
        $owner = User::factory()->create();
        $species = Species::factory()->create(['is_active' => true, 'average_weight_kg' => 5]);
        $catch = FishCatch::factory()->create([
            'user_id' => $owner->id,
            'species_id' => $species->id,
            'xp_earned' => 42,
        ]);

        $xpBefore = $owner->fresh()->xp ?? null;

        $this->actingAs($owner)
            ->put(route('catches.update', $catch), [
                'species_id' => $species->id,
                'weight_kg' => 4,
                'caught_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'method' => 'fly',
                'water_body_type' => 'lake',
            ])
            ->assertRedirect();

        // xp_earned on the catch is untouched by an edit.
        $this->assertSame(42, $catch->fresh()->xp_earned);
        $this->assertSame($xpBefore, $owner->fresh()->xp ?? null);
    }
}
