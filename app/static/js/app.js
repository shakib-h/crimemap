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

function getMarkerColor(crimeType) {
    const colorMapping = {
        'Theft': 'green',
        'Fraud': 'green',
        'Harassment': 'lightgreen',
        'Vandalism': 'green',
        'Hacking': 'blue',
        'Burglary': 'orange',
        'Assault': 'orange',
        'Embezzlement': 'orange',
        'Extortion': 'orange',
        'Domestic Violence': 'orange',
        'Robbery': 'red',
        'Arson': 'red',
        'Kidnapping': 'red',
        'Manslaughter': 'darkred',
        'Sexual Assault': 'darkred',
        'Murder': 'darkred',
        'Terrorism': 'darked',
        'Drug trafficking': 'darked',
        'Corruption': 'darked'
    };
    return colorMapping[crimeType] || 'grey';
}


function addCrimeMarkers() {
    crimeReports.forEach(report => {
        const fields = report.fields;
        const coordinates = [fields.latitude, fields.longitude];
        const crimeType = fields.type;

        if (
            fields.latitude !== undefined &&
            fields.longitude >= -180 &&
            fields.longitude <= 180
        ) {
            L.circleMarker(coordinates, {
                radius: 18,
                color: getMarkerColor(fields.type),
                fillColor: getMarkerColor(fields.type),
                fillOpacity: 0.4,
            })
                .addTo(map)
                .bindPopup(
                    `<b>Crime Type:</b> ${fields.type}<br>
                     <b>Address:</b> ${fields.address}<br>
                     <b>Description:</b> ${fields.description}<br>
                     <b>Date & Time:</b> ${new Date(fields.date_time).toLocaleString()}<br>
                     <b>Status:</b> ${fields.status}`
                );
        } else {
            console.error(`Invalid coordinates for report:`, report);
        }

    });
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

const submitUrl = reportForm.getAttribute('data-submit-url');

reportForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(reportForm);

    fetch(submitUrl, {
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


const locationSearch = document.getElementById('locationSearch');
const locationSuggestions = document.getElementById('locationSuggestions');

function getLocationSuggestions(query) {
    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`;

    fetch(url)
        .then(response => response.json())
        .then(data => displayLocationSuggestions(data))
        .catch(error => console.log('Error fetching location suggestions:', error));
}

function displayLocationSuggestions(suggestions) {
    locationSuggestions.innerHTML = '';

    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
            const suggestionElement = document.createElement('div');
            suggestionElement.classList.add('suggestion-item');
            suggestionElement.textContent = suggestion.display_name;
            suggestionElement.addEventListener('click', function () {
                selectLocationSuggestion(suggestion);
            });
            locationSuggestions.appendChild(suggestionElement);
        });
    } else {
        locationSuggestions.innerHTML = '<p>No suggestions found</p>';
    }
}

function selectLocationSuggestion(suggestion) {
    locationSearch.value = suggestion.display_name;
    locationSuggestions.innerHTML = '';
    setMapView(suggestion.lat, suggestion.lon, 13, true);
}

locationSearch.addEventListener('input', function () {
    const query = locationSearch.value;
    if (query.length > 2) {
        getLocationSuggestions(query);
    } else {
        locationSuggestions.innerHTML = '';
    }
});


document.addEventListener('click', function (e) {
    if (!locationSearch.contains(e.target) && !locationSuggestions.contains(e.target)) {
        locationSuggestions.innerHTML = '';
    }
});


const crimeTypeFilter = document.getElementById('crimeTypeFilter');
const dateFilter = document.getElementById('dateFilter');
const applyFilters = document.getElementById('applyFilters');
const resetFilterButton = document.getElementById('resetFilter');


function resetFilters() {
    
    crimeTypeFilter.value = "";
    dateFilter.value = "";

    
    map.eachLayer((layer) => {
        if (layer instanceof L.CircleMarker) {
            map.removeLayer(layer);
        }
    });

    
    crimeReports.forEach((report) => {
        const fields = report.fields;
        const coordinates = [fields.latitude, fields.longitude];
        if (
            fields.latitude !== undefined &&
            fields.longitude >= -180 &&
            fields.longitude <= 180
        ) {
            L.circleMarker(coordinates, {
                radius: 18,
                color: getMarkerColor(fields.type),
                fillColor: getMarkerColor(fields.type),
                fillOpacity: 0.4,
            })
                .addTo(map)
                .bindPopup(
                    `<b>Crime Type:</b> ${fields.type}<br>
                     <b>Address:</b> ${fields.address}<br>
                     <b>Description:</b> ${fields.description}<br>
                     <b>Date & Time:</b> ${new Date(fields.date_time).toLocaleString()}<br>
                     <b>Status:</b> ${fields.status}`
                );
        }
    });
}


resetFilterButton.addEventListener('click', resetFilters);


function filterCrimeMarkers() {
    
    const selectedCrimeType = crimeTypeFilter.value;
    const selectedDate = dateFilter.value;

    
    map.eachLayer((layer) => {
        if (layer instanceof L.CircleMarker) {
            map.removeLayer(layer);
        }
    });

    
    crimeReports.forEach((report) => {
        const fields = report.fields;
        const crimeDate = new Date(fields.date_time).toISOString().split('T')[0];

        const matchesType = selectedCrimeType ? fields.type === selectedCrimeType : true;
        const matchesDate = selectedDate ? crimeDate === selectedDate : true;

        if (matchesType && matchesDate) {
            const coordinates = [fields.latitude, fields.longitude];
            if (
                fields.latitude !== undefined &&
                fields.longitude >= -180 &&
                fields.longitude <= 180
            ) {
                L.circleMarker(coordinates, {
                    radius: 18,
                    color: getMarkerColor(fields.type),
                    fillColor: getMarkerColor(fields.type),
                    fillOpacity: 0.4,
                })
                    .addTo(map)
                    .bindPopup(
                        `<b>Crime Type:</b> ${fields.type}<br>
                         <b>Address:</b> ${fields.address}<br>
                         <b>Description:</b> ${fields.description}<br>
                         <b>Date & Time:</b> ${new Date(fields.date_time).toLocaleString()}<br>
                         <b>Status:</b> ${fields.status}`
                    );
            }
        }
    });
}


applyFilters.addEventListener('click', filterCrimeMarkers);
