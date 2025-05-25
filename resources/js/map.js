import L from 'leaflet';

// Global variables
let mainMap = null;

// Crime color mapping function (exposed globally)
function getCrimeColor(crimeType) {
    const colors = {
        'Theft': '#22c55e',
        'Assault': '#dc2626',
        'Vandalism': '#f97316',
        'Breaking and Entering': '#b91c1c',
        'Cybercrime': '#7c3aed',
        'Drug Trafficking': '#be123c',
        'Fraud': '#84cc16',
        'Human Trafficking': '#991b1b',
        'Corruption': '#92400e',
        'Counterfeiting': '#16a34a',
        'Extortion': '#e11d48',
        'Kidnapping': '#b91c1c',
        'Arson': '#ea580c',
        'Money Laundering': '#0f766e',
        'Terrorism': '#7f1d1d'
    };

    return colors[crimeType] || '#6b7280'; // Default gray if not found
}
window.getCrimeColor = getCrimeColor;

// Helper functions
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

// Create user location marker and circle
function createUserLocationMarker(latlng, accuracy, map) {
    const userLayer = {};
    
    userLayer.circle = L.circle(latlng, {
        radius: 50,
        color: '#3388ff',
        fillColor: '#3388ff',
        fillOpacity: 0.7,
        weight: 1
    }).addTo(map);
    
    userLayer.marker = L.marker(latlng, {
        icon: L.icon({
            iconUrl: '/images/marker-icon.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: '/images/marker-shadow.png',
            shadowSize: [41, 41]
        })
    }).addTo(map);
    
    userLayer.marker.bindPopup(`You are within ${Math.round(accuracy)} meters from this point`).openPopup();
    
    return userLayer;
}

function updateUserLocation(position, map, userLayer) {
    const { latitude: lat, longitude: lng, accuracy } = position.coords;
    const latlng = { lat, lng };

    if (userLayer.circle) {
        userLayer.circle.setLatLng(latlng);
    }
    
    if (userLayer.marker) {
        userLayer.marker.setLatLng(latlng);
        userLayer.marker.bindPopup(`You are within ${Math.round(accuracy)} meters from this point`).openPopup();
    }
    
    map.setView(latlng, 16);
    
    return latlng;
}

function setupLocationTracking(map) {
    let userLayer = {};
    
    function handleLocationError(error) {
        console.error("Location error:", error.message);
    }
    
    // Get initial location
    navigator.geolocation?.getCurrentPosition(
        (position) => {
            const latlng = updateUserLocation(position, map, userLayer);
            userLayer = createUserLocationMarker(latlng, position.coords.accuracy, map);
        },
        handleLocationError,
        { enableHighAccuracy: true, maximumAge: 30000, timeout: 27000 }
    );
    
    // Setup location button
    document.getElementById('locate-btn')?.addEventListener('click', () => {
        navigator.geolocation.getCurrentPosition(
            (position) => updateUserLocation(position, map, userLayer),
            handleLocationError,
            { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
        );
    });
    
    // Handle map location events
    map.on('locationfound', (e) => {
        if (!userLayer.circle) {
            userLayer = createUserLocationMarker(e.latlng, e.accuracy, map);
        } else {
            updateUserLocation({ coords: { latitude: e.latlng.lat, longitude: e.latlng.lng, accuracy: e.accuracy }}, map, userLayer);
        }
    });
    
    map.on('locationerror', (e) => {
        console.error("Could not find your location:", e.message);
    });
}

function initializeMiniMap() {
    let miniMap = null;
    let marker = null;
    
    // Setup modal event listeners
    document.addEventListener('open-modal', function(e) {
        if (e.detail !== 'crime-report') return;
        
        setTimeout(() => {
            const container = document.getElementById('mini-map');
            if (!container) return;
            
            try {
                miniMap = L.map('mini-map', {
                    zoomControl: false,
                    attributionControl: false
                });
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(miniMap);
                L.control.zoom({ position: 'bottomright' }).addTo(miniMap);
                
                // Get user location for mini map
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude: lat, longitude: lng } = position.coords;
                        miniMap.setView([lat, lng], 18);
                        marker = L.marker([lat, lng]).addTo(miniMap);
                        
                        // Set form values
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);
                        
                        miniMap.invalidateSize();
                    },
                    (error) => {
                        miniMap.setView(window.mapConfig?.center || [23.8103, 90.4125], 13);
                        miniMap.invalidateSize();
                    },
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                );
                
                // Handle map clicks
                miniMap.on('click', function(e) {
                    const { lat, lng } = e.latlng;
                    
                    if (marker) {
                        marker.setLatLng(e.latlng);
                    } else {
                        marker = L.marker(e.latlng).addTo(miniMap);
                    }
                    
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                });
            } catch (error) {
                console.error('Error initializing mini map:', error);
            }
        }, 500);
    });
    
    // Clean up on modal close
    document.addEventListener('close-modal', function(e) {
        if (e.detail !== 'crime-report' || !miniMap) return;
        miniMap.remove();
        miniMap = null;
        marker = null;
    });
}

// Filter functions
function isWithinDateRange(dateStr, range) {
    const date = new Date(dateStr);
    const now = new Date();
    
    switch (range) {
        case 'today':
            return date.toDateString() === now.toDateString();
        case 'week':
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            return date >= weekAgo;
        case 'month':
            const monthAgo = new Date();
            monthAgo.setMonth(monthAgo.getMonth() - 1);
            return date >= monthAgo;
        default:
            return true;
    }
}

function applyFilters() {
    if (!mainMap) return;

    const dateFilter = document.getElementById('dateFilter').value;
    const selectedTypes = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
        .map(cb => cb.value);

    // Remove existing circles
    mainMap.eachLayer((layer) => {
        if (layer instanceof L.Circle) {
            mainMap.removeLayer(layer);
        }
    });

    // Filter crimes
    const filteredCrimes = window.mapConfig.crimes.filter(crime => {
        const matchesDate = dateFilter === 'all' ? true : isWithinDateRange(crime.created_at, dateFilter);
        const matchesType = selectedTypes.length === 0 || selectedTypes.includes(crime.crime_type_id.toString());
        return matchesDate && matchesType;
    });

    // Add filtered circles
    addCrimeCircles(mainMap, filteredCrimes);
}
window.applyFilters = applyFilters;

// Main initialization function
export function initializeMap() {
    // Get config or use defaults
    const config = window.mapConfig || {
        center: [23.8103, 90.4125],
        zoom: 13,
        crimes: []
    };

    // Initialize main map
    mainMap = L.map('map', {
        zoomControl: false,
        attributionControl: false,
        fadeAnimation: true,
        markerZoomAnimation: true,
    }).setView(config.center, config.zoom);

    // Add base layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap Contributors',
    }).addTo(mainMap);

    // Add controls
    L.control.zoom({ position: 'topleft' }).addTo(mainMap);

    // Initialize features
    addCrimeCircles(mainMap, config.crimes);
    setupLocationTracking(mainMap);
    initializeMiniMap();
}