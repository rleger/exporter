<?php

use App\Models\TwoFactorAuthentication;
use App\Models\TwoFactorBackupCode;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| 2FA Setup Flow Tests
|--------------------------------------------------------------------------
*/

test('user without 2FA is redirected to setup after login', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.setup'));
    $this->assertAuthenticatedAs($user);
});

test('2FA setup page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('two-factor.setup'));

    $response->assertStatus(200);
});

test('user can setup TOTP', function () {
    $user = User::factory()->create();
    $twoFactorService = app(TwoFactorService::class);
    $secret = $twoFactorService->generateSecret();

    // Store secret in session (simulating the setup flow)
    $response = $this->actingAs($user)
        ->withSession(['two_factor:totp_secret' => $secret])
        ->post(route('two-factor.setup.totp.confirm'), [
            'code' => app(PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret),
        ]);

    // TOTP confirmation redirects back to setup page
    $response->assertRedirect(route('two-factor.setup'));

    $user->refresh();
    expect($user->twoFactorAuthentication)->not->toBeNull();
    expect($user->twoFactorAuthentication->enabled)->toBeTrue();
});

test('user can complete 2FA setup and see backup codes', function () {
    $user = User::factory()->create();
    $twoFactorService = app(TwoFactorService::class);

    // First, enable TOTP for the user
    $secret = $twoFactorService->generateSecret();
    $twoFactorService->enableTotp($user, $secret);

    // Now complete the 2FA setup
    $response = $this->actingAs($user)
        ->post(route('two-factor.setup.complete'));

    $response->assertRedirect(route('two-factor.backup-codes'));
    $response->assertSessionHas('backup_codes');

    $user->refresh();
    expect($user->hasTwoFactorEnabled())->toBeTrue();
    expect($user->backupCodes()->count())->toBe(10);
});

/*
|--------------------------------------------------------------------------
| 2FA Challenge Flow Tests
|--------------------------------------------------------------------------
*/

test('user with 2FA is redirected to challenge after login', function () {
    $user = User::factory()->withTwoFactor()->create();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => app(TwoFactorService::class)->generateSecret(),
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.challenge'));
    $this->assertGuest();
    expect(session('two_factor:pending'))->toBeTrue();
    expect(session('two_factor:user_id'))->toBe($user->id);
});

test('2FA challenge page can be rendered when pending', function () {
    $user = User::factory()->withTwoFactor()->create();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => app(TwoFactorService::class)->generateSecret(),
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $response = $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->get(route('two-factor.challenge'));

    $response->assertStatus(200);
});

test('2FA challenge page redirects to login when not pending', function () {
    $response = $this->get(route('two-factor.challenge'));

    $response->assertRedirect(route('login'));
});

test('user can verify with valid TOTP code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $secret = app(TwoFactorService::class)->generateSecret();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $validCode = app(PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret);

    $response = $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->post(route('two-factor.challenge.totp'), [
        'code' => $validCode,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect(session('two_factor:pending'))->toBeNull();
});

test('user cannot verify with invalid TOTP code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $secret = app(TwoFactorService::class)->generateSecret();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $response = $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->post(route('two-factor.challenge.totp'), [
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

test('user can verify with valid backup code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $secret = app(TwoFactorService::class)->generateSecret();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $backupCode = 'ABCD-EFGH';
    TwoFactorBackupCode::create([
        'user_id' => $user->id,
        'code' => Hash::make($backupCode),
    ]);

    $response = $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->post(route('two-factor.challenge.backup'), [
        'code' => $backupCode,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('backup code is marked as used after successful verification', function () {
    $user = User::factory()->withTwoFactor()->create();
    $secret = app(TwoFactorService::class)->generateSecret();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $backupCode = 'ABCD-EFGH';
    TwoFactorBackupCode::create([
        'user_id' => $user->id,
        'code' => Hash::make($backupCode),
    ]);

    $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->post(route('two-factor.challenge.backup'), [
        'code' => $backupCode,
    ]);

    $backupCodeRecord = TwoFactorBackupCode::where('user_id', $user->id)->first();
    expect($backupCodeRecord->used_at)->not->toBeNull();
});

test('user cannot verify with invalid backup code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $secret = app(TwoFactorService::class)->generateSecret();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $response = $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->post(route('two-factor.challenge.backup'), [
        'code' => 'INVALID-CODE',
    ]);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

test('user cannot reuse backup code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $secret = app(TwoFactorService::class)->generateSecret();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $backupCode = 'ABCD-EFGH';
    TwoFactorBackupCode::create([
        'user_id' => $user->id,
        'code' => Hash::make($backupCode),
        'used_at' => now(),
    ]);

    $response = $this->withSession([
        'two_factor:pending' => true,
        'two_factor:user_id' => $user->id,
    ])->post(route('two-factor.challenge.backup'), [
        'code' => $backupCode,
    ]);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

/*
|--------------------------------------------------------------------------
| 2FA Protected Routes Tests
|--------------------------------------------------------------------------
*/

test('user with 2FA can access protected routes', function () {
    $user = User::factory()->withTwoFactor()->create();
    TwoFactorAuthentication::create([
        'user_id' => $user->id,
        'secret' => app(TwoFactorService::class)->generateSecret(),
        'enabled' => true,
        'confirmed_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
});

test('user without 2FA is redirected to setup from protected routes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('two-factor.setup'));
});

/*
|--------------------------------------------------------------------------
| TwoFactorService Tests
|--------------------------------------------------------------------------
*/

test('TwoFactorService generates valid secret', function () {
    $service = app(TwoFactorService::class);
    $secret = $service->generateSecret();

    expect($secret)->toBeString();
    expect(strlen($secret))->toBeGreaterThanOrEqual(16);
});

test('TwoFactorService verifies valid TOTP code', function () {
    $service = app(TwoFactorService::class);
    $secret = $service->generateSecret();
    $code = app(PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret);

    expect($service->verify($secret, $code))->toBeTrue();
});

test('TwoFactorService rejects invalid TOTP code', function () {
    $service = app(TwoFactorService::class);
    $secret = $service->generateSecret();

    expect($service->verify($secret, '000000'))->toBeFalse();
});

test('TwoFactorService generates backup codes', function () {
    $user = User::factory()->withTwoFactor()->create();
    $service = app(TwoFactorService::class);

    $codes = $service->generateBackupCodes($user);

    expect($codes)->toBeArray();
    expect(count($codes))->toBe(10);
    expect($user->backupCodes()->count())->toBe(10);
});

test('TwoFactorService verifies valid backup code', function () {
    $user = User::factory()->withTwoFactor()->create();
    $service = app(TwoFactorService::class);

    $codes = $service->generateBackupCodes($user);
    $firstCode = $codes[0];

    expect($service->verifyBackupCode($user, $firstCode))->toBeTrue();
});
