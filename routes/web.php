<?php

use App\Http\Controllers\Web\Registration\RegistrationController;
use Illuminate\Support\Facades\Route;

// Central domain only — these routes must have a domain constraint so they
// aren't overwritten by tenant.php routes that register the same URIs later.
Route::domain(config('app.base_domain', 'localhost'))->group(function () {

    Route::get('/', function () {
        return view('welcome', ['plans' => \App\Models\Plan::active()->get()]);
    });

    // Company registration (public — central domain)
    Route::prefix('register')->name('register.')->group(function (): void {
        Route::get('/', [RegistrationController::class, 'index'])->name('index');
        Route::post('/', [RegistrationController::class, 'store'])->name('store');
        Route::get('/success', [RegistrationController::class, 'success'])->name('success');
    });

    Route::get('/subscription/expired', [RegistrationController::class, 'expired'])->name('billing.expired');

});
