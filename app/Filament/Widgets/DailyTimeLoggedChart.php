<?php

namespace App\Filament\Widgets;

use App\Models\TicketHour;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class DailyTimeLoggedChart extends LineChartWidget
{
    protected static ?string $heading = 'Chart';

//    public ?string $filter = 'today';

    public static function canView(): bool
    {
        return auth()->user()->id == 1;
    }

    protected function getFilters(): ?array
    {
        return [
            'current_week' => 'Current Week',
            'last_week' => 'Last Week',
            'current_month' => 'Current Month',
            'last_month' => 'Last Month',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
//        dd($activeFilter);
//        if($activeFilter == 'current_week') {
        $from = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
        $to = Carbon::now()->endOfWeek()->format('Y-m-d H:i');
//        }

        if($activeFilter == 'last_week') {
            $from = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d H:i:s');
            $to   = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d H:i:s');
        }

        if($activeFilter == 'current_month') {
            $from = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
            $to = Carbon::now()->endOfMonth()->format('Y-m-d H:i');
        }

        if($activeFilter == 'last_month') {
            $from = Carbon::now()->startOfMonth()->subMonth()->startOfMonth()->format('Y-m-d H:i:s');
            $to = Carbon::now()->startOfMonth()->subMonth()->endOfMonth()->format('Y-m-d H:i');
        }

        $query = TicketHour::query()
            ->select(
                DB::raw('DAY(created_at) As date, SUM(value)/3600 AS total_time')
            );
        $query->whereBetween('start_time', [$from, $to]);
        $query->where('user_id', 3);
        $query->orderBy('date');
        $query->groupBy(DB::raw('DAY(created_at)'));

        $data = $label = [];
        foreach ($query->get() as $day) {
            $label[] = $day->date;
            $data[] = number_format($day->total_time,2,'.');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Daily Working Hours',
                    'data' => $data,
                ],
            ],
            'labels' => $label,
        ];
    }

//    protected function getFilters(): ?array
//    {
//        return [
//            'today' => 'Today',
//            'week' => 'Last week',
//            'month' => 'Last month',
//            'year' => 'This year',
//        ];
//    }
}
