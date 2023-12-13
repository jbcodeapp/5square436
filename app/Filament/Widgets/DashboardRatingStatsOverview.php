<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class DashboardRatingStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval= null;
    protected function getCards(): array
    {
        if(auth()->user()->id == 1){
            $tickets = Ticket::query()
                ->select('id','rating', )
                ->where('status_id', 3)
                ->pluck('rating', 'id')->toArray();
        } else {
            $tickets = Ticket::query()
                ->select('id','rating', )
                ->where('status_id', 3)
                ->where('responsible_id', auth()->user()->id)
                ->pluck('rating', 'id')->toArray();
        }
        $avg = (array_sum($tickets) == 0 || count($tickets)== 0)
            ? 0
            : number_format(array_sum($tickets)/count($tickets),2);

        if($avg<=3){
            $msg = 'Need to Improve Performance !!';
            $color = 'danger';
        } else if($avg <=4){
            $msg = 'Very Good, Do Your Best !!';
            $color = 'warning';
        } else if($avg>4) {
            $msg = 'Your Performance is Excellent. Keep It Up !!';
            $color = 'success';
        }

        return [
            Card::make('Overall Performance', $avg)
                ->description($msg)
//                ->url('https://www.google.com',true)
//                ->descriptionIcon('heroicon-s-trending-down')
//                ->extraAttributes([
//                    'class' => 'cursor-pointer',
//                    'wire:click' => '$emitUp("setStatusFilter", "processed")',
//                ])
                ->color($color),
        ];
    }
}
