<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /** Toggle following another user. */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        if ($me->id === $user->id) {
            return back();
        }

        if ($me->following()->where('followed_id', $user->id)->exists()) {
            $me->following()->detach($user->id);
        } else {
            $me->following()->attach($user->id);
        }

        return back();
    }
}
