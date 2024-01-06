<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\TicketHour;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Facades\DB;

class UserTimeLogged extends BarChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px';
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];
    protected static ?string $pollingInterval= null;
    public ?string $filter = 'current_week';

    public static function canView(): bool
    {
        return auth()->user()->id == 1;
    }

    protected function getHeading(): string
    {
        return __('Time logged by users');
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

//        $role = auth()->user()->roles->pluck('id');
//        $query = User::query();
//        if($role[0] != 1) {
//            $query->whereIn('id', [auth()->user()->id]);
//        }
//        $query->has('hours');
//        $query->limit(10);

//        dd($query->get());

        $query = TicketHour::query()
            ->select(
                DB::raw('DAY(created_at) As date, SUM(value)/3600 AS total_time')
            );
            $query->whereBetween('start_time', [$from, $to]);
            $query->where('user_id', 4);
            $query->orderBy('date');
            $query->groupBy(DB::raw('DAY(created_at)'));

        $data = $label = [];
        foreach ($query->get() as $day) {
            $label[] = $day->date;
            $data[] = number_format($day->total_time,2,'.');
        }
//        dd( count($data), $label);

        return [
            'datasets' => [
                [
                    'label' => __('Working Days ('.count($label).') | Total Working Hours('.array_sum($data).')'),
//                    'data' => $query->get()->pluck('totalLoggedInHours')->toArray(),
//                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
//                    'data' => [0, 5.6, 5, 2, 7, 6.4, 4, 3, 2, 7, 8, 7],
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, .6)'
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, .8)'
                    ],
                ],
            ],
//            'labels' => $query->get()->pluck('name')->toArray(),
//            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'labels' => $label,
        ];
    }
}
