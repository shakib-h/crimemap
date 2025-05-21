import './bootstrap';
import 'leaflet/dist/leaflet.css';
import { initializeMap } from './map';

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

// Wait for DOM and ensure map config is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (!window.mapConfig) {
        console.error('Map configuration not found');
        return;
    }

    try {
        initializeMap();
        console.log('Main map initialized');
    } catch (error) {
        console.error('Error initializing map:', error);
    }
});
