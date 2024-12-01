<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          {{ __("You're logged in!") }}
        </div>
      </div>

      <!-- Bouton pour exécuter la commande d'importation -->
      <div class="p-6 mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <form method="POST" action="{{ route('import.calendars') }}">
          @csrf
          <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">
            Importer les Calendriers
          </button>
        </form>

        <!-- Afficher la sortie de la commande -->
        @if(session('importOutput'))
        <div class="p-4 mt-4 bg-gray-100 rounded">
          <h3 class="mb-2 text-lg font-semibold">Résultat de l'Importation :</h3>
          <div class="whitespace-pre-wrap">
            {!! session('importOutput') !!}
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

</x-app-layout>
