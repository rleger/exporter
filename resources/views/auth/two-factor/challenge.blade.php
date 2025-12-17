<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Veuillez confirmer votre identité avec votre authentification à deux facteurs.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('warning'))
        <div class="mb-4 rounded-md bg-yellow-50 p-4">
            <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
        </div>
    @endif

    <div x-data="{ method: '{{ $hasTotp ? 'totp' : 'backup' }}' }">
        <!-- Method Selector -->
        <div class="mb-4 flex space-x-2">
            @if ($hasTotp)
                <button type="button"
                    @click="method = 'totp'"
                    :class="method === 'totp' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-gray-700 border-gray-300'"
                    class="flex-1 rounded-md border px-3 py-2 text-sm font-medium">
                    Application
                </button>
            @endif
            @if ($hasPasskeys)
                <button type="button"
                    @click="method = 'passkey'"
                    :class="method === 'passkey' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-gray-700 border-gray-300'"
                    class="flex-1 rounded-md border px-3 py-2 text-sm font-medium">
                    Passkey
                </button>
            @endif
            <button type="button"
                @click="method = 'backup'"
                :class="method === 'backup' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white text-gray-700 border-gray-300'"
                class="flex-1 rounded-md border px-3 py-2 text-sm font-medium">
                Code de secours
            </button>
        </div>

        <!-- TOTP Form -->
        @if ($hasTotp)
            <form method="POST" action="{{ route('two-factor.challenge.totp') }}" x-show="method === 'totp'" x-cloak>
                @csrf

                <div>
                    <x-input-label for="totp_code" :value="__('Code à 6 chiffres')" />
                    <x-text-input
                        id="totp_code"
                        class="mt-1 block w-full text-center tracking-widest"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        required
                        autofocus
                    />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-primary-button class="w-full justify-center">
                        Vérifier
                    </x-primary-button>
                </div>
            </form>
        @endif

        <!-- Passkey Form -->
        @if ($hasPasskeys)
            <div x-show="method === 'passkey'" x-cloak>
                <p class="mb-4 text-center text-sm text-gray-600">
                    Utilisez votre clé de sécurité ou authentification biométrique.
                </p>

                <button type="button"
                    id="verify-passkey"
                    class="flex w-full items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Utiliser ma clé de sécurité
                </button>

                <x-input-error :messages="$errors->get('passkey')" class="mt-2" />
            </div>
        @endif

        <!-- Backup Code Form -->
        <form method="POST" action="{{ route('two-factor.challenge.backup') }}" x-show="method === 'backup'" x-cloak>
            @csrf

            <div>
                <x-input-label for="backup_code" :value="__('Code de récupération')" />
                <x-text-input
                    id="backup_code"
                    class="mt-1 block w-full text-center font-mono tracking-wider"
                    type="text"
                    name="code"
                    placeholder="XXXX-XXXX"
                    autocomplete="off"
                    required
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-primary-button class="w-full justify-center">
                    Vérifier
                </x-primary-button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
                Annuler et se déconnecter
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
        document.getElementById('verify-passkey')?.addEventListener('click', async () => {
            try {
                // Get assertion options from our custom endpoint (works without auth)
                const optionsResponse = await fetch('{{ route('two-factor.challenge.passkey.options') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!optionsResponse.ok) {
                    const error = await optionsResponse.json();
                    alert(error.error || 'Erreur lors de la récupération des options.');
                    return;
                }

                const options = await optionsResponse.json();

                options.challenge = base64UrlToUint8Array(options.challenge);
                if (options.allowCredentials) {
                    options.allowCredentials = options.allowCredentials.map(cred => ({
                        ...cred,
                        id: base64UrlToUint8Array(cred.id)
                    }));
                }

                // Prompt user for passkey
                const assertion = await navigator.credentials.get({ publicKey: options });

                // Verify the assertion with our custom endpoint
                const verifyResponse = await fetch('{{ route('two-factor.challenge.passkey.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id: assertion.id,
                        rawId: uint8ArrayToBase64Url(new Uint8Array(assertion.rawId)),
                        type: assertion.type,
                        response: {
                            clientDataJSON: uint8ArrayToBase64Url(new Uint8Array(assertion.response.clientDataJSON)),
                            authenticatorData: uint8ArrayToBase64Url(new Uint8Array(assertion.response.authenticatorData)),
                            signature: uint8ArrayToBase64Url(new Uint8Array(assertion.response.signature)),
                            userHandle: assertion.response.userHandle ? uint8ArrayToBase64Url(new Uint8Array(assertion.response.userHandle)) : null
                        }
                    })
                });

                if (verifyResponse.ok) {
                    // Complete the 2FA challenge after successful WebAuthn
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('two-factor.challenge.passkey') }}';
                    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert('Échec de la vérification de la clé de sécurité.');
                }
            } catch (error) {
                console.error('Passkey verification failed:', error);
                if (error.name === 'NotAllowedError') {
                    alert('Authentification annulée ou expirée.');
                } else {
                    alert('Erreur lors de la vérification de la clé de sécurité.');
                }
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
