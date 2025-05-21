<x-app-layout>
    {{-- Map Container --}}
    <div id="map" class="fixed inset-0 w-screen h-screen z-0"></div>

    {{-- Location Button --}}
    <button id="locate-btn" class="fixed bottom-4 right-20 z-40 p-3 bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 backdrop-blur-sm">
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
                    position: 'topleft',
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

                // Location tracking variables
                let userMarker, userCircle;

                // Setup location button
                const locateBtn = document.getElementById('locate-btn');
                locateBtn?.addEventListener('click', () => {
                    map.locate({ setView: true, maxZoom: 16 });
                });

                // Handle location found
                map.on('locationfound', function(e) {
                    const radius = e.accuracy;

                    if (userMarker) {
                        userMarker.setLatLng(e.latlng);
                        userCircle.setLatLng(e.latlng).setRadius(radius);
                    } else {
                        userMarker = L.marker(e.latlng).addTo(map);
                        userCircle = L.circle(e.latlng, {
                            radius: radius,
                            color: '#3b82f6',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.15
                        }).addTo(map);
                    }

                    userMarker.bindPopup("You are within " + radius + " meters from this point").openPopup();
                });

                // Handle location error
                map.on('locationerror', function(e) {
                    alert("Could not find your location: " + e.message);
                });
            });
        </script>
    @endpush
</x-app-layout>