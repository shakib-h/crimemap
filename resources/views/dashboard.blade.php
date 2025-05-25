<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ Auth::user()->role->name }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ Auth::user()->isAdmin() || Auth::user()->isModerator() ? 'All Reports' : 'Your Reports' }}
                        </h3>
                        
                        <div class="flex gap-4">
                            <select 
                                id="type-filter" 
                                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                onchange="filterCrimes()"
                            >
                                <option value="">All Types</option>
                                @foreach($crimeTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>

                            <select 
                                id="status-filter" 
                                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                onchange="filterCrimes()"
                            >
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto ring-1 ring-gray-300 dark:ring-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                                @forelse($crimes as $crime)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $crime->crimeType->color }}"></span>
                                                <span class="text-gray-900 dark:text-gray-200">{{ $crime->crimeType->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-gray-200">{{ $crime->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span @class([
                                                'px-2 py-1 text-xs rounded-full inline-flex items-center',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200' => $crime->status === 'pending',
                                                'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' => $crime->status === 'approved',
                                                'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' => $crime->status === 'rejected',
                                            ])>
                                                {{ ucfirst($crime->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-gray-700 dark:text-gray-300">{{ $crime->created_at->format('M d, Y') }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $crime->created_at->format('h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <button 
                                                    type="button" 
                                                    @click="$dispatch('open-modal', 'view-crime-{{ $crime->id }}')" 
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-800/70 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    View
                                                </button>
                                                
                                                @if(Auth::user()->isAdmin() || Auth::user()->isModerator())
                                                    <div x-data="{ open: false }" class="relative" x-cloak>
                                                        <button
                                                            @click="open = !open"
                                                            type="button"
                                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            </svg>
                                                        </button>
                                                        
                                                        <div
                                                            x-show="open"
                                                            @click.away="open = false"
                                                            class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50"
                                                            x-transition:enter="transition ease-out duration-100"
                                                            x-transition:enter-start="transform opacity-0 scale-95"
                                                            x-transition:enter-end="transform opacity-100 scale-100"
                                                            x-transition:leave="transition ease-in duration-75"
                                                            x-transition:leave-start="transform opacity-100 scale-100"
                                                            x-transition:leave-end="transform opacity-0 scale-95"
                                                        >
                                                            <div class="py-1">
                                                                <form action="{{ route('crimes.moderate', $crime) }}" method="POST" class="block w-full text-left">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="pending">
                                                                    <button type="submit" class="w-full px-4 py-2 text-sm text-yellow-700 dark:text-yellow-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
                                                                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span>
                                                                        Pending
                                                                    </button>
                                                                </form>
                                                                
                                                                <form action="{{ route('crimes.moderate', $crime) }}" method="POST" class="block w-full text-left">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="approved">
                                                                    <button type="submit" class="w-full px-4 py-2 text-sm text-green-700 dark:text-green-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
                                                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                                                        Approve
                                                                    </button>
                                                                </form>
                                                                
                                                                <form action="{{ route('crimes.moderate', $crime) }}" method="POST" class="block w-full text-left">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="rejected">
                                                                    <button type="submit" class="w-full px-4 py-2 text-sm text-red-700 dark:text-red-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
                                                                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                                                                        Reject
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No crime reports found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $crimes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- View Crime Modals --}}
    @foreach($crimes as $crime)
        <div x-cloak>
            <x-modal name="view-crime-{{ $crime->id }}" :show="false" maxWidth="md">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $crime->title }}</h2>
                        <span @class([
                            'px-2 py-1 text-xs rounded-full',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200' => $crime->status === 'pending',
                            'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' => $crime->status === 'approved',
                            'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' => $crime->status === 'rejected',
                        ])>
                            {{ ucfirst($crime->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</p>
                            <div class="flex items-center mt-1">
                                <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $crime->crimeType->color }}"></span>
                                <span class="text-gray-900 dark:text-gray-200">{{ $crime->crimeType->name }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Reported by</p>
                            <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $crime->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Reported on</p>
                            <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $crime->created_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                        @if($crime->moderated_by)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Last moderated by</p>
                                <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $crime->moderator->name ?? 'Unknown' }} ({{ $crime->moderated_at->diffForHumans() }})</p>
                            </div>
                        @endif
                    </div>

                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</p>
                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-md">
                            <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $crime->description }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Location</p>
                        <div id="view-map-{{ $crime->id }}" class="h-60 w-full rounded-md bg-gray-100 dark:bg-gray-900 overflow-hidden"></div>
                    </div>

                    <div class="flex justify-between">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            Close
                        </x-secondary-button>
                        
                        @if(Auth::user()->isAdmin() || Auth::user()->isModerator())
                            <div class="flex space-x-2">
                                @if($crime->status !== 'approved')
                                    <form action="{{ route('crimes.moderate', $crime) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all">
                                            Approve
                                        </button>
                                    </form>
                                @endif
                                
                                @if($crime->status !== 'rejected')
                                    <form action="{{ route('crimes.moderate', $crime) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all">
                                            Reject
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </x-modal>
        </div>
    @endforeach

    @push('scripts')
    <script>
        // Modal map initialization for crime details
        document.addEventListener('DOMContentLoaded', () => {
            // Setup event listeners for each crime modal
            @foreach($crimes as $crime)
            document.addEventListener('open-modal', function(e) {
                if (e.detail === 'view-crime-{{ $crime->id }}') {
                    initializeViewMap(
                        '{{ $crime->id }}', 
                        {{ $crime->latitude }}, 
                        {{ $crime->longitude }}, 
                        '{{ $crime->crimeType->color }}'
                    );
                }
            });
            @endforeach
        });

        // Temporary fallback if dashboard.js isn't loaded yet
        if (typeof filterCrimes !== 'function') {
            function filterCrimes() {
                try {
                    const typeFilter = document.getElementById('type-filter')?.value || '';
                    const statusFilter = document.getElementById('status-filter')?.value || '';
                    
                    // Create fresh URL
                    const url = new URL(window.location.pathname, window.location.origin);
                    
                    // Add parameters conditionally (removed sort parameter)
                    if (typeFilter) {
                        url.searchParams.append('type', typeFilter);
                    }
                    
                    if (statusFilter) {
                        url.searchParams.append('status', statusFilter);
                    }
                    
                    // Navigate to filtered URL
                    window.location.href = url.toString();
                } catch (error) {
                    console.error('Error applying filters:', error);
                }
            }
            
            // Initialize filter values
            document.addEventListener('DOMContentLoaded', () => {
                try {
                    const params = new URLSearchParams(window.location.search);
                    
                    const typeSelect = document.getElementById('type-filter');
                    if (typeSelect && params.has('type')) {
                        typeSelect.value = params.get('type');
                    }
                    
                    const statusSelect = document.getElementById('status-filter');
                    if (statusSelect && params.has('status')) {
                        statusSelect.value = params.get('status');
                    }
                } catch (error) {
                    console.error('Error initializing filters:', error);
                }
            });
            
            // Map initialization function
            function initializeViewMap(id, lat, lng, color) {
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        try {
                            const mapId = 'view-map-' + id;
                            const container = document.getElementById(mapId);
                            
                            if (!container) return;
                            
                            const viewMap = L.map(mapId, {
                                zoomControl: true,
                                attributionControl: false
                            }).setView([lat, lng], 15);
                            
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19
                            }).addTo(viewMap);
                            
                            L.circle([lat, lng], {
                                radius: 100,
                                color: color,
                                fillColor: color,
                                fillOpacity: 0.5
                            }).addTo(viewMap);
                            
                            viewMap.invalidateSize();
                        } catch(error) {
                            console.error('Error initializing view map:', error);
                        }
                    }, 300); // Delay to ensure modal is fully visible
                });
            }
        }
    </script>
    @endpush
</x-app-layout>
