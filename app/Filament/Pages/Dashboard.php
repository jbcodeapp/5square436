<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TicketResource\Widgets\DashboardTicketStatsOverview;
use App\Filament\Widgets\CronWidget;
use App\Filament\Widgets\DashboardRatingStatsOverview;
use App\Filament\Widgets\FavoriteProjects;
use App\Filament\Widgets\LatestActivities;
use App\Filament\Widgets\LatestComments;
use App\Filament\Widgets\LatestProjects;
use App\Filament\Widgets\LatestTickets;
use App\Filament\Widgets\TicketsByPriority;
use App\Filament\Widgets\TicketsByType;
use App\Filament\Widgets\TicketTimeLogged;
use App\Filament\Widgets\UserTimeLogged;
use Filament\Pages\Dashboard as BasePage;
use Filament\Widgets\AccountWidget;

class Dashboard extends BasePage
{
    protected static bool $shouldRegisterNavigation = false;

    protected function getColumns(): int | array
    {
        return 2;
    }

    protected function getWidgets(): array
    {
        return [
            AccountWidget::class,
            CronWidget::class,
            DashboardRatingStatsOverview::class,
            DashboardTicketStatsOverview::class,
            LatestTickets::class,
//            FavoriteProjects::class,
            LatestActivities::class,
            LatestComments::class,
            LatestProjects::class,
//            TicketsByPriority::class,
//            TicketsByType::class,
//            TicketTimeLogged::class,
//            UserTimeLogged::class
        ];
    }
}
