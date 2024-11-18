var map = L.map('map').setView([23.8103, 90.4125], 12);
var userMarker;

function addTileLayer() {
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap | © Crime Map'
    }).addTo(map);
}

function setMapView(lat, lng, zoomLevel = 13, showMarker = false) {
    map.setView([lat, lng], zoomLevel);
    if (showMarker) {
        if (userMarker) {
            userMarker.setLatLng([lat, lng]);
        } else {
            userMarker = L.marker([lat, lng]).addTo(map)
                .bindPopup('You are here!')
                .openPopup();
        }
    }
}

function onLocationSuccess(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    setMapView(lat, lng, 13, true);
}

function onLocationError() {
    alert('Unable to retrieve your location. Defaulting to Dhaka.');
    setMapView(23.8103, 90.4125, 13);
}

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(onLocationSuccess, onLocationError);
    } else {
        alert('Geolocation is not supported by this browser.');
        setMapView(23.8103, 90.4125, 13);
    }
}

function addCrimeMarkers() {
    crimeReports.forEach(report => {
        const fields = report.fields;
        const coordinates = [fields.latitude, fields.longitude];
        const intensity = fields.intensity;

        if (fields.latitude !== undefined && fields.longitude >= -180 && fields.longitude <= 180) {
            L.circleMarker(coordinates, {
                radius: 18,
                color: getMarkerColor(intensity),
                fillColor: getMarkerColor(intensity),
                fillOpacity: 0.2
            }).addTo(map).bindPopup(`${fields.crime_type} reported in ${fields.location} (Intensity: ${intensity})`);
        } else {
            console.error(`Invalid coordinates for report:`, report);
        }
    });
}


function getMarkerColor(intensity) {
    if (intensity === 1) return 'green';
    if (intensity === 2) return 'green';
    if (intensity === 3) return 'orange';
    if (intensity === 4) return 'red';
    if (intensity === 5) return 'darkred';
}

addTileLayer();
getLocation();
addCrimeMarkers();

document.getElementById('locate-btn').addEventListener('click', function () {
    getLocation();
});

const addressInput = document.getElementById('address');
    const suggestionsContainer = document.getElementById('address-suggestions');
    const reportForm = document.getElementById('reportForm');

    
    function getAddressSuggestions(query) {
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`;

        fetch(url)
            .then(response => response.json())
            .then(data => displaySuggestions(data))
            .catch(error => console.log('Error fetching suggestions:', error));
    }

    
    function displaySuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';

        if (suggestions.length > 0) {
            suggestions.forEach(suggestion => {
                const suggestionElement = document.createElement('div');
                suggestionElement.classList.add('suggestion-item');
                suggestionElement.textContent = suggestion.display_name;
                suggestionElement.addEventListener('click', function () {
                    selectSuggestion(suggestion);
                });
                suggestionsContainer.appendChild(suggestionElement);
            });
        } else {
            suggestionsContainer.innerHTML = '<p>No suggestions found</p>';
        }
    }

    
    function selectSuggestion(suggestion) {
        addressInput.value = suggestion.display_name;
        document.getElementById('latitude').value = suggestion.lat;
        document.getElementById('longitude').value = suggestion.lon;
        suggestionsContainer.innerHTML = ''; 
    }

    
    addressInput.addEventListener('input', function () {
        const query = addressInput.value;
        if (query.length > 2) {
            getAddressSuggestions(query);
        } else {
            suggestionsContainer.innerHTML = ''; 
        }
    });

    
    reportForm.addEventListener("submit", function (e) {
        e.preventDefault(); 

        
        const formData = new FormData(reportForm);
        
        
        fetch("{% url 'submit_report' %}", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Report submitted successfully');
                
                reportForm.reset();
            } else {
                alert('Error submitting report');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an issue submitting the form');
        });
    });