<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /** Search anglers by name so the user can find people to follow. */
    public function search(Request $request): Response
    {
        $me = $request->user();
        $term = trim((string) $request->query('q', ''));

        $results = [];

        if ($term !== '') {
            $followingIds = $me->following()->pluck('users.id')->flip();

            $results = User::query()
                ->where('id', '!=', $me->id)
                ->where('name', 'like', '%'.$term.'%')
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'level', 'avatar_path'])
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'level' => $u->level,
                    'avatar' => $u->avatar,
                    'is_following' => $followingIds->has($u->id),
                ])
                ->values();
        }

        return Inertia::render('users/Search', [
            'query' => $term,
            'results' => $results,
        ]);
    }
}
