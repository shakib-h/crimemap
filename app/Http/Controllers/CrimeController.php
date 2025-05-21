<?php

namespace App\Http\Controllers;

use App\Models\Crime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrimeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'crime_type_id' => ['required', 'exists:crime_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $crime = Crime::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'pending',
            'incident_date' => now(),
        ]);

        return redirect()->route('map')
            ->with('success', 'Crime report submitted successfully and pending review.');
    }
}
