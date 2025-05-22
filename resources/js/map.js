import L from 'leaflet';

// Add this at the top of the file with other global variables
let mainMap = null;

function getCrimeColor(crimeType) {
    const colors = {
        'Theft': '#22c55e',              // green-500 (medium)
        'Assault': '#dc2626',            // red-600 (high)
        'Vandalism': '#f97316',          // orange-500 (low)
        'Breaking and Entering': '#b91c1c', // red-700 (high)
        'Cybercrime': '#7c3aed',         // violet-600 (high)
        'Drug Trafficking': '#be123c',   // rose-700 (high)
        'Fraud': '#84cc16',              // lime-500 (medium)
        'Human Trafficking': '#991b1b',  // red-800 (high)
        'Corruption': '#92400e',         // amber-800 (high)
        'Counterfeiting': '#16a34a',     // green-600 (medium)
        'Extortion': '#e11d48',          // rose-600 (high)
        'Kidnapping': '#b91c1c',         // red-700 (high)
        'Arson': '#ea580c',              // orange-600 (high)
        'Money Laundering': '#0f766e',   // teal-700 (high)
        'Terrorism': '#7f1d1d'           // red-900 (high)
    };

    return colors[crimeType] || '#6b7280'; // gray-500 as default
}

function createCrimePopup(crime) {
    return `
        <h3 class="font-bold">${crime.title}</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">${crime.description}</p>
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center">
                <span class="w-2 h-2 rounded-full mr-1" style="background-color: ${getCrimeColor(crime.crime_type.name)}"></span>
                ${crime.crime_type.name}
            </span>
            <span>${new Date(crime.created_at).toLocaleDateString()}</span>
        </div>
    `;
}

function addCrimeCircles(map, crimes) {
    if (!Array.isArray(crimes)) return;

    crimes.forEach(crime => {
        L.circle([crime.latitude, crime.longitude], {
            radius: 100,
            color: getCrimeColor(crime.crime_type.name),
            fillColor: getCrimeColor(crime.crime_type.name),
            fillOpacity: 0.5,
            weight: 1
        })
            .bindPopup(createCrimePopup(crime))
            .addTo(map);
    });
}

function setupLocationTracking(map) {
    let userCircle, userMarker;
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by this browser.");
        return;
    }

    function updateLocation(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        const latlng = { lat, lng };

        if (userCircle) {
            userCircle.setLatLng(latlng);
        } else {
            userCircle = L.circle(latlng,
                {
                    radius: 50,
                    color: '#3388ff',
                    fillColor: '#3388ff',
                    fillOpacity: 0.7,
                    weight: 1
                }
            ).addTo(map);
        }

        if (userMarker) {
            userMarker.setLatLng(latlng);
        }
        else {
            userMarker = L.marker(latlng, {
                icon: L.icon({
                    iconUrl: '/images/marker-icon.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowUrl: '/images/marker-shadow.png',
                    shadowSize: [41, 41]
                })
            }).addTo(map);
        }

        userMarker.bindPopup(`You are within ${Math.round(accuracy)} meters from this point`).openPopup();
        map.setView(latlng, 16);
    }

    function handleLocationError(error) {
        alert("Could not find your location: " + error.message);
    }

    // Get location on page load
    navigator.geolocation.getCurrentPosition(updateLocation, handleLocationError, {
        enableHighAccuracy: true,
        maximumAge: 30000,
        timeout: 27000
    });

    // Setup locate button
    const locateBtn = document.getElementById('locate-btn');
    locateBtn?.addEventListener('click', () => {
        navigator.geolocation.getCurrentPosition(updateLocation, handleLocationError, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 5000
        });
    });

    // Keep existing map event handlers for compatibility
    map.on('locationfound', (e) => {
        const radius = e.accuracy;

        if (userCircle) {
            userCircle.setLatLng(e.latlng);
        } else {
            userCircle = L.marker(e.latlng).addTo(map);
        }

        userCircle.bindPopup(`You are within ${Math.round(radius)} meters from this point`).openPopup();
    });

    map.on('locationerror', (e) => {
        alert("Could not find your location: " + e.message);
    });
}

function initializeMiniMap() {
    let miniMap = null;
    let marker = null;

    // Listen for modal open event
    document.addEventListener('open-modal', function (e) {
        if (e.detail !== 'crime-report') return;

        console.log('Modal opened, initializing mini map');

        setTimeout(() => {
            const container = document.getElementById('mini-map');
            if (!container) {
                console.error('Mini map container not found');
                return;
            }

            try {
                miniMap = L.map('mini-map', {
                    zoomControl: false,
                    attributionControl: false
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(miniMap);

                L.control.zoom({
                    position: 'bottomright'
                }).addTo(miniMap);

                // Get user's location first
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude: lat, longitude: lng } = position.coords;
                        miniMap.setView([lat, lng], 18);

                        // Add marker at user's location
                        marker = L.marker([lat, lng]).addTo(miniMap);

                        // Set initial form values
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);

                        // Force map to recalculate its container size
                        miniMap.invalidateSize();
                    },
                    (error) => {
                        console.error('Location error:', error);
                        // Fallback to default location
                        miniMap.setView(window.mapConfig?.center || [23.8103, 90.4125], 13);
                        miniMap.invalidateSize();
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );

                // Add click handler for location selection
                miniMap.on('click', function (e) {
                    const { lat, lng } = e.latlng;

                    if (marker) {
                        marker.setLatLng(e.latlng);
                    } else {
                        marker = L.marker(e.latlng).addTo(miniMap);
                    }

                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                });

                console.log('Mini map initialized successfully');
            } catch (error) {
                console.error('Error initializing mini map:', error);
            }
        }, 500);
    });

    // Clean up on modal close
    document.addEventListener('close-modal', function (e) {
        if (e.detail !== 'crime-report') return;

        if (miniMap) {
            miniMap.remove();
            miniMap = null;
            marker = null;
            console.log('Mini map cleaned up');
        }
    });
}

// Update the applyFilters function
function applyFilters() {
    if (!mainMap) return;

    const dateFilter = document.getElementById('dateFilter').value;
    const selectedTypes = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
        .map(cb => cb.value);

    // Clear existing markers
    mainMap.eachLayer((layer) => {
        if (layer instanceof L.Circle) {
            mainMap.removeLayer(layer);
        }
    });

    // Filter crimes based on selection
    const filteredCrimes = window.mapConfig.crimes.filter(crime => {
        const matchesDate = dateFilter === 'all' ? true : isWithinDateRange(crime.created_at, dateFilter);
        const matchesType = selectedTypes.length === 0 || selectedTypes.includes(crime.crime_type_id.toString());
        return matchesDate && matchesType;
    });

    // Add filtered crimes to map
    addCrimeCircles(mainMap, filteredCrimes);
}

// Add this to make applyFilters available globally
window.applyFilters = applyFilters;

function isWithinDateRange(dateStr, range) {
    const date = new Date(dateStr);
    const now = new Date();
    
    switch (range) {
        case 'today':
            return date.toDateString() === now.toDateString();
        case 'week':
            const weekAgo = new Date(now.setDate(now.getDate() - 7));
            return date >= weekAgo;
        case 'month':
            const monthAgo = new Date(now.setMonth(now.getMonth() - 1));
            return date >= monthAgo;
        default:
            return true;
    }
}

// Modify the initializeMap function
export function initializeMap() {
    // Use default config if window.mapConfig is not available
    const config = window.mapConfig || {
        center: [23.8103, 90.4125],
        zoom: 13,
        crimes: []
    };

    // Initialize main map and store in global variable
    mainMap = L.map('map', {
        zoomControl: false,
        attributionControl: true
    }).setView(config.center, config.zoom);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(mainMap);

    // Add zoom control
    L.control.zoom({
        position: 'topleft'
    }).addTo(mainMap);

    // Initialize features
    addCrimeCircles(mainMap, config.crimes);
    setupLocationTracking(mainMap);
    initializeMiniMap();
}