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
          <form method="GET" action="{{ route('entries.index') }}" class="flex items-center mb-6 space-x-2">
            <div class="flex-grow max-w-xl mt-2">
              <input type="text" name="search" id="search" value="{{ request('search') }}" class="block w-full rounded-full bg-white px-4 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="Rechercher par Nom, Prénom ou Email">
            </div>
            @if(request('search'))
            <a href="{{ route('entries.index') }}" class="px-4 py-1.5 bg-red-500 text-white rounded-full">Réinitialiser</a>
            @endif
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
                        Contact
                      </th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                        Description
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
                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">{{ $entry->lastname }}</td>
                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">{{ $entry->name }}</td>
                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($entry->birthdate)->format('d/m/Y') }}
                        ({{ \Carbon\Carbon::parse($entry->birthdate)->age }} ans)
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">
                        <div class="flex flex-col">
                          <span>{{ $entry->tel }}</span>
                          <span>{{ $entry->email }}</span>
                        </div>
                      </td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $entry->description }}</td>

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
