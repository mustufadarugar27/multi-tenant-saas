<?php


use App\Http\Controllers\Web\Registration\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Company registration (public — central domain)
Route::prefix('register')->name('register.')->group(function (): void {
    Route::get('/', [RegistrationController::class, 'index'])->name('index');
    Route::post('/', [RegistrationController::class, 'store'])->name('store');
    Route::get('/success', [RegistrationController::class, 'success'])->name('success');
});

Route::get('/subscription/expired', [RegistrationController::class, 'expired'])->name('billing.expired');
