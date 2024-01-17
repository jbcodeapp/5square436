<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticeboardResource\Pages;
use App\Models\Noticeboard;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;

class NoticeboardResource extends Resource
{
    protected static ?string $model = Noticeboard::class;

    protected static ?string $slug = 'noticeboards';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 6;

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('List notice');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('notice')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('notice'),
                ])
//            ->filters()
            ->actions([
                ViewAction::make(),
                EditAction::make()->visible(auth()->user()->can('Update notice')),
                DeleteAction::make()->visible(auth()->user()->can('Delete notice')),

            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(auth()->user()->can('Delete notice')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNoticeboards::route('/'),
            'create' => Pages\CreateNoticeboard::route('/create'),
            'edit' => Pages\EditNoticeboard::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
