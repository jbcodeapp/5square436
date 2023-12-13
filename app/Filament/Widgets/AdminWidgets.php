<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class AdminWidgets extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Tickets', Ticket::count()),
        ];
    }

//    public static function canView(): bool
//    {
//        return auth()->user()->id==1;
//    }
}
