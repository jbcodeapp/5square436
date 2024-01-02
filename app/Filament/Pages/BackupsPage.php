<?php


namespace App\Filament\Pages;

use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups;

class BackupsPage extends Backups
{
    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->id == 1;
    }

}
