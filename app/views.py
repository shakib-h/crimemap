from django.shortcuts import render
from itertools import chain
from django.core.serializers import serialize
from django.contrib.auth.decorators import login_required
from django.http import JsonResponse
from django.shortcuts import render, redirect
from django.contrib.auth import login, authenticate
from django.contrib.auth.models import User
from .forms import LoginForm, RegistrationForm
from django.contrib import messages
from django.utils import timezone
from django.http import JsonResponse
from .models import Crime, Location
from .db_router import DivisionRouter

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
                return redirect('dashboard')
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
            messages.success(
                request, 'Registration successful. You can now log in.')
            return redirect('login')
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

    databases = ['default', 'dhaka', 'chittagong']

    crime_reports = []

    for db_alias in databases:
        crime_reports.extend(Crime.objects.using(db_alias).all())

    crime_reports_json = serialize('json', crime_reports)

    locations = Location.objects.using('default').all()

    crime_type_choices = Crime._meta.get_field('type').choices

    context = {
        'crime_reports_json': crime_reports_json,
        'locations': locations,
        'crime_type_choices': crime_type_choices,
    }

    return render(request, 'home.html', context)


router = DivisionRouter()


def submit_report(request):
    if request.method == 'POST':
        try:

            crime_type = request.POST.get('type')
            description = request.POST.get('description')
            location_id = request.POST.get('location')
            address = request.POST.get('address')
            latitude = request.POST.get('latitude')
            longitude = request.POST.get('longitude')
            user_id = request.user.id
            location = Location.objects.get(location_id=location_id)
            division = location.division

            db = 'default'

            if division == 'Dhaka':
                db = 'dhaka'
            elif division == 'Chattogram' | division == 'Chittagong':
                db = 'chittagong'

            crime = Crime.objects.using(db).create(
                type=crime_type,
                description=description,
                date_time=timezone.now(),
                address=address,
                latitude=latitude,
                longitude=longitude,
                location=location,
                user_id=user_id
            )

            return JsonResponse({'success': True, 'crime_id': crime.crime_id})

        except Location.DoesNotExist:
            return JsonResponse({'success': False, 'error': 'Location not found.'})
        except Exception as e:
            print(f"Error: {str(e)}")
            return JsonResponse({'success': False, 'error': str(e)})

    return JsonResponse({'success': False, 'error': 'Invalid request method.'})
