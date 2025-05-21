<x-app-layout>
    {{-- Map Container --}}
    <div id="map" class="fixed inset-0 w-screen h-screen z-0"></div>

    {{-- Location Button --}}
    <button id="locate-btn" class="fixed bottom-20 right-4 z-40 p-3 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 backdrop-blur-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    {{-- Bottom Sheet for authenticated users --}}
    @auth
        <div class="crime-report-sheet fixed bottom-0 left-0 w-full md:w-96 bg-white/95 dark:bg-gray-800/95 shadow-lg rounded-t-xl transform transition-transform duration-300 translate-y-[90%] hover:translate-y-0 focus-within:translate-y-0 z-40 backdrop-blur-sm">
            <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                <div class="w-12 h-1 bg-gray-300 dark:bg-gray-600 rounded-full mx-auto"></div>
            </div>
            
            <div class="p-4">
                <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Report Crime</h2>
                <x-crime-report-form :crimeTypes="$crimeTypes" />
            </div>
        </div>
    @endauth

    @push('scripts')
        @vite(['resources/js/app.js'])
        <script>
            window.mapConfig = {
                center: [23.8103, 90.4125],
                zoom: 13,
                crimes: @json($crimes ?? [])
            };
        </script>
    @endpush
</x-app-layout>