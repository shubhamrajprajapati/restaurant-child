<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RestaurantController::class, 'index'])->name('home');
Route::get('/reservation', [ReservationController::class, 'index'])->name('reservation.index');
Route::post('/reservation', [ReservationController::class, 'create'])->name('reservation.create');

Route::get('/super-admin-panel', function () {
    return view('super-admin-panel-api-data');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'update']);
