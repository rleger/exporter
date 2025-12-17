<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactorService,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor.setup', [
            'hasTotp' => $user->hasTotp(),
            'hasPasskeys' => $user->hasPasskeys(),
            'canComplete' => $user->hasAnyTwoFactor(),
        ]);
    }

    public function showTotp(Request $request): View
    {
        $user = $request->user();

        $secret = session('two_factor:totp_secret');

        if (!$secret) {
            $secret = $this->twoFactorService->generateSecret();
            session(['two_factor:totp_secret' => $secret]);
        }

        $qrCodeSvg = $this->twoFactorService->generateQrCodeSvg($user, $secret);

        return view('auth.two-factor.setup-totp', [
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    public function confirmTotp(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = session('two_factor:totp_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expirée. Veuillez recommencer.']);
        }

        if (!$this->twoFactorService->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Code invalide. Veuillez réessayer.']);
        }

        $user = $request->user();
        $this->twoFactorService->enableTotp($user, $secret);

        session()->forget('two_factor:totp_secret');

        return redirect()->route('two-factor.setup')
            ->with('status', 'Application d\'authentification configurée avec succès.');
    }

    public function complete(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->hasAnyTwoFactor()) {
            return back()->withErrors(['error' => 'Vous devez configurer au moins une méthode d\'authentification.']);
        }

        $backupCodes = $this->twoFactorService->generateBackupCodes($user);
        $this->twoFactorService->enableTwoFactor($user);

        return redirect()->route('two-factor.backup-codes')
            ->with('backup_codes', $backupCodes);
    }

    public function showBackupCodes(Request $request): View|RedirectResponse
    {
        $backupCodes = session('backup_codes');

        if (!$backupCodes) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor.backup-codes', [
            'backupCodes' => $backupCodes,
        ]);
    }

    public function confirmBackupCodes(): RedirectResponse
    {
        session()->forget('backup_codes');

        return redirect()->route('dashboard')
            ->with('status', 'Authentification à deux facteurs activée avec succès.');
    }
}
