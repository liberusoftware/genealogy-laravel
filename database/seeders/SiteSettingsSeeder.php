<?php

namespace Database\Seeders;

use App\Settings\GeneralSettings;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(GeneralSettings::class);

        $settings->site_name = config('app.name', 'Liberu Genealogy');
        // site_description and og_image are deliberately absent: the #1648
        // migration owns their defaults, and a second copy of that sentence here
        // would drift the day one of them is edited.
        $settings->site_email = 'info@liberugenealogy.com';
        $settings->site_phone = '+44 208 050 5865';
        $settings->site_address = '123 Genealogy St, London, UK';
        $settings->site_country = 'United Kingdom';
        $settings->site_currency = '£';
        $settings->site_default_language = 'en';
        $settings->facebook_url = 'https://www.facebook.com/familytree365';
        $settings->twitter_url = null;
        $settings->github_url = 'https://www.github.com/liberu-genealogy';
        $settings->youtube_url = null;
        $settings->footer_copyright = '© '.date('Y').' '.config('app.name', 'Liberu Genealogy').'. All rights reserved.';

        $settings->save();
    }
}
