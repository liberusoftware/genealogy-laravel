<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

final class Dashboard extends FilamentDashboard
{
    protected static ?string $title = 'Your family story';

    protected string $view = 'filament.app.pages.dashboard';
}
