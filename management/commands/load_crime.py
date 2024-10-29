import random
import json

# Define some random locations in Dhaka with their latitude and longitude
locations = [
    {"location": "Mohammadpur", "latitude": 23.7663, "longitude": 90.3652},
    {"location": "Dhanmondi", "latitude": 23.7466, "longitude": 90.3946},
    {"location": "Gulshan", "latitude": 23.7911, "longitude": 90.4105},
    {"location": "Banani", "latitude": 23.7864, "longitude": 90.3970},
    {"location": "Mirpur", "latitude": 23.8437, "longitude": 90.3648},
    {"location": "Uttara", "latitude": 23.8831, "longitude": 90.3949},
    {"location": "Tejgaon", "latitude": 23.7598, "longitude": 90.4052},
    {"location": "Bashundhara", "latitude": 23.8235, "longitude": 90.4250},
    {"location": "Motijheel", "latitude": 23.7475, "longitude": 90.3932},
    {"location": "Khilgaon", "latitude": 23.8172, "longitude": 90.4212}
]

crime_types = ["Robbery", "Theft", "Assault", "Fraud", "Vandalism"]
num_reports = 100  # Number of reports to generate

crime_reports = []

for _ in range(num_reports):
    location = random.choice(locations)
    intensity = random.randint(1, 5)  # Intensity from 1 to 5
    crime_reports.append({
        "model": "app.crimereport",
        "pk": _ + 1,  # Primary Key, assuming starting from 1
        "fields": {
            "location": location["location"],
            "crime_type": random.choice(crime_types),
            "intensity": intensity,
            "latitude": location["latitude"],
            "longitude": location["longitude"]
        }
    })

# Save to a JSON file
with open('dummy_crime_reports.json', 'w') as f:
    json.dump(crime_reports, f, indent=4)

print("Dummy data generated and saved to 'dummy_crime_reports.json'.")
