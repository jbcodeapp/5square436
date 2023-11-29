<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Exports\TicketHoursExport;
use App\Filament\Resources\TicketResource;
use App\Models\Activity;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketComment;
use App\Models\TicketHour;
use App\Models\TicketSubscriber;
use Carbon\Carbon;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use phpDocumentor\Reflection\Types\This;
use Yepsua\Filament\Forms\Components\Rating;

class ViewTicket extends ViewRecord implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.tickets.view';

    public string $tab = 'comments';

    protected $listeners = ['doDeleteComment'];

    public $selectedCommentId;

    public function mount($record): void
    {
        parent::mount($record);
        $this->form->fill();
    }

    protected function getActions(): array
    {
        return [
//            Actions\Action::make('toggleSubscribe')
//                ->label(
//                    fn() => $this->record->subscribers()->where('users.id', auth()->user()->id)->count() ?
//                        __('Unsubscribe')
//                        : __('Subscribe')
//                )
//                ->color(
//                    fn() => $this->record->subscribers()->where('users.id', auth()->user()->id)->count() ?
//                        'danger'
//                        : 'success'
//                )
//                ->icon('heroicon-o-bell')
//                ->button()
//                ->action(function () {
//                    if (
//                        $sub = TicketSubscriber::where('user_id', auth()->user()->id)
//                            ->where('ticket_id', $this->record->id)
//                            ->first()
//                    ) {
//                        $sub->delete();
//                        $this->notify('success', __('You unsubscribed from the ticket'));
//                    } else {
//                        TicketSubscriber::create([
//                            'user_id' => auth()->user()->id,
//                            'ticket_id' => $this->record->id
//                        ]);
//                        $this->notify('success', __('You subscribed to the ticket'));
//                    }
//                    $this->record->refresh();
//                }),

//  ================ Submit Ticket start==================== //
            Actions\Action::make('submitTicket')
                ->label(__('Submit Ticket'))
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->modalWidth('sm')
                ->modalHeading(__('Submit your ticket to Reviewer'))
//                ->modalSubheading(__('Use the following form to add your comments in this ticket.'))
                ->visible(fn() => in_array(auth()->user()->id, [$this->record->responsible_id]))
                ->hidden(fn () => $this->record->status_id != 2)
                ->action(function () {
                    $hourLogged = TicketHour::where('user_id', auth()->user()->id)
                        ->where('ticket_id', $this->record->id)
                        ->latest()->first();
                    if($hourLogged->count() > 0 && $hourLogged->status != 1) {
                        Ticket::where('id', $this->record->id)->update(['status_id'=> 5]);
                        TicketActivity::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'old_status_id' => 2,
                            'new_status_id' => 5,
                        ]);
                        $this->notify('success', __('Your work send to Reviewer'));
                    } else {
                        $this->notify('danger', __('Please start/stop timer first'));
                    }
                    $this->record->refresh();
                })
                ->requiresConfirmation(),
// ================ Submit Ticket end ==================== //

// ================ Review Ticket start ==================== //
            Actions\Action::make('reviewTicket')
                ->label(__('Ticket Review'))
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->modalWidth('sm')
                ->modalHeading(__('Enter your review for this ticket'))
//                ->modalSubheading()
                ->modalSubheading(__('Use the following form to add your worked time in this ticket.'))
                ->modalButton(__('Submit'))
                ->visible(fn() =>
                    in_array(auth()->user()->id, [$this->record->owner_id, $this->record->responsible_id]) &&
                    (in_array($this->record->status_id, [4, 5]))
                )
                ->hidden(fn () => $this->record->owner_id != auth()->user()->id)
                ->form([
                    Rating::make('rating')->required()
                        // ->size(10)
                        // ->min(5)->max(10)
                        // ->icons('heroicon-o-moon', 'heroicon-s-sun')
                        // ->color('orange')

                        // ->clearable()
                        // ->clearIconColor('red')
                        // ->clearIconTooltip('Clear')

                        //->disabled()
                        ->effects(true),

                    Textarea::make('review_comment')
                        ->label(__('Review'))
                        ->required()
                        ->rows(3),
                ])
                ->action(function (Collection $records, array $data): void {

                    if($this->record->hours()->where('is_reviewer', 1)->count()) {
                        Ticket::where('id', $this->record->id)
                            ->update([
                                'rating' => $data['rating'],
                                'review_comment' => $data['review_comment'],
                                'status_id' => 3,
                            ]);
                        TicketActivity::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'old_status_id' => 5,
                            'new_status_id' => 3,
                        ]);
                        $this->notify('success', __('Review rating and comment saved'));
                    } else {
                        $this->notify('danger', __('Please start/stop review timer first'));
                    }
                    $this->record->refresh();
                })
                ->requiresConfirmation(),
// ================ Review Ticket end ==================== //

// ================ Timer start ==================== //
            Actions\Action::make('toggleStartTime')
                ->label(
                    fn() => (
                        $this->record->hours()->count() > 0 &&
                        in_array(auth()->user()->id, [$this->record->owner_id, $this->record->responsible_id]) &&
                        $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first() != null &&
                        $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first()->status == 1
                    )
                        ? __('End Time')
                        : __('Start Time')
                )
                ->color(
                    fn() => ($this->record->hours()->count() > 0 && $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first() != null &&
                        $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first()->status == 1
                    )
                        ? __('danger')
                        : __('success')
                )
                ->visible(fn() =>
                in_array(auth()->user()->id, [$this->record->responsible_id]) &&
                (in_array($this->record->status_id, [1, 2]))
                )
                ->icon('heroicon-o-clock')
                ->button()
                ->action(function () {
                    $timer = TicketHour::where('user_id', auth()->user()->id)
                        ->where('ticket_id', $this->record->id)
                        ->latest()->first();
                    if ($timer != null && $timer->status == 1) {
                            $start = Carbon::parse($timer->start_time);
                            $end = Carbon::now();
                            $diff = $start->diffInSeconds($end);
                            $timer->update([
                                'value' => $diff,
                                'end_time' => now(),
                                'status' => 0,
                            ]);
                            $this->notify('success', __('Your timer ended'));
                    } else {
                        Ticket::where('id', $this->record->id)->update(['status_id'=> 2]);
                        TicketHour::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'status' => true,
                            'start_time' => now()
                        ]);
                        TicketActivity::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'old_status_id' => 1,
                            'new_status_id' => 2,
                        ]);
                        $this->notify('success', __('Your timer started'));
                    }
                    $this->record->refresh();
                }),
// ================ Timer end ==================== //

// ================ Review Timer start ==================== //
            Actions\Action::make('toggleReviewStartTime')
                ->label(
                    fn() => (
                        $this->record->hours()->count() > 0 &&
                        in_array(auth()->user()->id, [$this->record->owner_id, $this->record->responsible_id]) &&
                        $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first() != null &&
                        $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first()->status == 1
                    )
                        ? __('End Review Time')
                        : __('Start Review Time')
                )
                ->color(
                    fn() => ($this->record->hours()->count() > 0 && $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first() != null &&
                        $this->record->hours()
                            ->where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)->latest()->first()->status == 1
                    )
                        ? __('danger')
                        : __('success')
                )
                ->visible(fn() =>
                    in_array(auth()->user()->id, [$this->record->owner_id]) &&
                    (in_array($this->record->status_id, [ 4, 5]))
                )
                ->icon('heroicon-o-clock')
                ->button()
                ->action(function () {
                    $timer = TicketHour::where('user_id', auth()->user()->id)
                        ->where('ticket_id', $this->record->id)
                        ->where('is_reviewer', true)
                        ->latest()->first();
                    if ($timer != null && $timer->status == 1) {
                        // review end time action
                        $start = Carbon::parse($timer->start_time);
                        $end = Carbon::now();
                        $diff = $start->diffInSeconds($end);
                        $timer->update([
                            'value' => $diff,
                            'end_time' => now(),
                            'status' => 0,
                        ]);
                        $this->notify('success', __('Your Review timer ended'));
                    } else {
                        // review start time action
                        // Ticket::where('id', $this->record->id)->update(['status_id'=> 2]);
                        TicketHour::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'status' => true,
                            'is_reviewer' => true,
                            'start_time' => now()
                        ]);
                        TicketActivity::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'old_status_id' => 5,
                            'new_status_id' => 5,
                        ]);
                        $this->notify('success', __('Your Review timer started'));
                    }
                    $this->record->refresh();
                }),
// ================ Timer end ==================== //

//  ================ Ticket Back to In-Progress start ==================== //
            Actions\Action::make('submitReviewTicket')
                ->label(__('Submit Ticket to Employee'))
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->modalWidth('sm')
                ->modalHeading(__('Submit ticket to Employee'))
//                ->modalSubheading(__('Use the following form to add your comments in this ticket.'))
                ->visible(fn() => in_array(auth()->user()->id, [$this->record->owner_id]) && (in_array($this->record->status_id, [3, 4, 5])))
                ->hidden(fn () => $this->record->status_id != 5)
                ->action(function () {
                    $hourLogged = TicketHour::where('user_id', auth()->user()->id)
                        ->where('ticket_id', $this->record->id)
                        ->where('is_reviewer', 1)
                        ->latest()->first();
                    if($hourLogged != null && $hourLogged->count() > 0 && $hourLogged->status != 1) {
                        Ticket::where('id', $this->record->id)->update(['status_id'=> 2]);
                        TicketActivity::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id,
                            'old_status_id' => 5,
                            'new_status_id' => 2,
                        ]);
                        $this->notify('success', __('Ticket submit to employee'));
                    } else {
                        $this->notify('danger', __('Please start/stop review timer first'));
                    }
                    $this->record->refresh();
                })
                ->requiresConfirmation(),
// ================ Ticket Back to In-Progress end ==================== //

//            Actions\Action::make('share')
//                ->label(__('Share'))
//                ->color('secondary')
//                ->button()
//                ->icon('heroicon-o-share')
//                ->action(fn() => $this->dispatchBrowserEvent('shareTicket', [
//                    'url' => route('filament.resources.tickets.share', $this->record->code)
//                ])),

            Actions\EditAction::make(),

//            Actions\Action::make('logHours')
//                ->label(__('Log time'))
//                ->icon('heroicon-o-clock')
//                ->color('warning')
//                ->modalWidth('sm')
//                ->modalHeading(__('Log worked time'))
//                ->modalSubheading(__('Use the following form to add your worked time in this ticket.'))
//                ->modalButton(__('Log'))
//                ->visible(fn() => in_array(
//                    auth()->user()->id,
//                    [$this->record->owner_id, $this->record->responsible_id]
//                ))
//                ->form([
//                    TextInput::make('time')
//                        ->label(__('Time to log'))
//                        ->numeric()
//                        ->required(),
//                    Select::make('activity_id')
//                        ->label(__('Activity'))
//                        ->searchable()
//                        ->reactive()
//                        ->options(function ($get, $set) {
//                            return Activity::all()->pluck('name', 'id')->toArray();
//                        }),
//                    Textarea::make('comment')
//                        ->label(__('Comment'))
//                        ->rows(3),
//                ])
//                ->action(function (Collection $records, array $data): void {
//                    $value = $data['time'];
//                    $comment = $data['comment'];
//                    TicketHour::create([
//                        'ticket_id' => $this->record->id,
////                        'activity_id' => $data['activity_id'],
//                        'user_id' => auth()->user()->id,
//                        'value' => $value,
//                        'comment' => $comment
//                    ]);
//                    $this->record->refresh();
//                    $this->notify('success', __('Time logged into ticket'));
//                }),

            Actions\ActionGroup::make([
                Actions\Action::make('exportLogHours')
                    ->label(__('Export time logged'))
                    ->icon('heroicon-o-document-download')
                    ->color('warning')
                    ->visible(
                        fn() => $this->record->watchers->where('id', auth()->user()->id)->count()
                            && $this->record->hours()->count()
                    )
                    ->action(fn() => Excel::download(
                        new TicketHoursExport($this->record),
                        'time_' . str_replace('-', '_', $this->record->code) . '.csv',
                        \Maatwebsite\Excel\Excel::CSV,
                        ['Content-Type' => 'text/csv']
                    )),
            ])
                ->visible(fn() => (in_array(
                        auth()->user()->id,
                        [$this->record->owner_id, $this->record->responsible_id]
                    )) || (
                        $this->record->watchers->where('id', auth()->user()->id)->count()
                        && $this->record->hours()->count()
                    ))
                ->color('secondary'),
        ];
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    protected function getFormSchema(): array
    {
        return [
            RichEditor::make('comment')
                ->disableLabel()
                ->placeholder(__('Type a new comment'))
                ->required()
        ];
    }

    public function submitComment(): void
    {
        $data = $this->form->getState();
        if ($this->selectedCommentId) {
            TicketComment::where('id', $this->selectedCommentId)
                ->update([
                    'content' => $data['comment']
                ]);
        } else {
            TicketComment::create([
                'user_id' => auth()->user()->id,
                'ticket_id' => $this->record->id,
                'content' => $data['comment']
            ]);
        }
        $this->record->refresh();
        $this->cancelEditComment();
        $this->notify('success', __('Comment saved'));
    }

    public function isAdministrator(): bool
    {
        return $this->record
                ->project
                ->users()
                ->where('users.id', auth()->user()->id)
                ->where('role', 'administrator')
                ->count() != 0;
    }

    public function editComment(int $commentId): void
    {
        $this->form->fill([
            'comment' => $this->record->comments->where('id', $commentId)->first()?->content
        ]);
        $this->selectedCommentId = $commentId;
    }

    public function deleteComment(int $commentId): void
    {
        Notification::make()
            ->warning()
            ->title(__('Delete confirmation'))
            ->body(__('Are you sure you want to delete this comment?'))
            ->actions([
                Action::make('confirm')
                    ->label(__('Confirm'))
                    ->color('danger')
                    ->button()
                    ->close()
                    ->emit('doDeleteComment', compact('commentId')),
                Action::make('cancel')
                    ->label(__('Cancel'))
                    ->close()
            ])
            ->persistent()
            ->send();
    }

    public function doDeleteComment(int $commentId): void
    {
        TicketComment::where('id', $commentId)->delete();
        $this->record->refresh();
        $this->notify('success', __('Comment deleted'));
    }

    public function cancelEditComment(): void
    {
        $this->form->fill();
        $this->selectedCommentId = null;
    }
}
