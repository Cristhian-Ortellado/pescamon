<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatchRequest;
use App\Http\Requests\UpdateCatchRequest;
use App\Jobs\ProcessCatchImage;
use App\Models\FishCatch;
use App\Models\Species;
use App\Services\LevelingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CatchController extends Controller
{
    public function __construct(
        private readonly LevelingService $levelingService
    ) {}

    public function create(Request $request): Response
    {
        $species = Species::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'scientific_name', 'slug', 'rarity', 'average_weight_kg']);

        return Inertia::render('catches/Create', [
            'species' => $species,
        ]);
    }

    public function store(StoreCatchRequest $request): RedirectResponse
    {
        $user = $request->user();
        $imagePath = $request->file('image')->store('catches', 'public');

        $catch = FishCatch::create([
            'user_id' => $user->id,
            'species_id' => $request->input('species_id'),
            'weight_kg' => $request->input('weight_kg'),
            'length_cm' => $request->input('length_cm'),
            'image_path' => $imagePath,
            'caught_at' => $request->input('caught_at'),
            'method' => $request->input('method'),
            'bait_used' => $request->input('bait_used'),
            'water_body_type' => $request->input('water_body_type'),
            'location_name' => $request->input('location_name'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'notes' => $request->input('notes'),
            'status' => 'pending',
        ]);

        // Run synchronously so XP/achievements/verification are awarded within
        // the request; the redirect target then shows the celebration overlay.
        ProcessCatchImage::dispatchSync($catch);

        return redirect()->route('catches.show', $catch)
            ->with('success', '¡Captura registrada!');
    }

    public function edit(Request $request, FishCatch $catch): Response
    {
        abort_unless($request->user()->id === $catch->user_id, 403);

        $species = Species::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'scientific_name', 'slug', 'rarity', 'average_weight_kg']);

        return Inertia::render('catches/Edit', [
            'species' => $species,
            'catch' => $catch->only([
                'id', 'species_id', 'weight_kg', 'length_cm', 'image_path',
                'caught_at', 'method', 'bait_used', 'water_body_type',
                'location_name', 'latitude', 'longitude', 'notes',
            ]),
        ]);
    }

    public function update(UpdateCatchRequest $request, FishCatch $catch): RedirectResponse
    {
        $data = [
            'species_id' => $request->input('species_id'),
            'weight_kg' => $request->input('weight_kg'),
            'length_cm' => $request->input('length_cm'),
            'caught_at' => $request->input('caught_at'),
            'method' => $request->input('method'),
            'bait_used' => $request->input('bait_used'),
            'water_body_type' => $request->input('water_body_type'),
            'location_name' => $request->input('location_name'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'notes' => $request->input('notes'),
        ];

        // When a new photo is uploaded, replace the stored files and regenerate
        // the thumbnail. We deliberately do NOT re-run ProcessCatchImage so the
        // catch is not re-awarded XP/achievements (rewards are a one-time event).
        if ($request->hasFile('image')) {
            $oldImage = $catch->image_path;
            $oldThumbnail = $catch->thumbnail_path;

            $catch->image_path = $request->file('image')->store('catches', 'public');
            $catch->save();

            $catch->thumbnail_path = ProcessCatchImage::generateThumbnail($catch);

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            if ($oldThumbnail && $oldThumbnail !== $catch->thumbnail_path) {
                Storage::disk('public')->delete($oldThumbnail);
            }
        }

        $catch->update($data);

        return redirect()->route('catches.show', $catch)
            ->with('success', 'Captura actualizada.');
    }

    public function show(Request $request, FishCatch $catch): Response
    {
        $catch->load(['species', 'user:id,name,level,avatar_path', 'comments.user:id,name,avatar_path', 'likes']);

        $isLiked = $request->user()->likes()->where('catch_id', $catch->id)->exists();

        return Inertia::render('catches/Show', [
            'catch' => [
                ...$catch->toArray(),
                'likes_count' => $catch->likes()->count(),
                'is_liked' => $isLiked,
                'can_edit' => $request->user()->id === $catch->user_id,
            ],
            'levelProgress' => $this->levelingService->levelProgress($request->user()),
        ]);
    }
}
