<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-2 text-gray-900">
          @if($calendars->count())
          <div class="flow-root">
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
      <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
        <!-- Colonne des derniers rendez-vous ajoutés -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">Derniers rendez-vous ajoutés</h3>
            @if($recentAppointments->count())
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Entrée</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  @foreach($recentAppointments as $appointment)
                  <tr>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                      <div class="flex flex-col">
                        <span class="{{ \Carbon\Carbon::parse($appointment->date)->isPast() ? 'text-gray-400' : 'text-gray-800' }}">
                          {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}
                        </span>
                        <span class="text-xs text-gray-500">
                          {{ $appointment->created_at->diffForHumans() }}
                        </span>
                      </div>
                    </td>

                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                      <div class="flex flex-col">
                        <span class="font-semibold">
                          <a class="text-indigo-600 hover:text-indigo-700 hover:underline" href="{{ route('appointments.show', $appointment->entry->id) }}">
                            {{ $appointment->entry->name }} {{ $appointment->entry->lastname }}
                          </a>
                        </span>
                        <span>
                          {{ $appointment->subject }}
                        </span>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <p>Aucun rendez-vous ajouté récemment.</p>
            @endif
          </div>
        </div>

        <!-- Colonne des derniers rendez-vous modifiés -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">Derniers rendez-vous modifiés</h3>
            @if($updatedAppointments->count())
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Entrée</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  @foreach($updatedAppointments as $appointment)
                  <tr>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                      <div class="flex flex-col">
                        <span class="{{ \Carbon\Carbon::parse($appointment->date)->isPast() ? 'text-gray-400' : 'text-gray-800' }}">
                          {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}
                        </span>
                        <span class="text-xs text-gray-500">
                          {{ $appointment->updated_at->diffForHumans() }}
                        </span>
                      </div>
                    </td>

                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                      <div class="flex flex-col">
                        <span class="font-semibold">
                          <a class="text-indigo-600 hover:text-indigo-700 hover:underline" href="{{ route('appointments.show', $appointment->entry->id) }}">
                            {{ $appointment->entry->name }} {{ $appointment->entry->lastname }}
                          </a>
                        </span>
                        <span>
                          {{ $appointment->subject }}
                        </span>
                      </div>
                    </td>

                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <p>Aucun rendez-vous modifié récemment.</p>
            @endif
          </div>
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
