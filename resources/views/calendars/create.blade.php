<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ __('Ajouter un Calendrier') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">

          <h1 class="pb-10 text-lg">Ajouter un Calendrier</h1>

          <form action="{{ route('calendars.store') }}" method="POST">
            @csrf
            <div>
              <label for="name" class="block font-medium text-gray-900 text-sm/6">Nom du Calendrier</label>

              <div class="mt-2">
                <input type="text" name="name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required value="{{ old('name') }}">
              </div>
            </div>

            <div class="pt-5">
              <label for="url" class="block font-medium text-gray-900 text-sm/6">URL du Calendrier</label>

              <div class="mt-2">
                <input type="url" name="url" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required value="{{ old('url') }}">
              </div>
            </div>

            <div class="pt-10">
              <button type="submit" type="button" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Ajouter</button>
            </div>




          </form>
        </div>
      </div>
    </div>
</x-app-layout>
