<?php

namespace App\Http\Controllers;

use App\Models\Crime;
use App\Models\CrimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cache crime types (they rarely change)
        $crimeTypes = Cache::remember('crime-types', 60 * 24, function () {
            return CrimeType::all();
        });

        // Build query with eager loading
        $query = Crime::with(['crimeType', 'user'])
            ->select('crimes.*'); // Ensure we select all fields

        // Filter by user role - only show user's own crimes if they're not admin/mod
        if (!$user->role_id === 3 && !$user->role_id === 2) {
            $query->where('user_id', $user->id);
        }

        // Apply type filter
        if ($request->filled('type')) {
            $query->where('crime_type_id', $request->type);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply sorting (default: newest first)
        $query->latest('created_at');

        // Get paginated results
        $crimes = $query->paginate(15)->withQueryString();

        return view('dashboard', compact('crimes', 'crimeTypes'));
    }
}
