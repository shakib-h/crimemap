import L from 'leaflet';

export function initializeMap() {
    if (typeof L === 'undefined') {
        console.error('Leaflet is not loaded');
        return;
    }

    const map = L.map('map', {
        zoomControl: false,
        attributionControl: true
    }).setView(window.mapConfig.center, window.mapConfig.zoom);

    // Add tile layer
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Add zoom control
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    // Add markers
    addCrimeMarkers(map, window.mapConfig.crimes);

    // Handle navigation padding
    adjustMapPadding(map);

    // Initialize report marker if authenticated
    initializeReportMarker(map);
}

function addCrimeMarkers(map, crimes) {
    if (Array.isArray(crimes) && crimes.length > 0) {
        crimes.forEach(crime => {
            L.marker([crime.latitude, crime.longitude])
                .bindPopup(createPopupContent(crime))
                .addTo(map);
        });
    }
}

function createPopupContent(crime) {
    return `
        <h3 class="font-bold">${crime.title}</h3>
        <p class="text-sm text-gray-600 mb-2">${crime.description}</p>
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>${crime.crime_type?.name ?? 'Unknown'}</span>
            <span>${new Date(crime.created_at).toLocaleDateString()}</span>
        </div>
    `;
}

function adjustMapPadding(map) {
    const nav = document.querySelector('nav');
    if (nav) {
        const navHeight = nav.offsetHeight;
        map.setView(map.getCenter(), map.getZoom(), {
            paddingTopLeft: [0, navHeight]
        });
    }
}

function initializeReportMarker(map) {
    if (!document.getElementById('crimeReportForm')) return;

    let reportMarker;
    map.on('click', function(e) {
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