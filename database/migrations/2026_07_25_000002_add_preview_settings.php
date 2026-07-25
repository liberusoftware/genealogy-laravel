<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Link preview settings (#1648). `site_description` is what a shared link says
 * under its title; `og_image` overrides the bundled 1200x630 card for a
 * deployment that has rebranded. Blank og_image falls back to
 * public/images/og-default.png — see App\View\Components\AppLayout.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add(
            'general.site_description',
            'Open-source family tree platform built on evidence. GEDCOM in, GEDCOM out. DNA matches stated in words, not colours. A citation under every claim.'
        );
        $this->migrator->add('general.og_image', null);
    }
};
