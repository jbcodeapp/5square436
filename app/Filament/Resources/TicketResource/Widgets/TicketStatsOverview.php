<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class TicketStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval= null;
    protected function getCards(): array
    {
//        $tickets = Ticket::query()
//            ->groupBy('status_id')
//            ->select('status_id', DB::raw('count(*) as total'))
//            ->pluck('status_id', 'total')->toArray();
//        dd($tickets, array_filter);

        if(auth()->user()->id == 1){
            $total = Ticket::count();
            $todo = Ticket::query()->where('status_id', 1)->count();
            $inProgress = Ticket::query()->where('status_id', 2)->count();
            $done = Ticket::query()->where('status_id', 3)->count();
            $under_review = Ticket::query()->where('status_id', 5)->count();
            $archived = Ticket::query()->where('status_id', 4)->count();
            $overDue = Ticket::query()
                ->whereIn('status_id',[1,2])
                ->where('target_date', '<', Carbon::today())
                ->count();
        } else {
            $id = auth()->user()->id;
            $total = Ticket::where('responsible_id', $id)->count();
            $todo = Ticket::query()->where('responsible_id', $id)->where('status_id', 1)->count();
            $inProgress = Ticket::query()->where('responsible_id', $id)->where('status_id', 2)->count();
            $done = Ticket::query()->where('responsible_id', $id)->where('status_id', 3)->count();
            $under_review = Ticket::query()->where('responsible_id', $id)->where('status_id', 5)->count();
            $archived = Ticket::query()->where('responsible_id', $id)->where('status_id', 4)->count();
            $overDue = Ticket::query()->where('responsible_id', $id)->whereIn('status_id',[1,2])->where('target_date', '>', Carbon::today())->count();
        }

        return [
            Card::make('All Tickets', $total)
                ->description('All tickets from database')
                ->color('warning'),
            Card::make('To Do', $todo)
                ->description('To Do tickets counter')
                ->color('warning'),
            Card::make('In-Progress', $inProgress)
                ->description('In Progress tickets counter')
                ->color('warning'),
            Card::make('Done', $done)
                ->description('Completed tickets counter')
                ->color('warning'),
            Card::make('Under-Review', $under_review)
                ->description('Under Review tickets counter')
                ->color('warning'),
            Card::make('OverDue', $overDue)
                ->description('Over Due tickets counter')
//                ->url('https://www.google.com',true)
//                ->descriptionIcon('heroicon-s-trending-down')
//                ->extraAttributes([
//                    'class' => 'cursor-pointer',
//                    'wire:click' => '$emitUp("setStatusFilter", "processed")',
//                ])
                ->color('danger'),
//            Card::make('Deleted', $archived)
//                ->description('Deleted tickets counter')
//                ->color('warning'),
        ];
    }
}
