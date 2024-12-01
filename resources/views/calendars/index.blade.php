<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Mes Calendriers') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <div class="pb-5">
            <a href="{{ route('calendars.create') }}" class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Ajouter un Calendrier</a>
          </div>

          @if(session('success'))
          <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Info !</span> {{ session('success') }}
          </div>
          @endif

          @if($calendars->count())
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nom</th>

                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">URL</th>

                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">

              @foreach($calendars as $calendar)
              <tr>
                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $calendar->name }}</td>

                <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $calendar->url }}</td>
                <td class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">
                  <form action="{{ route('calendars.destroy', $calendar) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce calendrier ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-full bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Supprimer</button>
                  </form>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @else
          <p>Vous n'avez pas encore de calendriers.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
