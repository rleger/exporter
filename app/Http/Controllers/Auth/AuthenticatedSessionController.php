<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            // Store user ID for 2FA challenge, log out temporarily
            $userId = $user->id;
            Auth::logout();

            $request->session()->put('two_factor:pending', true);
            $request->session()->put('two_factor:user_id', $userId);
            $request->session()->put('two_factor:remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        // Check if 2FA needs to be set up (mandatory)
        if (!$user->hasTwoFactorEnabled() && config('two-factor.enforcement.mandatory', true)) {
            $request->session()->regenerate();

            return redirect()->route('two-factor.setup');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
