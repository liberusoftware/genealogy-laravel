<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Liberu\Foundation\Identity\Socialstream\Models\ConnectedAccount;
use Liberu\Genealogy\Discovery\Models\SocialConnectionPrivacy;
use Liberu\Genealogy\Discovery\Models\SocialFamilyConnection;

final class SocialFamilyDiscovery
{
    public function enable(ConnectedAccount $account): bool
    {
        try {
            $account->forceFill(['enable_family_matching' => true])->save();

            return $this->sync($account);
        } catch (\Throwable $exception) {
            Log::error('Failed to enable social family discovery.', ['account_id' => $account->getKey(), 'error' => $exception->getMessage()]);

            return false;
        }
    }

    public function disable(ConnectedAccount $account): bool
    {
        try {
            $account->forceFill(['enable_family_matching' => false, 'cached_profile_data' => null, 'last_synced_at' => null])->save();
            SocialFamilyConnection::query()->where('connected_account_id', $account->getKey())->delete();

            return true;
        } catch (\Throwable $exception) {
            Log::error('Failed to disable social family discovery.', ['account_id' => $account->getKey(), 'error' => $exception->getMessage()]);

            return false;
        }
    }

    public function sync(ConnectedAccount $account): bool
    {
        if (! (bool) $account->getAttribute('enable_family_matching')) {
            return false;
        }

        try {
            $account->forceFill([
                'cached_profile_data' => [
                    'name' => $account->name,
                    'email' => $account->email,
                    'nickname' => $account->nickname,
                    'provider' => $account->provider,
                    'provider_id' => $account->provider_id,
                    'fetched_at' => now()->toIso8601String(),
                ],
                'last_synced_at' => now(),
            ])->save();

            return true;
        } catch (\Throwable $exception) {
            Log::error('Failed to sync social family discovery account.', ['account_id' => $account->getKey(), 'error' => $exception->getMessage()]);

            return false;
        }
    }

    public function privacy(int|string $userId): SocialConnectionPrivacy
    {
        return SocialConnectionPrivacy::query()->firstOrCreate(['user_id' => $userId]);
    }

    public function updatePrivacy(int|string $userId, array $settings): SocialConnectionPrivacy
    {
        $privacy = $this->privacy($userId);
        $privacy->update($settings);

        return $privacy->fresh();
    }

    public function needsSync(ConnectedAccount $account): bool
    {
        if (! (bool) $account->getAttribute('enable_family_matching')) {
            return false;
        }

        $lastSyncedAt = $account->getAttribute('last_synced_at');

        return $lastSyncedAt === null || Carbon::parse($lastSyncedAt)->diffInHours(now()) >= 24;
    }
}
