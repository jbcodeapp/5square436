<?php

namespace App\Filament\Resources\ProjectResource\Widgets;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class ProjectStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval= null;

    protected function getCards(): array
    {
        return [
            Card::make('All Projects', Project::count())
                ->description('All Projects from database')
                ->color('warning'),
            Card::make('Total In-Progress', Project::where('status_id', 1)->count())
                ->description('In-Progress projects counter')
                ->color('warning'),
            Card::make('Total Completed', Project::where('status_id', 2)->count())
                ->description('Completed projects counter')
                ->color('warning'),
            Card::make('Total Deleted', Project::where('status_id', 3)->count())
                ->description('Deleted projects counter')
                ->color('warning'),
        ];
    }
}
