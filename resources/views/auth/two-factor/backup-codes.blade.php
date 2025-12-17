<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <strong class="text-red-600">Important :</strong> {{ __('Conservez ces codes de récupération dans un endroit sûr. Ils vous permettront d\'accéder à votre compte si vous perdez l\'accès à votre méthode d\'authentification principale.') }}
    </div>

    <div class="mb-6 rounded-lg bg-gray-100 p-4">
        <div class="grid grid-cols-2 gap-2">
            @foreach ($backupCodes as $code)
                <code class="rounded bg-white px-2 py-1 text-center font-mono text-sm">{{ $code }}</code>
            @endforeach
        </div>
    </div>

    <div class="mb-4 text-center text-xs text-gray-500">
        <p>Chaque code ne peut être utilisé qu'une seule fois.</p>
        <p>Vous ne pourrez plus voir ces codes après avoir quitté cette page.</p>
    </div>

    <form method="POST" action="{{ route('two-factor.backup-codes.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="confirm" class="flex items-center">
                <input id="confirm" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="confirm" required>
                <span class="ms-2 text-sm text-gray-600">{{ __('J\'ai copié et sauvegardé mes codes de récupération') }}</span>
            </label>
        </div>

        <x-primary-button class="w-full justify-center">
            Continuer vers le tableau de bord
        </x-primary-button>
    </form>
</x-guest-layout>
