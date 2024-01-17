<?php

namespace App\Filament\Resources\NoticeboardResource\Widgets;

use Carbon\Carbon;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class Noticeboard extends BaseWidget
{
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];
    protected function getTableRecordsPerPage(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {
        return \App\Models\Noticeboard::query()
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('notice')
                ->formatStateUsing(fn($record) => new HtmlString('<span style="color: red;">' . $record->notice . '</span>')),
        ];
    }
}
