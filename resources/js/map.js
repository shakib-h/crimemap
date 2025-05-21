import L from 'leaflet';

function getCrimeColor(crimeType) {
    const colors = {
        'Theft': '#22c55e',         // green-500
        'Assault': '#dc2626',       // red-600
        'Vandalism': '#f97316',     // orange-500
        'Breaking and Entering': '#b91c1c'  // red-700
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
    let userMarker;
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by this browser.");
        return;
    }

    const locateBtn = document.getElementById('locate-btn');
    locateBtn?.addEventListener('click', () => {
        map.locate({ setView: true, maxZoom: 16 });
    });

    map.on('locationfound', (e) => {
        const radius = e.accuracy;

        if (userMarker) {
            userMarker.setLatLng(e.latlng);
        } else {
            userMarker = L.marker(e.latlng).addTo(map);
        }

        userMarker.bindPopup(`You are within ${Math.round(radius)} meters from this point`).openPopup();
    });

    map.on('locationerror', (e) => {
        alert("Could not find your location: " + e.message);
    });
}

function setupReportMarker(map) {
    if (!document.getElementById('latitude')) return;

    let reportMarker;
    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        
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
}

export function initializeMap() {
    const map = L.map('map', {
        zoomControl: false,
        attributionControl: true
    }).setView(window.mapConfig.center, window.mapConfig.zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    L.control.zoom({
        position: 'topleft'
    }).addTo(map);

    // Initialize features
    addCrimeCircles(map, window.mapConfig.crimes);
    setupLocationTracking(map);
    setupReportMarker(map);

    // Adjust for navbar
    const nav = document.querySelector('nav');
    if (nav) {
        const navHeight = nav.offsetHeight;
        map.setView(map.getCenter(), map.getZoom(), {
            paddingTopLeft: [0, navHeight]
        });
    }
}