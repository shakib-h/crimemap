from django.shortcuts import render
from django.contrib.auth.decorators import login_required

from app.models import CrimeReport

@login_required
def dashboard(request):
    context = {}
    if request.user.is_staff:
        # Admin-specific data can be added here
        context['is_admin'] = True
    else:
        # User-specific data can be added here
        context['is_admin'] = False
    return render(request, 'dashboard.html', context)

def map_view(request):
    crime_reports = CrimeReport.objects.all()
    context = {
        'crime_reports': crime_reports,
    }
    return render(request, 'home.html', context)