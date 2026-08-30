<?php

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    config()->set('premium.enabled', false);
    config()->set('premium.require_card', true);
});

it('keeps the application fully open when premium is disabled', function (): void {
    $user = User::factory()->create();

    expect($user->isPremium())->toBeTrue()
        ->and($user->isPremiumSuspended())->toBeFalse();
});

it('uses the requested trial and pricing defaults for SaaS mode', function (): void {
    config()->set('premium.enabled', true);

    $pricing = app(SubscriptionService::class)->pricing();

    expect($pricing['trial_days'])->toBe(7)
        ->and($pricing['monthly']['amount'])->toBe(249)
        ->and($pricing['yearly']['amount'])->toBe(2499)
        ->and($pricing['currency'])->toBe('GBP')
        ->and($pricing['require_card'])->toBeTrue();
});

it('supports a no-card local trial when explicitly configured', function (): void {
    config()->set('premium.enabled', true);
    config()->set('premium.require_card', false);
    $user = User::factory()->create();

    app(SubscriptionService::class)->startLocalTrial($user);

    expect($user->fresh()->isPremium())->toBeTrue()
        ->and($user->fresh()->onPremiumTrial())->toBeTrue()
        ->and($user->fresh()->trial_ends_at->isBetween(now()->addDays(6), now()->addDays(8)))->toBeTrue();
});

it('keeps a cancelled no-card trial available until it expires then suspends it', function (): void {
    config()->set('premium.enabled', true);
    config()->set('premium.require_card', false);
    $user = User::factory()->create();
    $service = app(SubscriptionService::class);

    $service->startLocalTrial($user);
    $service->cancel($user->fresh());

    expect($user->fresh()->isPremium())->toBeTrue()
        ->and($user->fresh()->premium_cancelled_at)->not->toBeNull();

    $user->forceFill(['trial_ends_at' => now()->subMinute()])->save();

    expect($user->fresh()->isPremium())->toBeFalse()
        ->and($user->fresh()->isPremiumSuspended())->toBeTrue();
});
