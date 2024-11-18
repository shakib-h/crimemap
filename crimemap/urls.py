# project/urls.py

from django.contrib import admin
from django.urls import path, include
from django.contrib.auth import views as auth_views
from app import views

urlpatterns = [
    path('admin/', admin.site.urls),  # Admin panel
    path('accounts/login/', views.login_view, name='login'),  # Custom login view
    path('accounts/register/', views.register_view, name='register'),  # Custom register view
    path('accounts/logout/', auth_views.LogoutView.as_view(), name='logout'),  # Default logout view
    path('accounts/password_reset/', auth_views.PasswordResetView.as_view(), name='password_reset'),
    path('accounts/password_reset/done/', auth_views.PasswordResetDoneView.as_view(), name='password_reset_done'),
    path('accounts/reset/<uidb64>/<token>/', auth_views.PasswordResetConfirmView.as_view(), name='password_reset_confirm'),
    path('accounts/reset/done/', auth_views.PasswordResetCompleteView.as_view(), name='password_reset_complete'),
    
    path('dashboard/', views.dashboard, name='dashboard'),  # Dashboard for authenticated users
    path('', include('app.urls')),  # Include app URLs
]
