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

        $panel = Filament::getPanel('app');

        // When the panel uses tenancy and the newly registered user has no
        // default tenant yet, send them to the team-creation page.
        if ($panel->hasTenancy() && ! $user->getDefaultTenant($panel)) {
            return redirect('/app/new');
        }

        // Under the paywall (#1635, opt-in since #1638) every new account must
        // subscribe before it can use anything, so send them straight to checkout
        // — unless they already have access (e.g. an affiliate comp), in which
        // case the app is reachable. currentTeam exists here because CreateNewUser
        // creates + switches a personal team during registration.
        //
        // The same flag as EnsureSubscribed on purpose: a gate that redirected new
        // accounts at a checkout they don't need, on an app they can already use,
        // would be half a switch.
        if (config('subscription.paywall_enabled') && ! $user->isPremium() && $user->currentTeam) {
            return redirect()->route('filament.app.pages.subscription', ['tenant' => $user->currentTeam]);
        }

        return redirect()->intended(Filament::getUrl());
    }
}
