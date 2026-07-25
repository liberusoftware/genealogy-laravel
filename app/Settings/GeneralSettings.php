<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    /** What a shared link says under its title, when the page has nothing more specific. */
    public ?string $site_description = null;

    /** Absolute URL or public path; blank falls back to the bundled og-default.png. */
    public ?string $og_image = null;

    public ?string $site_email = null;

    public ?string $site_phone = null;

    public ?string $site_address = null;

    public ?string $site_country = null;

    public string $site_currency;

    public string $site_default_language;

    public ?string $facebook_url = null;

    public ?string $twitter_url = null;

    public ?string $github_url = null;

    public ?string $youtube_url = null;

    public string $footer_copyright;

    public static function group(): string
    {
        return 'general';
    }
}
