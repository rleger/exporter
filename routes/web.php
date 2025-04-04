<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Afficher les entrées de l'utilisateur
    Route::get('/entries', [EntryController::class, 'index'])->name('entries.index');

    Route::get('/recap', [RecapController::class, 'index'])->name('recap.index');

    Route::post('/import-calendars', [DashboardController::class, 'importCalendars'])->name('import.calendars');

    Route::get('/entries/{entry}/appointments', [AppointmentController::class, 'show'])->name('appointments.show');

    // Gérer les calendriers
    Route::resource('calendars', CalendarController::class)->except(['show', 'edit', 'update']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
