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
        <div class="p-6 text-gray-900">
          @if($calendars->count())
          <div class="flow-root mt-8">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nom du Calendrier</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Propriétaire</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nombre d'Entrées</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white">
                    @foreach($calendars as $calendar)
                    <tr class="even:bg-gray-50">
                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">{{ $calendar->name }}</td>
                      <td class="py-4 pl-4 pr-3 text-sm text-gray-500 whitespace-nowrap">
                        {{ $calendar->user->name ?? 'Utilisateur inconnu' }}
                      </td>
                      <td class="py-4 pl-4 pr-3 text-sm text-gray-500 whitespace-nowrap">{{ $calendar->entries_count }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="px-4 py-4 sm:px-6">
                <!-- Pagination si nécessaire -->
              </div>
            </div>
          </div>
          @else
          <p>Aucun calendrier trouvé.</p>
          @endif
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
