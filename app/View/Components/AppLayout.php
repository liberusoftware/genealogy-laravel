<?php

namespace App\View\Components;

use App\Settings\GeneralSettings;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  bool  $bare  Render the slot without wrapping it in <main>, for
     *                      layouts that supply their own header/main/footer.
     *                      Without this the marketing layout nests a <main>
     *                      inside this one and puts <header>/<footer> in it.
     * @param  string|null  $pageTitle  Overrides the site name in <title> and og:title.
     *                                  The pages people actually share (home,
     *                                  /subscription) set it; everything else
     *                                  inherits the site name. #1648.
     * @param  string|null  $pageDescription  Overrides GeneralSettings::$site_description.
     */
    public function __construct(
        public bool $bare = false,
        public ?string $pageTitle = null,
        public ?string $pageDescription = null,
    ) {}

    /**
     * The preview image, always absolute — scrapers reject a relative path, and
     * a relative path is how this bug stays invisible until someone shares a link.
     */
    public function ogImage(): string
    {
        $configured = app(GeneralSettings::class)->og_image;

        if (blank($configured)) {
            return asset('images/og-default.png');
        }

        return Str::startsWith($configured, ['http://', 'https://'])
            ? $configured
            : asset(ltrim($configured, '/'));
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
