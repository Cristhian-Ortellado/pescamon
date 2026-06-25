<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /** Toggle the current user's like on a catch. */
    public function toggle(Request $request, FishCatch $catch): RedirectResponse
    {
        $userId = $request->user()->id;

        $existing = $catch->likes()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
        } else {
            $catch->likes()->create(['user_id' => $userId]);
        }

        return back();
    }
}
