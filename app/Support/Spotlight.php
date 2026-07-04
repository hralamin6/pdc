<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Spotlight
{
    /**
     * Search and return the results.
     */
    public function search(Request $request): Collection
    {
        $search = $request->search;

        return collect([
            User::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->take(5)
                ->get()
                ->map(function (User $user) {
                    return [
                        'avatar' => $user->getMedia('avatar')->first()?->getUrl() ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name),
                        'name' => $user->name,
                        'description' => $user->email,
                        'link' => "/users/{$user->id}",
                    ];
                }),
        ])->flatten(1)->filter(fn ($item) => str_contains(strtolower($item['name']), strtolower($search)));
    }
}
