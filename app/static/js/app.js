var map = L.map('map').setView([23.8103, 90.4125], 12);
        var userMarker;

        function addTileLayer() {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
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

        const crimeReports = [
            { location: "Uttara", type: "Theft", intensity: 3, coordinates: [23.8678, 90.3982] },
            { location: "Uttara", type: "Robbery", intensity: 4, coordinates: [23.8678, 90.3982] },
            { location: "Mirpur", type: "Assault", intensity: 5, coordinates: [23.8463, 90.3563] },
            { location: "Mirpur", type: "Burglary", intensity: 2, coordinates: [23.8463, 90.3563] },
            { location: "Mohammadpur", type: "Vandalism", intensity: 5, coordinates: [23.7782, 90.3595] },
            { location: "Mohammadpur", type: "Theft", intensity: 4, coordinates: [23.7782, 90.3595] },
            { location: "Mohammadpur", type: "Assault", intensity: 5, coordinates: [23.7782, 90.3595] },
            { location: "Gulshan", type: "Robbery", intensity: 3, coordinates: [23.7957, 90.4051] },
            { location: "Banani", type: "Theft", intensity: 2, coordinates: [23.7941, 90.4108] },
            { location: "Dhanmondi", type: "Vandalism", intensity: 2, coordinates: [23.7465, 90.3734] },
            { location: "Bashundhara", type: "Assault", intensity: 3, coordinates: [23.8003, 90.4302] },
            { location: "Jatrabari", type: "Robbery", intensity: 4, coordinates: [23.7710, 90.4338] },
            { location: "Khilkhet", type: "Theft", intensity: 3, coordinates: [23.8632, 90.4146] },
            { location: "Mirpur", type: "Assault", intensity: 3, coordinates: [23.8436, 90.3563] },
            { location: "Paltan", type: "Burglary", intensity: 3, coordinates: [23.7584, 90.3898] },
            { location: "Bashundhara", type: "Vandalism", intensity: 4, coordinates: [23.8053, 90.4234] },
            { location: "Motijheel", type: "Robbery", intensity: 5, coordinates: [23.7586, 90.3895] },
            { location: "Sadarghat", type: "Theft", intensity: 4, coordinates: [23.7184, 90.3984] },
            { location: "Khilkhet", type: "Assault", intensity: 2, coordinates: [23.8687, 90.4133] },
            { location: "Shahbag", type: "Vandalism", intensity: 3, coordinates: [23.7433, 90.3889] },
            { location: "Tejgaon", type: "Burglary", intensity: 4, coordinates: [23.7728, 90.4113] },
            { location: "Gulshan", type: "Assault", intensity: 3, coordinates: [23.7865, 90.3993] }
        ];

        function addCrimeMarkers() {
            crimeReports.forEach(report => {
                const { coordinates, intensity } = report;
                L.circleMarker(coordinates, {
                    radius: 18,
                    color: getMarkerColor(intensity),
                    fillColor: getMarkerColor(intensity),
                    fillOpacity: 0.2
                }).addTo(map).bindPopup(`${report.type} reported in ${report.location} (Intensity: ${intensity})`);
            });
        }

        function getMarkerColor(intensity) {
            if (intensity === 1) return 'green';
            if (intensity === 2) return 'yellow';
            if (intensity === 3) return 'orange';
            if (intensity === 4) return 'red';
            if (intensity === 5) return 'darkred';
        }

        addTileLayer();
        getLocation();
        addCrimeMarkers();

        document.getElementById('locate-btn').addEventListener('click', function() {
            getLocation();
        });