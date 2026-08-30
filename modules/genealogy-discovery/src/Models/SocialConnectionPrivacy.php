<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SocialConnectionPrivacy extends Model
{
    protected $table = 'genealogy_social_connection_privacy';

    protected $fillable = ['user_id', 'allow_family_discovery', 'show_profile_to_matches', 'share_tree_with_matches', 'allow_contact_from_matches', 'blocked_users'];

    protected function casts(): array
    {
        return ['allow_family_discovery' => 'boolean', 'show_profile_to_matches' => 'boolean', 'share_tree_with_matches' => 'boolean', 'allow_contact_from_matches' => 'boolean', 'blocked_users' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'));
    }
}
