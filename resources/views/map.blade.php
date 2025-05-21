<x-app-layout>
    {{-- Map Container --}}
    <div id="map" class="fixed inset-0 w-screen h-screen z-0"></div>

    @push('scripts')
        <script>
            // Define map configuration before loading app.js
            window.mapConfig = {
                center: [23.8103, 90.4125],
                zoom: 13,
                crimes: @json($crimes ?? [])
            };
        </script>
        @vite(['resources/js/app.js'])
    @endpush

    {{-- Location Button --}}
    <button id="locate-btn" class="fixed bottom-20 right-4 z-40 p-3 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 backdrop-blur-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    @auth
        {{-- Report Button --}}
        <button 
            x-data
            @click="$dispatch('open-modal', 'crime-report')"
            class="fixed bottom-4 left-4 z-40 px-4 py-2 bg-red-600 text-white rounded-full shadow-lg hover:bg-red-700 flex items-center space-x-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Report Crime</span>
        </button>

        {{-- Report Modal with Map --}}
        <x-modal name="crime-report" :show="false" maxWidth="md" x-data="{ show: false }">
            <div class="p-6" x-cloak>
                <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Report Crime</h2>
                
                {{-- Mini Map Container with explicit dimensions --}}
                <div id="mini-map" class="w-full h-64 mb-4 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-700" style="min-height: 256px;"></div>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Click on the map above to set the crime location
                </p>

                {{-- Report Form --}}
                <form id="crimeReportForm" action="{{ route('crimes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="crime_type_id" value="Crime Type" />
                            <select name="crime_type_id" id="crime_type_id" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select Crime Type</option>
                                @foreach($crimeTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="title" value="Title" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" name="description" rows="3" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                Cancel
                            </x-secondary-button>
                            <x-primary-button>
                                Report
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </x-modal>
    @endauth
</x-app-layout>