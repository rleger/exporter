<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-xs sm:rounded-lg">
        <div class="p-2 text-gray-900">
          @if($calendars->count())
          <div class="flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nom du Calendrier</th>
                      <th scope="col" class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Propriétaire</th>
                      <th scope="col" class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nombre d'Entrées</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white">
                    @foreach($calendars as $calendar)
                    <tr class="even:bg-gray-50">
                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">{{ $calendar->name }}</td>
                      <td class="py-4 pl-4 pr-3 text-sm text-gray-500 whitespace-nowrap">
                        {{ $calendar->user->name ?? 'Utilisateur inconnu' }}
                      </td>
                      <td class="py-4 pl-4 pr-3 text-sm text-gray-500 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 rounded-md bg-gray-50 ring-1 ring-inset ring-gray-500/10">{{ $calendar->entries_count }}</span>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          @else
          <p>Aucun calendrier trouvé.</p>
          @endif
        </div>
      </div>
      <div class="grid grid-cols-1 gap-6 mt-6 mb-8 md:grid-cols-2">
        <!-- Colonne des derniers rendez-vous ajoutés -->
        <x-appointments.list :appointments="$recentAppointments" title="Nouveaux patients" :isUpdated="false" empty-message="Aucun rendez-vous récent." />

        <!-- Colonne des derniers rendez-vous modifiés -->
        <x-appointments.list :appointments="$updatedAppointments" title="Patients existants" :isUpdated="true" empty-message="Aucun rendez-vous récent." />
      </div>

      <x-appointments.upcoming-list :appointments="$groupedAppointments" title="Prochains rendez-vous" :isUpdated="true" empty-message="Aucun rendez-vous récent." />

      <x-top-cancelled-entries :entries="$topCancelledEntries" />

      <!-- Bouton pour exécuter la commande d'importation -->
      <div class="p-6 mt-6 overflow-hidden bg-white shadow-xs sm:rounded-lg">
        <form method="POST" action="{{ route('import.calendars') }}">
          @csrf
          <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-sm">
            Importer les Calendriers
          </button>
        </form>

        <!-- Afficher la sortie de la commande -->
        @if(session('importOutput'))
        <div class="p-4 mt-4 bg-gray-100 rounded-sm">
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
