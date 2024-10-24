<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $apiData = app('api.data');
    $data = Restaurant::where(['domain' => $apiData['domain']])?->first()?->toArray();
    // Convert other_details to array if it's a JSON
    if (isset($data['other_details']) && !is_array($data['other_details'])) {
        $data['other_details'] = json_decode($data['other_details'], true);
    }

    return view('home', compact('apiData','data'));
});

Route::get('/super-admin-panel', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'update']);
