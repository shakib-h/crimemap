<x-app-layout>
    {{-- Map Container --}}
    <div id="map" class="fixed inset-0 w-screen h-screen z-0"></div>

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

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof L === 'undefined') {
                    console.error('Leaflet is not loaded');
                    return;
                }

                console.log('Initializing map...');  // Debug log

                // Initialize map with adjusted options
                const map = L.map('map', {
                    zoomControl: false,
                    attributionControl: true
                }).setView(window.mapConfig.center, window.mapConfig.zoom);

                console.log('Map initialized');  // Debug log

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Add zoom control to bottom right
                L.control.zoom({
                    position: 'bottomright'
                }).addTo(map);

                // Add crime markers only if they exist
                const crimes = window.mapConfig.crimes;
                if (Array.isArray(crimes) && crimes.length > 0) {
                    crimes.forEach(crime => {
                        L.marker([crime.latitude, crime.longitude])
                            .bindPopup(`
                                <h3 class="font-bold">${crime.title}</h3>
                                <p class="text-sm text-gray-600 mb-2">${crime.description}</p>
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>${crime.crime_type.name}</span>
                                    <span>${new Date(crime.created_at).toLocaleDateString()}</span>
                                </div>
                            `)
                            .addTo(map);
                    });
                }

                // Adjust map padding for navigation bar
                const nav = document.querySelector('nav');
                if (nav) {
                    const navHeight = nav.offsetHeight;
                    map.setView(map.getCenter(), map.getZoom(), {
                        paddingTopLeft: [0, navHeight]
                    });
                }

                @auth
                let reportMarker;
                map.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    document.getElementById('location-text').textContent = 
                        `Location set: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;

                    if (reportMarker) {
                        reportMarker.setLatLng(e.latlng);
                    } else {
                        reportMarker = L.marker(e.latlng).addTo(map);
                    }
                });
                @endauth
            });
        </script>
    @endpush
</x-app-layout>