<?php

namespace App\Support;

use Filament\Navigation\NavigationGroup;

final class PanelNavigation
{
    /**
     * @return list<NavigationGroup>
     */
    public static function app(): array
    {
        return [
            self::group('Genealogy', 'heroicon-o-share', collapsed: false),
            self::group('Research & Evidence', 'heroicon-o-book-open'),
            self::group('DNA & Matching', 'heroicon-o-beaker'),
            self::group('Collaboration', 'heroicon-o-user-group'),
            self::group('Data & Media', 'heroicon-o-archive-box'),
            self::group('Account', 'heroicon-o-user-circle'),
            self::group('Billing & plan', 'heroicon-o-credit-card'),
            self::group('Settings', 'heroicon-o-cog-6-tooth'),
        ];
    }

    /**
     * @return list<NavigationGroup>
     */
    public static function admin(): array
    {
        return [
            self::group('Administration', 'heroicon-o-building-office-2', collapsed: false),
            self::group('Genealogy', 'heroicon-o-share', collapsed: false),
            self::group('Research & Evidence', 'heroicon-o-book-open'),
            self::group('DNA & Matching', 'heroicon-o-beaker'),
            self::group('Collaboration', 'heroicon-o-user-group'),
            self::group('Data & Media', 'heroicon-o-archive-box'),
            self::group('Operations', 'heroicon-o-wrench-screwdriver'),
            self::group('Settings', 'heroicon-o-cog-6-tooth'),
            self::group('Account', 'heroicon-o-user-circle'),
        ];
    }

    private static function group(string $label, string $icon, bool $collapsed = true): NavigationGroup
    {
        return NavigationGroup::make($label)
            ->icon($icon)
            ->collapsible()
            ->collapsed($collapsed);
    }
}
