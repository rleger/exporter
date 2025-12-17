<?php

namespace App\Services;

use App\Models\TwoFactorAuthentication;
use App\Models\TwoFactorBackupCode;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQrCodeSvg(User $user, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code, config('two-factor.totp.window', 1));
    }

    public function verifyForUser(User $user, string $code): bool
    {
        $twoFactor = $user->twoFactorAuthentication;

        if (!$twoFactor || !$twoFactor->enabled) {
            return false;
        }

        return $this->verify($twoFactor->secret, $code);
    }

    /**
     * @return array<int, string>
     */
    public function generateBackupCodes(User $user, int $count = 10): array
    {
        $user->backupCodes()->delete();

        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4));
            $codes[] = $code;

            TwoFactorBackupCode::create([
                'user_id' => $user->id,
                'code' => Hash::make($code),
            ]);
        }

        return $codes;
    }

    public function verifyBackupCode(User $user, string $code): bool
    {
        $normalizedCode = strtoupper(str_replace(' ', '', $code));

        $backupCodes = $user->availableBackupCodes()->get();

        foreach ($backupCodes as $backupCode) {
            if (Hash::check($normalizedCode, $backupCode->code)) {
                $backupCode->markAsUsed();

                return true;
            }
        }

        return false;
    }

    public function enableTotp(User $user, string $secret): TwoFactorAuthentication
    {
        return TwoFactorAuthentication::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $secret,
                'enabled' => true,
                'confirmed_at' => now(),
            ]
        );
    }

    public function disableTotp(User $user): void
    {
        $user->twoFactorAuthentication?->update([
            'enabled' => false,
        ]);
    }

    public function enableTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ]);

        $this->disableTotp($user);
        $user->webAuthnCredentials()->delete();
        $user->backupCodes()->delete();
    }
}
