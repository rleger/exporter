<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Pour sécuriser votre compte, veuillez configurer l\'authentification à deux facteurs.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <p class="text-sm text-red-700">{{ $errors->first('error') }}</p>
        </div>
    @endif

    <div class="space-y-4">
        <!-- TOTP Option -->
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900">Application d'authentification</h3>
                    <p class="text-sm text-gray-500">Google Authenticator, Authy, 1Password...</p>
                </div>
                @if ($hasTotp)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                        Configuré
                    </span>
                @else
                    <a href="{{ route('two-factor.setup.totp') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Configurer
                    </a>
                @endif
            </div>
        </div>

        <!-- Passkey Option -->
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900">Clé de sécurité (Passkey)</h3>
                    <p class="text-sm text-gray-500">Touch ID, Face ID, Windows Hello, clé USB...</p>
                </div>
                @if ($hasPasskeys)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                        Configuré
                    </span>
                @else
                    <button type="button" id="register-passkey" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Configurer
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if ($canComplete)
        <form method="POST" action="{{ route('two-factor.setup.complete') }}" class="mt-6">
            @csrf
            <x-primary-button class="w-full justify-center">
                Terminer la configuration
            </x-primary-button>
        </form>
    @else
        <p class="mt-6 text-center text-sm text-gray-500">
            Configurez au moins une méthode d'authentification pour continuer.
        </p>
    @endif

    @push('scripts')
    <script>
        document.getElementById('register-passkey')?.addEventListener('click', async () => {
            try {
                const optionsResponse = await fetch('/webauthn/register/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const options = await optionsResponse.json();

                options.challenge = base64UrlToUint8Array(options.challenge);
                options.user.id = base64UrlToUint8Array(options.user.id);

                const credential = await navigator.credentials.create({ publicKey: options });

                const response = await fetch('/webauthn/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id: credential.id,
                        rawId: uint8ArrayToBase64Url(new Uint8Array(credential.rawId)),
                        type: credential.type,
                        response: {
                            clientDataJSON: uint8ArrayToBase64Url(new Uint8Array(credential.response.clientDataJSON)),
                            attestationObject: uint8ArrayToBase64Url(new Uint8Array(credential.response.attestationObject))
                        }
                    })
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Passkey registration failed:', error);
                alert('Erreur lors de l\'enregistrement de la clé de sécurité.');
            }
        });

        function base64UrlToUint8Array(base64Url) {
            const padding = '='.repeat((4 - base64Url.length % 4) % 4);
            const base64 = (base64Url + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            return Uint8Array.from(rawData, c => c.charCodeAt(0));
        }

        function uint8ArrayToBase64Url(uint8Array) {
            const base64 = btoa(String.fromCharCode(...uint8Array));
            return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }
    </script>
    @endpush
</x-guest-layout>
