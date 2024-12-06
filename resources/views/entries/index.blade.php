<!-- resources/views/entries/index.blade.php -->

<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Entrées') }} ({{ $entries->total() }})
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-8xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">

          @if(session('importOutput'))
          <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">
            {{ session('importOutput') }}
          </div>
          @endif

          <!-- Formulaire de Recherche -->
          <form method="GET" action="{{ route('entries.index') }}">
            <div class="flex items-center justify-between mb-6 space-x-2">

              <div>
                <div class="flex ">
                  <div class="grid grid-cols-1 -mr-px w-80 grow focus-within:relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="col-start-1 row-start-1 block w-full rounded-l-md bg-white py-1.5 pl-10 pr-3 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:pl-9 sm:text-sm/6" placeholder="Rechercher par Nom, Prénom ou Email">

                    <svg class="self-center col-start-1 row-start-1 ml-3 text-gray-400 pointer-events-none size-5 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                      <path d="M8.5 4.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10.9 12.006c.11.542-.348.994-.9.994H2c-.553 0-1.01-.452-.902-.994a5.002 5.002 0 0 1 9.803 0ZM14.002 12h-1.59a2.556 2.556 0 0 0-.04-.29 6.476 6.476 0 0 0-1.167-2.603 3.002 3.002 0 0 1 3.633 1.911c.18.522-.283.982-.836.982ZM12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                    </svg>
                  </div>

                  @if(request('search') || request('all_entries'))
                  <a href="{{ route('entries.index') }}" class="flex shrink-0 items-center gap-x-1.5  bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-900 outline outline-1 -outline-offset-1 outline-indigo-300 hover:bg-indigo-100 focus:relative focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600">
                    Reinitialiser
                  </a>
                  @endif
                  <button type="submit" class="flex shrink-0 items-center gap-x-1.5 rounded-r-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 hover:bg-gray-50 focus:relative focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-0.5 size-4 text-gray-400">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Rechercher
                  </button>
                </div>
              </div>

              {{-- Checkbox --}}
              <div class="flex items-center">
                <input type="checkbox" name="all_entries" id="all_entries" {{ request()->has('all_entries') ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded">
                <label for="all_entries" class="block ml-2 text-sm text-gray-900">
                  Toutes les entrées
                </label>
              </div>
            </div>
          </form>

          @if($entries->count())
          <div class="flow-root mt-8">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      @php
                      // Fonction pour générer les liens de tri
                      function sortLink($column, $label, $sort, $direction, $search) {
                      $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
                      return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $newDirection, 'search' => $search ?? '']);
                      }
                      @endphp

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('lastname', 'Prénom', $sort, $direction, $search) }}" class="flex items-center">
                          Nom
                          @if($sort === 'lastname')
                          @if($direction === 'asc')
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('name', 'Nom', $sort, $direction, $search) }}" class="flex items-center">
                          Prénom
                          @if($sort === 'name')
                          @if($direction === 'asc')
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('birthdate', 'Date de Naissance', $sort, $direction, $search) }}" class="flex items-center">
                          Date de Naissance
                          @if($sort === 'birthdate')
                          @if($direction === 'asc')
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('appointments_count', 'Nb', $sort, $direction, $search) }}" class="flex items-center">
                          Nb
                          @if($sort === 'appointments_count')
                          @if($direction === 'asc')
                          <!-- Icône pour le tri ascendant -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <!-- Icône pour le tri descendant -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('total_duration', 'Durée', $sort, $direction, $search) }}" class="flex items-center">
                          Durée
                          @if($sort === 'total_duration')

                          @if($direction === 'asc')
                          <!-- Icône pour le tri ascendant -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <!-- Icône pour le tri descendant -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        Contact
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        Sujet
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('created_at', 'Date ajout', $sort, $direction, $search) }}" class="flex items-center">
                          Date ajout
                          @if($sort === 'created_at')
                          @if($direction === 'asc')
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <a href="{{ sortLink('updated_at', 'Date maj', $sort, $direction, $search) }}" class="flex items-center">
                          Date maj
                          @if($sort === 'updated_at')
                          @if($direction === 'asc')
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          @else
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          @endif
                          @endif
                        </a>
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        Propriétaire
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white">
                    @foreach($entries as $entry)
                    <tr class="even:bg-gray-50">
                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">
                        <a class="text-gray-800 hover:text-gray-700 hover:underline" href="{{ route('appointments.show', $entry->id) }}">
                          {{ $entry->lastname }}
                        </a>
                      </td>
                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">
                        <a class="text-gray-800 hover:text-gray-700 hover:underline" href="{{ route('appointments.show', $entry->id) }}">
                          {{ $entry->name }}
                        </a>
                      </td>
                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($entry->birthdate)->format('d/m/Y') }}
                        ({{ \Carbon\Carbon::parse($entry->birthdate)->age }} ans)
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        <a href="{{ route('appointments.show', $entry->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 rounded-full bg-gray-50 ring-1 ring-inset ring-gray-500/10 hover:bg-gray-200">
                          {{ $entry->appointments_count }}
                        </a>
                      </td>

                      <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ $entry->total_duration }}
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        <div class="flex flex-col">
                          <span>{{ $entry->tel }}</span>
                          <span>{{ $entry->email }}</span>
                        </div>
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        <x-appointments.label :item="$entry" />
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ $entry->created_at->diffForHumans() }}
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ $entry->updated_at->diffForHumans() }}
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        <div class="flex flex-col">
                          <span>{{ optional($entry->calendar->user)->name ?? 'Utilisateur inconnu' }}</span>
                          <span>{{ optional($entry->calendar)->name ?? 'Calendrier inconnu' }}</span>
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="px-4 py-4 sm:px-6">
                {{ $entries->links() }}
              </div>
            </div>
          </div>
          @else
          <p>Aucune entrée trouvée.</p>
          @endif

        </div>
      </div>
    </div>
  </div>
</x-app-layout>
