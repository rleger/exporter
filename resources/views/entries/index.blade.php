<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Entrées') }} ({{ $entries->count() }})
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-8xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">

          @if($entries->count())
          <div class="flow-root mt-8">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nom</th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Prénom</th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date de Naissance</th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Téléphone</th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>

                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Propriétaire</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white">
                    @foreach($entries as $entry)
                    <tr class="even:bg-gray-50">

                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">{{ $entry->name }}</td>

                      <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-3">{{ $entry->lastname }}</td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $entry->birthdate }}</td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $entry->tel }}</td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $entry->email }}</td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $entry->description }}</td>

                      <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $entry->calendar->user->name }} {{ $entry->calendar->name }}</td>

                    </tr>
                    @endforeach
                  </tbody>
                </table>

              </div>
              <div class="px-4 py-4 sm:px-6">
                {{ $entries->links() }}
              </div>
              @else
              <p>Aucune entrée trouvée.</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
</x-app-layout>
