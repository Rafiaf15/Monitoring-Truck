<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TruckController;

Route::get('/', fn () => redirect()->route('trucks.index'));

Route::resource('trucks', TruckController::class);

// Inline update – hanya pipe & status dari modal dashboard
Route::post('trucks/{truck}/update-load', [TruckController::class, 'updateLoad'])
    ->name('trucks.updateLoad');
