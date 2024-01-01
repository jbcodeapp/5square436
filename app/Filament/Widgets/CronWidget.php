<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CronWidget extends Widget
{
    protected static ?int $sort = -3;
    protected static string $view = 'filament.widgets.cron-widget';

    public static function canView(): bool
    {
        return auth()->user()->id == 1;
    }
}
