export function initializeDashboard() {
    // Filter functionality
    window.filterCrimes = function() {
        const typeFilter = document.getElementById('type-filter').value;
        const statusFilter = document.getElementById('status-filter').value;
        const currentUrl = new URL(window.location.href);
        
        if (typeFilter) {
            currentUrl.searchParams.set('type', typeFilter);
        } else {
            currentUrl.searchParams.delete('type');
        }
        
        if (statusFilter) {
            currentUrl.searchParams.set('status', statusFilter);
        } else {
            currentUrl.searchParams.delete('status');
        }

        window.location.href = currentUrl.toString();
    };

    // Set initial filter values
    const params = new URLSearchParams(window.location.search);
    
    if (params.has('type')) {
        document.getElementById('type-filter').value = params.get('type');
    }
    
    if (params.has('status')) {
        document.getElementById('status-filter').value = params.get('status');
    }
}