<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Afficher les entrées de l'utilisateur
    Route::get('/entries', [EntryController::class, 'index'])->name('entries.index');

    // Gérer les calendriers
    Route::resource('calendars', CalendarController::class)->except(['show', 'edit', 'update']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
