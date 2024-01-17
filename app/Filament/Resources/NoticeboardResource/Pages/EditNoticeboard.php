<?php

namespace App\Filament\Resources\NoticeboardResource\Pages;

use App\Filament\Resources\NoticeboardResource;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNoticeboard extends EditRecord
{
    protected static string $resource = NoticeboardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(auth()->user()->can('Delete notice')),
        ];
    }
}
