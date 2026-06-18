<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatchRequest;
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
            'species_id' => $request->species_id,
            'weight_kg' => $request->weight_kg,
            'length_cm' => $request->length_cm,
            'image_path' => $imagePath,
            'caught_at' => $request->caught_at,
            'method' => $request->method,
            'bait_used' => $request->bait_used,
            'water_body_type' => $request->water_body_type,
            'location_name' => $request->location_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        ProcessCatchImage::dispatch($catch);

        return redirect()->route('catches.show', $catch)
            ->with('success', 'Captura enviada para revisión. ¡Recibirás XP cuando sea verificada!');
    }

    public function show(Request $request, FishCatch $catch): Response
    {
        $catch->load(['species', 'user:id,name,level', 'comments.user:id,name', 'likes']);

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
