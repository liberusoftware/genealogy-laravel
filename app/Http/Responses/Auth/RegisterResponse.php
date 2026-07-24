<?php

namespace App\Http\Responses\Auth;

use App\Models\User;
use Filament\Facades\Filament;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): mixed
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // A signup that came in through a premium CTA (?plan=premium, stashed by
        // the /register route) goes straight to checkout, not the app dashboard.
        // currentTeam exists here because CreateNewUser creates+switches a
        // personal team during registration.
        if ($request->session()->pull('premium_intent') && $user->currentTeam) {
            return redirect()->route('filament.app.pages.subscription', ['tenant' => $user->currentTeam]);
        }

        $panel = Filament::getPanel('app');

        // When the panel uses tenancy and the newly registered user has no
        // default tenant yet, send them to the team-creation page.
        if ($panel->hasTenancy() && ! $user->getDefaultTenant($panel)) {
            return redirect('/app/new');
        }

        return redirect()->intended(Filament::getUrl());
    }
}
