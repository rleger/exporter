<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Scannez le QR code ci-dessous avec votre application d\'authentification, puis entrez le code à 6 chiffres pour confirmer.') }}
    </div>

    <div class="mb-6 flex justify-center">
        <div class="rounded-lg bg-white p-4">
            {!! $qrCodeSvg !!}
        </div>
    </div>

    <div class="mb-4">
        <p class="text-center text-xs text-gray-500">
            Ou entrez ce code manuellement :
        </p>
        <p class="mt-1 text-center font-mono text-sm font-medium text-gray-900">
            {{ $secret }}
        </p>
    </div>

    <form method="POST" action="{{ route('two-factor.setup.totp.confirm') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Code de vérification')" />
            <x-text-input
                id="code"
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

        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('two-factor.setup') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                Retour
            </a>
            <x-primary-button>
                Confirmer
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
