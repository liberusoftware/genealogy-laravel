<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Liberu\Foundation\Identity\Socialstream\Models\ConnectedAccount;
use Liberu\Genealogy\Discovery\Models\SocialConnectionPrivacy;
use Liberu\Genealogy\Discovery\Models\SocialFamilyConnection;
use Liberu\Genealogy\People\Models\Person;

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
        return SocialConnectionPrivacy::query()->firstOrCreate(['user_id' => $userId], [
            'allow_family_discovery' => true,
            'show_profile_to_matches' => true,
            'share_tree_with_matches' => false,
            'allow_contact_from_matches' => true,
        ]);
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

    /** @return Collection<int, array<string, mixed>> */
    public function findPotentialConnections(Model $user): Collection
    {
        $privacy = $this->privacy($user->getKey());
        if (! $privacy->allow_family_discovery) {
            return collect();
        }

        $surnames = $this->familySurnames($user);
        if ($surnames === []) {
            return collect();
        }

        $connections = collect();
        $accounts = ConnectedAccount::query()
            ->where('user_id', $user->getKey())
            ->where('enable_family_matching', true)
            ->get();

        foreach ($accounts as $account) {
            $otherAccounts = ConnectedAccount::query()
                ->where('provider', $account->provider)
                ->where('enable_family_matching', true)
                ->where('user_id', '!=', $user->getKey())
                ->with('user')
                ->get();

            foreach ($otherAccounts as $otherAccount) {
                $otherUser = $otherAccount->user;
                if ($otherUser === null || ! $this->privacy($otherUser->getKey())->allow_family_discovery) {
                    continue;
                }

                $commonSurnames = array_values(array_intersect($surnames, $this->familySurnames($otherUser)));
                if ($commonSurnames === [] || SocialFamilyConnection::query()
                    ->where('user_id', $user->getKey())
                    ->where('connected_account_id', $account->getKey())
                    ->where('matched_social_id', $otherAccount->provider_id)
                    ->exists()) {
                    continue;
                }

                $connections->push([
                    'user_id' => $otherUser->getKey(),
                    'social_id' => $otherAccount->provider_id,
                    'name' => $otherAccount->name,
                    'email' => $otherAccount->email,
                    'common_surnames' => $commonSurnames,
                    'confidence_score' => min(100, count($commonSurnames) * 20),
                    'account_id' => $account->getKey(),
                ]);
            }
        }

        return $connections;
    }

    public function processMatches(Model $user): int
    {
        $count = 0;
        foreach ($this->findPotentialConnections($user) as $match) {
            $account = ConnectedAccount::query()->find($match['account_id']);
            if ($account === null) {
                continue;
            }

            SocialFamilyConnection::query()->create([
                'user_id' => $user->getKey(),
                'connected_account_id' => $account->getKey(),
                'matched_social_id' => $match['social_id'],
                'matched_name' => $match['name'],
                'matched_email' => $match['email'],
                'relationship_type' => 'potential_relative',
                'confidence_score' => $match['confidence_score'],
                'matching_criteria' => ['common_surnames' => $match['common_surnames']],
                'status' => 'pending',
            ]);
            $count++;
        }

        return $count;
    }

    /** @return list<string> */
    private function familySurnames(Model $user): array
    {
        $teamIds = method_exists($user, 'allTeams')
            ? $user->allTeams()->pluck('id')->all()
            : [];

        if ($teamIds === []) {
            return [];
        }

        return Person::query()
            ->withoutGlobalScopes()
            ->whereIn('team_id', $teamIds)
            ->whereNotNull('family_name')
            ->pluck('family_name')
            ->map(static fn (mixed $surname): string => mb_strtolower(trim((string) $surname)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
