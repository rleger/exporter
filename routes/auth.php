<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication Routes
|--------------------------------------------------------------------------
*/

// 2FA Setup (requires auth, allows without 2FA enabled)
Route::middleware(['auth'])->prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/setup', [TwoFactorSetupController::class, 'show'])
        ->name('setup');

    Route::get('/setup/totp', [TwoFactorSetupController::class, 'showTotp'])
        ->name('setup.totp');

    Route::post('/setup/totp/confirm', [TwoFactorSetupController::class, 'confirmTotp'])
        ->name('setup.totp.confirm');

    Route::post('/setup/complete', [TwoFactorSetupController::class, 'complete'])
        ->name('setup.complete');

    Route::get('/backup-codes', [TwoFactorSetupController::class, 'showBackupCodes'])
        ->name('backup-codes');

    Route::post('/backup-codes/confirm', [TwoFactorSetupController::class, 'confirmBackupCodes'])
        ->name('backup-codes.confirm');
});

// 2FA Challenge (for users with pending 2FA verification)
Route::prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('challenge');

    Route::post('/challenge/totp', [TwoFactorChallengeController::class, 'verifyTotp'])
        ->name('challenge.totp')
        ->middleware('throttle:5,1');

    Route::post('/challenge/passkey', [TwoFactorChallengeController::class, 'verifyPasskey'])
        ->name('challenge.passkey')
        ->middleware('throttle:10,1');

    Route::post('/challenge/backup', [TwoFactorChallengeController::class, 'verifyBackup'])
        ->name('challenge.backup')
        ->middleware('throttle:3,1');

    // Passkey assertion endpoints for 2FA challenge (user not authenticated)
    Route::post('/challenge/passkey/options', [TwoFactorChallengeController::class, 'passkeyOptions'])
        ->name('challenge.passkey.options');

    Route::post('/challenge/passkey/verify', [TwoFactorChallengeController::class, 'verifyPasskeyAssertion'])
        ->name('challenge.passkey.verify');
});
