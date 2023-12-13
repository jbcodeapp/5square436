<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Timesheet\ActivitiesReport;
use App\Filament\Widgets\Timesheet\MonthlyReport;
use App\Filament\Widgets\Timesheet\WeeklyReport;
use Filament\Pages\Page;

class TimesheetDashboard extends Page
{
    protected static ?string $slug = 'timesheet-dashboard';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament::pages.dashboard';
//    protected static bool $shouldRegisterNavigation = false;


    protected function getColumns(): int | array
    {
        return 6;
    }

    protected static function getNavigationLabel(): string
    {
        return __('My TimeSheet');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Timesheet');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return false; //auth()->user()->can('View timesheet dashboard');
    }

    protected function getWidgets(): array
    {
        return [
            WeeklyReport::class,
            MonthlyReport::class,
//            ActivitiesReport::class,
        ];
    }
}
