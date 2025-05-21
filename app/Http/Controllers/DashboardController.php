<?php

namespace App\Http\Controllers;

use App\Models\Crime;
use App\Models\CrimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $crimeTypes = CrimeType::all();
        
        $crimes = Crime::with(['crimeType', 'user'])
            ->when($user->role_id === 3, function ($query) use ($user) {
                // role_id 3 is for regular users (assuming 1 for admin, 2 for moderator)
                return $query->where('user_id', $user->id);
            })
            ->latest()
            ->paginate(10);

        return view('dashboard', compact('crimes', 'crimeTypes'));
    }
}