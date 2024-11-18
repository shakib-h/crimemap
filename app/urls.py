

from django.urls import path
from . import views

urlpatterns = [
    path('', views.map_view, name='map_view'),  # Home or map view
    path('dashboard/', views.dashboard, name='dashboard'),  # Dashboard for users
    path('submit_report/', views.submit_report, name='submit_report'),
]
