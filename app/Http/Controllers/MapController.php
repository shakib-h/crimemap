<?php

namespace App\Http\Controllers;

use App\Models\Crime;
use App\Models\CrimeType;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $crimeTypes = CrimeType::all();
        $crimes = Crime::with(['crimeType', 'user'])
            ->where('status', 'approved')
            ->get();

        return view('map', compact('crimes', 'crimeTypes'));
    }
}