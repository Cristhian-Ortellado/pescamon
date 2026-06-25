<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PescadexSettingsController extends Controller
{
    /** Combined PescaDex settings screen. */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Index', [
            'preferences' => [
                'location_privacy' => $user->location_privacy,
                'unit_preference' => $user->unit_preference,
            ],
        ]);
    }

    /** Update location privacy + unit preferences. */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location_privacy' => ['required', Rule::in(['exact', 'approximate', 'hidden'])],
            'unit_preference' => ['required', Rule::in(['metric', 'imperial'])],
        ]);

        $request->user()->update($validated);

        return back();
    }
}
