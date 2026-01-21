<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)
            ->scopes(['user:email'])
            ->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $response = Socialite::driver($provider)->user();

        $user = User::firstWhere(['email' => $response->getEmail()]);
        if (!$user) {
            return redirect()->route('socialite.redirect', ['provider' => $provider])
                ->withErrors(['email' => 'No user found with the email ' . $response->getEmail()]);
        }

        $user->update([$provider . '_id' => $response->getId()]);

        auth()->login($user);

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateProvider(string $provider): array
    {
        return Validator::make(
            ['provider' => $provider],
            ['provider' => 'in:github']
        )->validate();
    }
}
