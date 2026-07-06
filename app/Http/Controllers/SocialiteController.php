<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function loginSocial(Request $request, string $provider): RedirectResponse
    {
//        $this->validateProvider($request);
        return Socialite::driver($provider)->redirect();
    }

    public function callbackSocial(Request $request, string $provider)
    {
//        $this->validateProvider($request);
        $response = Socialite::driver($provider)->user();
        $user = User::firstOrCreate(
            ['email' => $response->getEmail()],
            ['password' => \Hash::make('pass'), 'name' => $response->getName() ?? $response->getNickname()]
        );
        // if email not verified, mark as verified
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        if (!$user->roles()->exists()){
            $user->assignRole('user');
        }
        $user->assignRole('user');
        if ($response->getAvatar()){
            $extension = pathinfo(parse_url($response->getAvatar(), PHP_URL_PATH), PATHINFO_EXTENSION);
            $media = $user->addMediaFromUrl($response->getAvatar())->usingFileName($response->getName() ?? $response->getNickname(). '.' . $extension)->toMediaCollection('profile');
            $path = storage_path("app/public/".$media->id.'/'. $media->file_name);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $data = [$provider . '_id' => $response->getId()];

        if ($user->wasRecentlyCreated) {
            $data['name'] = $response->getName() ?? $response->getNickname();
        }
        $user->update($data);
        Auth::login($user, remember: true);
        return redirect()->intended(route('app.dashboard', absolute: false));
    }

    protected function validateProvider(Request $request): array
    {
        return $this->getValidationFactory()->make(
            $request->route()->parameters(),
            ['provider' => 'in:facebook,google,github']
        )->validate();
    }
}

