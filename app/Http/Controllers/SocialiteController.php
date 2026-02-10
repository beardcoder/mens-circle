<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\GithubProvider;

class SocialiteController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_PROVIDERS = ['github'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(\in_array($provider, self::ALLOWED_PROVIDERS, true), 404);

        /** @var GithubProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver
            ->scopes(['user:email'])
            ->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(\in_array($provider, self::ALLOWED_PROVIDERS, true), 404);

        try {
            $response = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('filament.admin.auth.login')
                ->withErrors([
'email' => 'Authentifizierung fehlgeschlagen. Bitte versuche es erneut.'
]);
        }

        $user = User::firstWhere('email', $response->getEmail());

        if (!$user) {
            return redirect()->route('filament.admin.auth.login')
                ->withErrors([
'email' => 'Kein Benutzer mit dieser E-Mail-Adresse gefunden.'
]);
        }

        $user->update([
'github_id' => $response->getId()
]);

        auth()
->login($user);

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
