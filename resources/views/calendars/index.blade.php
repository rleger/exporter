<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Mes Calendriers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="shadow-xs overflow-hidden bg-white sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="pb-5">
                        <a href="{{ route('calendars.create') }}"
                            class="shadow-xs inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <div class="flex items-center">
                                <x-heroicon-o-plus-circle class="mr-2 size-6 text-white" />
                                Ajouter un Calendrier
                            </div>
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="dark:bg-gray-800 dark:text-blue-400 mb-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-800" role="alert">
                            <span class="font-medium">Info !</span> {{ session('success') }}
                        </div>
                    @endif

                    @if ($calendars->count())
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nom</th>

                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">URL</th>

                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">

                                @foreach ($calendars as $calendar)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $calendar->name }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $calendar->url }}</td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <form action="{{ route('calendars.destroy', $calendar) }}" method="POST"
                                                onsubmit="return confirm('Voulez-vous vraiment supprimer ce calendrier ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="shadow-xs rounded-full bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Supprimer</button>
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
