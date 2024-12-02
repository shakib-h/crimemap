from django.contrib.auth.models import AbstractUser
from django.db import models

class appUser(AbstractUser):
    role = models.CharField(max_length=10, choices=[('Admin', 'Admin'), ('User', 'User')], default='User')

    # Optional: Override the default related_name to avoid clashes
    groups = models.ManyToManyField(
        'auth.Group',
        related_name='custom_user_set',  # You can change this related_name to whatever you prefer
        blank=True
    )
    user_permissions = models.ManyToManyField(
        'auth.Permission',
        related_name='custom_user_permissions_set',  # Similarly, change this related_name as needed
        blank=True
    )

    def __str__(self):
        return self.username
class Location(models.Model):
    location_id = models.AutoField(primary_key=True)
    city = models.CharField(max_length=100)
    division = models.CharField(max_length=100, db_index=True)

    def __str__(self):
        return f"{self.city}, {self.division}"

class Crime(models.Model):
    crime_id = models.AutoField(primary_key=True)
    type = models.CharField(max_length=100, choices=[
        ('Theft', 'Theft'),
        ('Murder', 'Murder'),
        ('Burglary', 'Burglary'),
        ('Assault', 'Assault'),
        ('Robbery', 'Robbery'),
        ('Vandalism', 'Vandalism'),
        ('Fraud', 'Fraud'),
        ('Drug trafficking', 'Drug trafficking'),
        ('Kidnapping', 'Kidnapping'),
        ('Sexual Assault', 'Sexual Assault'),
        ('Arson', 'Arson'),
        ('Harassment', 'Harassment'),
        ('Domestic Violence', 'Domestic Violence'),
        ('Hacking', 'Hacking'),
        ('Embezzlement', 'Embezzlement'),
        ('Corruption', 'Corruption'),
        ('Terrorism', 'Terrorism'),
        ('Extortion', 'Extortion'),
        ('Manslaughter', 'Manslaughter'),
    ])
    description = models.TextField()
    date_time = models.DateTimeField()
    address = models.CharField(max_length=100)
    latitude = models.DecimalField(
        max_digits=9, 
        decimal_places=6, 
        default=0.000000
    )
    longitude = models.DecimalField(
        max_digits=9, 
        decimal_places=6, 
        default=0.000000
    )
    location = models.ForeignKey(Location, on_delete=models.CASCADE, db_constraint=False)
    status = models.CharField(max_length=10, choices=[
        ('Pending', 'Pending'),
        ('Verified', 'Verified'),
        ('Resolved', 'Resolved'),
    ], default='Pending')
    user = models.ForeignKey(appUser, on_delete=models.CASCADE, default='')
    def __str__(self):
        return f"{self.type} - {self.status}"
