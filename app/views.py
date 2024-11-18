from datetime import timezone
import json
from django.core.serializers import serialize
from django.contrib.auth.decorators import login_required
from django.http import JsonResponse
from django.shortcuts import render, redirect
from django.contrib.auth import login, authenticate
from django.contrib.auth.models import User
from .forms import LoginForm, RegistrationForm
from django.contrib import messages

from app.models import Crime, Location

def login_view(request):
    if request.method == 'POST':
        form = LoginForm(request, data=request.POST)
        if form.is_valid():
            username = form.cleaned_data['username']
            password = form.cleaned_data['password']
            user = authenticate(username=username, password=password)
            if user is not None:
                login(request, user)
                return redirect('dashboard')  # Redirect to homepage or desired page
            else:
                messages.error(request, 'Invalid username or password')
    else:
        form = LoginForm()

    return render(request, 'auth/login.html', {'form': form})

def register_view(request):
    if request.method == 'POST':
        form = RegistrationForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(request, 'Registration successful. You can now log in.')
            return redirect('login')  # Redirect to login page after successful registration
    else:
        form = RegistrationForm()

    return render(request, 'auth/register.html', {'form': form})

@login_required
def dashboard(request):
    context = {}
    if request.user.is_staff:
        context['is_admin'] = True
    else:
        context['is_admin'] = False
    return render(request, 'dashboard.html', context)

def map_view(request):
    crime_reports = Crime.objects.all()
    crime_reports_json = serialize('json', crime_reports)
    locations = Location.objects.all()
    crime_type_choices = Crime._meta.get_field('type').choices
    context = {
        'crime_reports_json': crime_reports_json,
        'locations': locations,
        'crime_type_choices': crime_type_choices
    }
    return render(request, 'home.html', context)

def submit_report(request):
    if request.method == 'POST':
        # Get form data
        crime_type = request.POST.get('type')
        description = request.POST.get('description')
        location_id = request.POST.get('location')
        address = request.POST.get('address')
        latitude = request.POST.get('latitude')
        longitude = request.POST.get('longitude')
        user_id = request.user.id  # Assuming the user is logged in

        # Get the location object
        location = Location.objects.get(id=location_id)

        # Create a new Crime report
        crime = Crime.objects.create(
            type=crime_type,
            description=description,
            date_time=timezone.now(),
            address=address,
            latitude=latitude,
            longitude=longitude,
            location=location,
            user_id=user_id
        )

        # Return a success response as JSON
        return JsonResponse({'success': True})

    return JsonResponse({'success': False})