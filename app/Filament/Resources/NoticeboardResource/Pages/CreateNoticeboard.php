<?php

namespace App\Filament\Resources\NoticeboardResource\Pages;

use App\Filament\Resources\NoticeboardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNoticeboard extends CreateRecord
{
    protected static string $resource = NoticeboardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getActions(): array
    {
        return [

        ];
    }
}
