<?php

namespace App\Http\Controllers;

use App\Models\Crime;
use App\Models\CrimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $crimeTypes = CrimeType::all();
        
        $crimes = Crime::with(['crimeType', 'user'])
            ->when($user->role_id === 3, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('crime_type_id', $request->type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString(); // This preserves filters in pagination links

        return view('dashboard', compact('crimes', 'crimeTypes'));
    }
}