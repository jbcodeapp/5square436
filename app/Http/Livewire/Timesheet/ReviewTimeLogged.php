<?php

declare(strict_types=1);

namespace App\Http\Livewire\Timesheet;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ReviewTimeLogged extends Component implements HasTable
{
    use InteractsWithTable;

    public Ticket $ticket;

    protected function getFormModel(): Model|string|null
    {
        return $this->ticket;
    }

    protected function getTableQuery(): Builder
    {
        return $this->ticket->hours()->where('is_reviewer', 1)->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')
                ->label(__('Owner'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->user]))
                ->searchable(),

            Tables\Columns\TextColumn::make('start_time')
                ->label(__('Start Time'))
                ->dateTime()
                ->sortable(),

            Tables\Columns\TextColumn::make('end_time')
                ->label(__('End Time'))
                ->dateTime()
                ->sortable(),

            Tables\Columns\TextColumn::make('value_readable')
                ->label(__('Logged Hours'))
                ->sortable()
                ->searchable(),

//            Tables\Columns\TextColumn::make('comment')
//                ->label(__('Comment'))
//                ->limit(50)
//                ->sortable()
//                ->searchable(),
//
//            Tables\Columns\TextColumn::make('activity.name')
//                ->label(__('Activity'))
//                ->sortable(),

//            Tables\Columns\TextColumn::make('ticket.name')
//                ->label(__('Ticket'))
//                ->sortable(),

//            Tables\Columns\TextColumn::make('created_at')
//                ->label(__('Created at'))
//                ->dateTime()
//                ->sortable()
//                ->searchable(),
        ];
    }
}
