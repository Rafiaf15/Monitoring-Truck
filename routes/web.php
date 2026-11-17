<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TruckController;

Route::get('/', fn () => redirect()->route('trucks.index'));

// Log aktivitas & fitur hapus log jika salah
Route::get('trucks/logs', [TruckController::class, 'logs'])->name('trucks.logs');
Route::delete('trucks/logs/{log}', [TruckController::class, 'destroyLog'])->name('trucks.logs.destroy');

Route::resource('trucks', TruckController::class);

// Inline update – pipe & status
Route::post('trucks/{truck}/update-load', [TruckController::class, 'updateLoad'])
    ->name('trucks.updateLoad');
