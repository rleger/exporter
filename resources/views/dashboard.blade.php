<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-8 mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Colonne des derniers rendez-vous ajoutés -->
                <x-appointments.list :appointments="$recentAppointments" title="Nouveaux patients" :isUpdated="false" empty-message="Aucun rendez-vous récent." />

                <!-- Colonne des derniers rendez-vous modifiés -->
                <x-appointments.list :appointments="$updatedAppointments" title="Patients existants" :isUpdated="true" empty-message="Aucun rendez-vous récent." />
            </div>

            <x-appointments.upcoming-list :appointments="$groupedAppointments" title="Prochains rendez-vous" :isUpdated="true" empty-message="Aucun rendez-vous récent." />

            <x-top-cancelled-entries :entries="$topCancelledEntries" />

            <!-- Bouton pour exécuter la commande d'importation -->
            <div class="shadow-xs mt-6 overflow-hidden bg-white p-6 sm:rounded-lg">
                <form method="POST" action="{{ route('import.calendars') }}">
                    @csrf
                    <button type="submit" class="rounded-sm bg-blue-500 px-4 py-2 text-white">
                        Importer les Calendriers
                    </button>
                </form>

                <!-- Afficher la sortie de la commande -->
                @if (session('importOutput'))
                    <div class="mt-4 rounded-sm bg-gray-100 p-4">
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
