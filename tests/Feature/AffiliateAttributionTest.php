<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Middleware\CaptureReferral;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tests\TestCase;

class AffiliateAttributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['affiliate.enabled' => true, 'premium.enabled' => false]);
    }

    /** @param array<string,string> $cookies */
    private function register(string $email, array $cookies = []): User
    {
        $request = Request::create('/register', 'POST', [], $cookies);
        $this->app->instance('request', $request);

        return app(CreateNewUser::class)->create([
            'name' => 'New Person',
            'email' => $email,
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);
    }

    public function test_middleware_stashes_valid_ref_code_for_guest(): void
    {
        $referrer = User::factory()->create();
        $referrer->referralCode();

        $req = Request::create('/?ref='.$referrer->referral_code);
        (new CaptureReferral)->handle($req, fn () => response('ok'));

        $this->assertTrue(Cookie::hasQueued('referral'));
        $this->assertSame($referrer->referral_code, Cookie::queued('referral')->getValue());
    }

    public function test_middleware_ignores_unknown_code(): void
    {
        $req = Request::create('/?ref=NOPENOPE');
        (new CaptureReferral)->handle($req, fn () => response('ok'));

        $this->assertFalse(Cookie::hasQueued('referral'));
    }

    public function test_middleware_no_op_when_dormant(): void
    {
        config(['premium.enabled' => true]); // everyone premium => program dormant
        $referrer = User::factory()->create();
        $referrer->referralCode();

        $req = Request::create('/?ref='.$referrer->referral_code);
        (new CaptureReferral)->handle($req, fn () => response('ok'));

        $this->assertFalse(Cookie::hasQueued('referral'));
    }

    public function test_registration_binds_pending_referral_and_clears_cookie(): void
    {
        $referrer = User::factory()->create();
        $referrer->referralCode();

        $referred = $this->register('referred@example.test', ['referral' => $referrer->referral_code]);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'status' => Referral::STATUS_PENDING,
        ]);
        $this->assertTrue(Cookie::hasQueued('referral')); // forget() is a queued cookie
        $this->assertTrue(Cookie::queued('referral')->isCleared());
    }

    public function test_unknown_cookie_code_binds_nothing(): void
    {
        $referred = $this->register('nobody@example.test', ['referral' => 'GHOSTNONE']);

        $this->assertDatabaseMissing('referrals', ['referred_user_id' => $referred->id]);
    }

    public function test_no_cookie_binds_nothing(): void
    {
        $referred = $this->register('plain@example.test');

        $this->assertDatabaseMissing('referrals', ['referred_user_id' => $referred->id]);
    }

    public function test_referral_link_points_at_root_with_ref(): void
    {
        $user = User::factory()->create();

        $this->assertSame(url('/').'?ref='.$user->referralCode(), $user->referralLink());
    }
}
