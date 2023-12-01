<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\User;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TimesheetExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'timesheet-export';
    protected static ?string $navigationLabel = "Report";
    protected static ?string $title = "Report";

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.timesheet-export';
    protected static bool $shouldRegisterNavigation = true;


    protected static function getNavigationGroup(): ?string
    {
        return __('Timesheet');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()->schema([
                Grid::make()
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->required()
                            ->reactive()
                            ->label('Star date'),
                        DatePicker::make('end_date')
                            ->required()
                            ->reactive()
                            ->label('End date'),
                        Select::make('user_id')
                            ->label(__('Employee'))
                            ->searchable()
                            ->options(function () {
                                if (auth()->user()->roles[0]->id == 1) {
                                    return User::all()->pluck('name', 'id')->toArray();
                                } else {
                                    return User::where('id', auth()->user()->id)
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                            }),
                        Select::make('project_id')
                            ->label(__('Project'))
                            ->searchable()
                            ->options(Project::all()->pluck('name', 'id')->toArray()),
                    ])
            ])
        ];
    }

    public function create(): BinaryFileResponse
    {
        $data = $this->form->getState();
        $fileName = 'report';
        if($data['user_id'] != null) {
            $username = User::where('id', $data['user_id'])->first()->name;
            $fileName .= "_".str_replace(' ', '_', $username);;
        }

        if($data['project_id'] != null) {
            $projectname = Project::where('id', $data['project_id'])->first()->name;
            $fileName .= "_".str_replace(' ', '_', $projectname);;
        }

        $fileName .= "_".date("d-m-Y", strtotime($data['start_date']))."_to_".date("d-m-Y", strtotime($data['end_date'])).'.csv';

        return Excel::download(
            new \App\Exports\TimesheetExport($data),
            $fileName,
            \Maatwebsite\Excel\Excel::CSV,
            ['Content-Type' => 'text/csv']
        );
    }
}
