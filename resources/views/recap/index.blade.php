<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Recap') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="shadow-xs overflow-hidden bg-white sm:rounded-lg">
                <div class="p-2 text-gray-900">
                    @if ($calendars->count())
                        <div class="flow-root">
                            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nom du
                                                    Calendrier</th>
                                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Propriétaire</th>
                                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nombre
                                                    d'Entrées</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            @foreach ($calendars as $calendar)
                                                <tr class="even:bg-gray-50">
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3">{{ $calendar->name }}
                                                    </td>
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500">
                                                        {{ $calendar->user->name ?? 'Utilisateur inconnu' }}
                                                    </td>
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500">
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">{{ $calendar->entries_count }}</span>
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

        </div>
    </div>

</x-app-layout>
