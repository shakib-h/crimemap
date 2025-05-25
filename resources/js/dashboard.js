// Main dashboard initialization function
export function initializeDashboard() {
    // Set up all dashboard functionality
    setupFilters();
    setupMapInitialization();
    setupDropdowns();
}

/**
 * Setup filter functionality
 */
function setupFilters() {
    // Make the filter function globally available
    window.filterCrimes = function() {
        try {
            // Get filter values, with fallbacks
            const typeFilter = document.getElementById('type-filter')?.value || '';
            const statusFilter = document.getElementById('status-filter')?.value || '';
            
            // Create a fresh URL to avoid parameter duplication
            const baseUrl = window.location.pathname;
            const url = new URL(baseUrl, window.location.origin);
            
            // Add parameters only if they have values
            if (typeFilter) {
                url.searchParams.append('type', typeFilter);
            }
            
            if (statusFilter) {
                url.searchParams.append('status', statusFilter);
            }
            
            // Navigate to filtered URL
            window.location.href = url.toString();
        } catch (error) {
            console.error('Error applying filters:', error);
        }
    };
    
    // Set initial filter values from URL on page load
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const params = new URLSearchParams(window.location.search);
            
            // Set filter dropdowns to match URL parameters
            setSelectValueIfExists('type-filter', params.get('type'));
            setSelectValueIfExists('status-filter', params.get('status'));
        } catch (error) {
            console.error('Error initializing filters:', error);
        }
    });
}

/**
 * Helper function to set a select element's value if the element exists
 */
function setSelectValueIfExists(elementId, value) {
    const element = document.getElementById(elementId);
    if (element && value) {
        element.value = value;
    }
}

/**
 * Setup map initialization for crime details
 */
function setupMapInitialization() {
    // Store created maps for cleanup
    const maps = new Map();
    
    // Initialize map when modal opens
    window.initializeViewMap = function(id, lat, lng, color) {
        // Wait for the modal transition to complete
        requestAnimationFrame(() => {
            setTimeout(() => {
                try {
                    const mapId = 'view-map-' + id;
                    const container = document.getElementById(mapId);
                    
                    if (!container) return;
                    
                    // Clean up existing map if present
                    if (maps.has(id)) {
                        maps.get(id).remove();
                        maps.delete(id);
                    }
                    
                    // Create new map
                    const viewMap = L.map(mapId, {
                        zoomControl: true,
                        attributionControl: false
                    }).setView([lat, lng], 15);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(viewMap);
                    
                    L.circle([lat, lng], {
                        radius: 100,
                        color: color,
                        fillColor: color,
                        fillOpacity: 0.5
                    }).addTo(viewMap);
                    
                    // Force map to recalculate its size
                    viewMap.invalidateSize();
                    
                    // Store map for future cleanup
                    maps.set(id, viewMap);
                } catch(error) {
                    console.error('Error initializing view map:', error);
                }
            }, 300); // Increased delay for better modal rendering
        });
    };
    
    // Clean up maps when modals are closed
    document.addEventListener('close-modal', function(e) {
        if (!e.detail || typeof e.detail !== 'string') return;
        
        try {
            const id = e.detail.replace('view-crime-', '');
            if (maps.has(id)) {
                maps.get(id).remove();
                maps.delete(id);
            }
        } catch (error) {
            console.error('Error cleaning up map:', error);
        }
    });
}

/**
 * Setup dropdown menu behavior
 */
function setupDropdowns() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        try {
            // Find all open dropdowns
            document.querySelectorAll('[x-data]').forEach(dropdown => {
                // Check if this is an Alpine component with an 'open' property
                if (dropdown.__x && 
                    dropdown.__x.$data && 
                    dropdown.__x.$data.open === true && 
                    !dropdown.contains(e.target)) {
                    
                    // Close the dropdown
                    dropdown.__x.$data.open = false;
                }
            });
        } catch (error) {
            // Silently fail as this is just a UI enhancement
        }
    });
}