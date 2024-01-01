<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;

// Share ticket
Route::get('/tickets/share/{ticket:code}', function (Ticket $ticket) {
    return redirect()->to(route('filament.resources.tickets.view', $ticket));
})->name('filament.resources.tickets.share');

Route::post('monthly/cron/run', function () {
    if (auth()->user()->id == 1) {
        $artisan = Artisan::call('monthly:task_copy');
        \Filament\Facades\Filament::notify('success', 'Ticket copy task run successfully', true);
    } else {
        \Filament\Facades\Filament::notify('error', 'You are not authorised to call this event', true);
    }
    return redirect()->back();
})->name('monthly.cron.run');

// Validate an account
Route::get('/validate-account/{user:creation_token}', function (User $user) {
    return view('validate-account', compact('user'));
})
    ->name('validate-account')
    ->middleware([
        'web',
        DispatchServingFilamentEvent::class
    ]);

// Login default redirection
Route::redirect('/login-redirect', '/login')->name('login');

// Road map JSON data
Route::get('road-map/data/{project}', [DataController::class, 'data'])
    ->middleware(['verified', 'auth'])
    ->name('road-map.data');

Route::name('oidc.')
    ->prefix('oidc')
    ->group(function () {
        Route::get('redirect', [OidcAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [OidcAuthController::class, 'callback'])->name('callback');
    });

Route::prefix('attachment')->name('attachment')
    ->group(function () {
        Route::controller(\App\Http\Controllers\AttachmentsController::class)->group(function () {
            Route::get('download/{media}', 'attachment_download')->name('download');
        });
    });
