<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // Map markers are fetched lazily by the visible viewport via
        // MapController@catches, so no catches are shipped with the page.
        return Inertia::render('dashboard/Map', [
            'userLevel' => $user->level,
            'userXp' => $user->xp,
        ]);
    }
}
