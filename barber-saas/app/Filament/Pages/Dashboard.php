<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\RecentAgendamentos;
use App\Filament\Widgets\BarbeariaOverview;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            BarbeariaOverview::class,
            RecentAgendamentos::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}