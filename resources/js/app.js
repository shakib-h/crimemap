import './bootstrap';
import 'leaflet/dist/leaflet.css';
import { initializeMap } from './map';
import { initializeDashboard } from './dashboard';

import Alpine from 'alpinejs';

// Fix Leaflet's icon path issues
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: '/images/marker-icon-2x.png',
    iconUrl: '/images/marker-icon.png',
    shadowUrl: '/images/marker-shadow.png',
});

window.L = L;
window.Alpine = Alpine;

Alpine.start();

// Initialize features based on current page
document.addEventListener('DOMContentLoaded', () => {
    // Initialize map if on map page
    if (document.getElementById('map')) {
        if (!window.mapConfig) {
            console.error('Map configuration not found');
            return;
        }
        initializeMap();
    }

    // Initialize dashboard if on dashboard page
    if (document.getElementById('type-filter')) {
        initializeDashboard();
    }
});
