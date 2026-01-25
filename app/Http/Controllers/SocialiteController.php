<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Socialite;

class SocialiteController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, $this->allowedProviders()), 404);

        /** @var \Laravel\Socialite\Two\GithubProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver
            ->scopes(['user:email'])
            ->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, $this->allowedProviders()), 404);

        $response = Socialite::driver($provider)->user();

        $user = User::firstWhere(['email' => $response->getEmail()]);
        if (! $user) {
            return redirect()->route('socialite.redirect', ['provider' => $provider])
                ->withErrors(['email' => 'No user found with the email '.$response->getEmail()]);
        }

        $user->update([$provider.'_id' => $response->getId()]);

        auth()->login($user);

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    /**
     * @return array<int, string>
     */
    private function allowedProviders(): array
    {
        return ['github'];
    }
}
