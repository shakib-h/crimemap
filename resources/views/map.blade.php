<x-app-layout>
    {{-- Map Container --}}
    <div id="map" class="fixed inset-0 w-screen h-screen z-0"></div>

    {{-- Filter Button --}}
    <div x-data="{ open: false }" class="fixed top-20 right-4 z-40">
        <button 
            @click="open = !open"
            class="flex items-center space-x-2 px-4 py-2 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 rounded-lg shadow-lg hover:bg-white dark:hover:bg-gray-700 transition-colors backdrop-blur-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>Filter</span>
        </button>

        {{-- Filter Dropdown --}}
        <div 
            x-show="open" 
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-64 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-200 dark:ring-gray-700"
        >
            <div class="p-4 space-y-4">
                {{-- Date Filter --}}
                <div>
                    <label for="dateFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                    <select id="dateFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>

                {{-- Crime Type Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Crime Types</label>
                    <div class="space-y-2 p-2 max-h-48 overflow-y-auto rounded-md bg-gray-50 dark:bg-gray-900">
                        @foreach($crimeTypes as $type)
                            <label class="flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 px-2 py-1 rounded cursor-pointer">
                                <div class="flex items-center flex-1">
                                    <input type="checkbox" value="{{ $type->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $type->name }}</span>
                                </div>
                                <span class="w-3 h-3 rounded-full ml-2" id="color-dot-{{ $type->id }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Apply Button --}}
                <button 
                    onclick="applyFilters()"
                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                >
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Location Button --}}
    <button id="locate-btn" class="fixed bottom-20 right-4 z-40 p-3 bg-gray-800/90 dark:bg-white/90 text-gray-200 dark:text-gray-700 rounded-full shadow-lg hover:bg-gray-700 dark:hover:bg-white transition-colors backdrop-blur-sm">
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
            class="fixed bottom-4 left-4 z-40 px-4 py-2 bg-red-600 text-white rounded-full shadow-lg hover:bg-red-700 transition-colors flex items-center space-x-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
            <span>Report Crime</span>
        </button>

        {{-- Report Modal with Map --}}
        <x-modal name="crime-report" :show="false" maxWidth="md">
            <div class="p-6" x-cloak>
                <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Report Crime</h2>
                
                {{-- Mini Map Container --}}
                <div id="mini-map" class="w-full h-64 mb-4 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-700"></div>
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
</x-app-layout>