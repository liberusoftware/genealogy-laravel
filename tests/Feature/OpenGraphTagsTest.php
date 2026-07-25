<?php

namespace Tests\Feature;

use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Link previews (#1648). Sharing any public URL used to render a blank thumbnail
 * and the seeded brand name, because the head carried no og/twitter tags at all.
 *
 * Two things are guarded here. First, that every public page emits a preview card
 * with an *absolute* image URL — scrapers reject relative paths, which is the
 * exact way this silently stays broken. Second, that every string comes from
 * GeneralSettings rather than a hardcode: the complaint in #1648 is that a
 * renamed deployment still previewed as "Liberu Genealogy".
 */
class OpenGraphTagsTest extends TestCase
{
    use RefreshDatabase;

    /** Every public route, as registered in routes/web.php. */
    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'subscription' => ['/subscription'],
            'about' => ['/about'],
            'privacy' => ['/privacy'],
            'terms' => ['/terms-and-conditions'],
            'contact' => ['/contact'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_page_emits_a_complete_preview_card(string $path): void
    {
        $html = $this->get($path)->assertOk()->getContent();

        // The image must be absolute or the card renders blank.
        $this->assertMatchesRegularExpression(
            '#<meta property="og:image" content="https?://[^"]+\.png"#',
            $html,
            "og:image on {$path} must be an absolute URL"
        );

        foreach (['og:title', 'og:description', 'og:url', 'og:type', 'og:site_name'] as $property) {
            $this->assertMatchesRegularExpression(
                '#<meta property="'.preg_quote($property, '#').'" content="[^"]+"#',
                $html,
                "{$property} missing or empty on {$path}"
            );
        }

        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertMatchesRegularExpression('#<meta name="twitter:image" content="https?://[^"]+"#', $html);
    }

    public function test_preview_strings_follow_site_settings(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->site_name = 'Ancestral Registry';
        $settings->site_description = 'A different platform entirely.';
        $settings->save();

        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:site_name" content="Ancestral Registry">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="Ancestral Registry">', $html);
        $this->assertStringContainsString('<meta property="og:description" content="A different platform entirely.">', $html);
        $this->assertStringContainsString('<title>Ancestral Registry</title>', $html);

        // Scoped to the head: /about's body copy ("What Liberu is") is prose this
        // effort deliberately leaves alone, and footer_copyright is itself a
        // setting. What must not survive a rename is a hardcode in the preview.
        $head = substr($html, 0, (int) strpos($html, '</head>'));
        $this->assertStringNotContainsString('Liberu', $head);
    }

    public function test_og_image_setting_overrides_the_bundled_default(): void
    {
        $this->assertStringContainsString(
            '<meta property="og:image" content="'.asset('images/og-default.png').'">',
            $this->get('/')->getContent(),
            'blank og_image must fall back to the bundled PNG'
        );

        $settings = app(GeneralSettings::class);
        $settings->og_image = 'https://cdn.example.com/custom-card.png';
        $settings->save();

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('<meta property="og:image" content="https://cdn.example.com/custom-card.png">', $html);
        $this->assertStringNotContainsString('og-default.png', $html);
    }

    public function test_a_relative_og_image_setting_is_made_absolute(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->og_image = 'images/brand/card.png';
        $settings->save();

        $this->assertStringContainsString(
            '<meta property="og:image" content="'.asset('images/brand/card.png').'">',
            $this->get('/')->getContent()
        );
    }

    /** The two pages anyone actually shares override the site-wide strings. */
    public static function pagesWithOwnPitch(): array
    {
        return [
            'home' => ['/', 'Every name, with the record that proves it.', 'Build a family tree out of evidence, not guesswork.'],
            'subscription' => ['/subscription', 'The tree is free. The DNA work isn&#039;t.', 'Premium adds DNA upload, matching, triangulation and duplicate detection.'],
        ];
    }

    #[DataProvider('pagesWithOwnPitch')]
    public function test_page_carries_its_own_pitch(string $path, string $title, string $description): void
    {
        $site = app(GeneralSettings::class)->site_description;

        $html = $this->get($path)->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="'.$title.'">', $html);
        $this->assertStringContainsString($description, $html);
        $this->assertStringNotContainsString('<meta property="og:description" content="'.e($site).'">', $html);
    }

    public function test_inherited_pages_fall_back_to_the_site_description(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->site_description = 'The house default.';
        $settings->save();

        foreach (['/about', '/privacy', '/terms-and-conditions', '/contact'] as $path) {
            $this->assertStringContainsString(
                '<meta property="og:description" content="The house default.">',
                $this->get($path)->getContent(),
                "{$path} should inherit the site description"
            );
        }
    }
}
