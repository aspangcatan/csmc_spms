@extends('layouts.app')

@section('title', 'SPMS Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- 🔹 Role-Based Greeting & Filters --}}
    <div class="mb-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                @if(auth()->user()->isSupervisor())
                    Rater Dashboard
                @else
                    Employee Dashboard
                @endif
            </h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.2em] mt-2">
                Strategic Performance Management System <span class="mx-2 text-gray-200">|</span> 
                <span class="text-orange-500">Live Database Feed</span>
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <div class="bg-white px-4 py-2 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Year</p>
                    <select id="dashboardYear" class="bg-transparent border-0 p-0 text-xs font-black text-gray-900 focus:ring-0 cursor-pointer">
                        @for($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="h-6 w-px bg-gray-100"></div>
                <div class="flex items-center gap-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Semester</p>
                    <select id="dashboardSemester" class="bg-transparent border-0 p-0 text-xs font-black text-gray-900 focus:ring-0 cursor-pointer">
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }}>1st Sem</option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }}>2nd Sem</option>
                    </select>
                </div>
            </div>
            
            <button onclick="applyFilters()" class="btn btn-orange px-6 py-2.5 text-[10px] uppercase font-black tracking-widest shadow-lg shadow-orange-500/20">
                Update View
            </button>
        </div>
    </div>

    {{-- 🔹 Key Metrics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Period Rating (Affected by Filters) --}}
        <div class="card-modern p-6 relative overflow-hidden group">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-orange-50 rounded-full blur-2xl group-hover:bg-orange-100 transition-colors"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Rating ({{ $year }} S{{ $semester }})</p>
            <div class="flex items-end gap-2">
                <h3 class="text-3xl font-black text-gray-900 leading-none">
                    {{ number_format($latestIpcr->final_rating ?? 0, 2) }}
                </h3>
                <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">
                    {{ $latestIpcr->final_rating_adjective ?? 'N/A' }}
                </span>
            </div>
            <p class="text-[10px] text-gray-400 mt-4 border-t border-gray-50 pt-4">
                {{ $latestIpcr ? "Validated score for this period" : 'No record for this period' }}
            </p>
        </div>

        {{-- Submission Status for Filtered Period --}}
        <div class="card-modern p-6 relative overflow-hidden group">
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-blue-50 rounded-full blur-2xl group-hover:bg-blue-100 transition-colors"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Period Status ({{ $year }} S{{ $semester }})</p>
            <div class="flex items-center gap-3">
                @php
                    $status = $filteredIpcr->status ?? 'NOT SUBMITTED';
                    $style = 'bg-gray-100 text-gray-500';
                    if(str_contains($status, 'Approved')) $style = 'bg-emerald-50 text-emerald-600';
                    elseif(str_contains($status, 'Submitted')) $style = 'bg-orange-50 text-orange-600';
                    elseif(str_contains($status, 'Draft')) $style = 'bg-blue-50 text-blue-600';
                @endphp
                <span class="px-3 py-1 rounded-full {{ $style }} text-[10px] font-black uppercase tracking-widest">
                    {{ $status }}
                </span>
            </div>
            <p class="text-[10px] text-gray-400 mt-5 border-t border-gray-50 pt-4">
                {{ $filteredIpcr ? 'Last activity: ' . $filteredIpcr->updated_at->diffForHumans() : 'No activity for this period' }}
            </p>
        </div>

        @if($supervisorStats)
            {{-- Supervisor: Team Compliance --}}
            <div class="card-modern p-6 relative overflow-hidden group">
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-purple-50 rounded-full blur-2xl group-hover:bg-purple-100 transition-colors"></div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Team Compliance ({{ date('Y') }})</p>
                <div class="flex items-end gap-2">
                    <h3 class="text-3xl font-black text-gray-900 leading-none">
                        {{ number_format($supervisorStats['compliance_rate'], 0) }}%
                    </h3>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
                        Submitted
                    </span>
                </div>
                <div class="mt-4 h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-purple-500 transition-all duration-1000" style="width: {{ $supervisorStats['compliance_rate'] }}%"></div>
                </div>
            </div>

            {{-- Supervisor: Pending Actions --}}
            <div class="card-modern p-6 relative overflow-hidden group">
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-red-50 rounded-full blur-2xl group-hover:bg-red-100 transition-colors"></div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Total Pending Now</p>
                <div class="flex items-center gap-4">
                    <h3 class="text-3xl font-black {{ $supervisorStats['pending_approvals'] > 0 ? 'text-red-500' : 'text-gray-900' }} leading-none">
                        {{ $supervisorStats['pending_approvals'] }}
                    </h3>
                    @if($supervisorStats['pending_approvals'] > 0)
                        <a href="{{ route('ipcr.staff') }}" class="text-[10px] font-black text-orange-500 uppercase tracking-widest hover:underline animate-pulse">
                            Needs Review
                        </a>
                    @else
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Clear</span>
                    @endif
                </div>
                <p class="text-[10px] text-gray-400 mt-5 border-t border-gray-50 pt-4">
                    Assigned signature requests
                </p>
            </div>
        @else
            {{-- Employee: Future Modules --}}
            <div class="card-modern p-6 opacity-40">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Upcoming Module</p>
                <div class="flex items-center gap-2">
                    <i class="fas fa-layer-group text-xs text-gray-300"></i>
                    <span class="text-xs font-bold">DPCR Integration</span>
                </div>
            </div>
            <div class="card-modern p-6 opacity-40">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Upcoming Module</p>
                <div class="flex items-center gap-2">
                    <i class="fas fa-sitemap text-xs text-gray-300"></i>
                    <span class="text-xs font-bold">OPCR Integration</span>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        {{-- 🔹 Real Performance Trend Chart --}}
        <div class="lg:col-span-2 card-modern p-8">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Performance History</h3>
                    <p class="text-xs text-gray-400 font-medium">All-time approved rating trends (Continuous)</p>
                </div>
                <div class="flex gap-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Efficiency</span>
                    </div>
                </div>
            </div>
            <div class="h-[300px] w-full">
                @if($history->count() > 0)
                    <canvas id="performanceChart"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-300">
                        <i class="fas fa-chart-line text-4xl mb-4 opacity-20"></i>
                        <p class="text-xs font-bold uppercase tracking-widest">No approved ratings history yet</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 🔹 Side Panels --}}
        <div class="space-y-6">
            @if($supervisorStats)
                {{-- REAL Rating Distribution --}}
                <div class="card-modern p-8">
                    <h3 class="text-sm font-black text-gray-900 tracking-widest uppercase mb-6">Staff Ratings distribution ({{ date('Y') }})</h3>
                    <div class="space-y-5">
                        @php
                            $distColors = [
                                'Outstanding' => 'bg-emerald-500', 
                                'Very Satisfactory' => 'bg-blue-500', 
                                'Satisfactory' => 'bg-orange-500',
                                'Unsatisfactory' => 'bg-yellow-500',
                                'Poor' => 'bg-red-500'
                            ];
                            $textColors = [
                                'Outstanding' => 'text-emerald-500', 
                                'Very Satisfactory' => 'text-blue-500', 
                                'Satisfactory' => 'text-orange-500',
                                'Unsatisfactory' => 'text-yellow-500',
                                'Poor' => 'text-red-500'
                            ];
                            $totalStaff = $supervisorStats['staff_count'];
                        @endphp
                        
                        @foreach($supervisorStats['distribution'] as $adj => $count)
                            <div class="space-y-2">
                                <div class="flex justify-between text-[10px] font-black uppercase tracking-widest">
                                    <span class="{{ $textColors[$adj] }}">{{ $adj }}</span>
                                    <span class="text-gray-400">{{ $count }} Staff</span>
                                </div>
                                <div class="h-1.5 w-full bg-gray-50 rounded-full overflow-hidden">
                                    <div class="h-full {{ $distColors[$adj] }}" style="width: {{ $totalStaff > 0 ? ($count / $totalStaff) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <button class="w-full mt-8 py-4 px-6 bg-gray-900 text-white rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-gray-200"
                            onclick="window.location.href='{{ route('ipcr.staff') }}'">
                        Review Team Data
                    </button>
                </div>
            @else
                {{-- Employee Action Center --}}
                <div class="card-modern p-8 bg-gray-900 text-white relative overflow-hidden">
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-orange-500/20 rounded-full blur-3xl"></div>
                    <h3 class="text-sm font-black tracking-widest uppercase mb-6 relative z-10">Quick Action</h3>
                    <div class="relative z-10">
                        @if($globalIpcr)
                            <p class="text-2xl font-black mb-2 italic">
                                @php
                                    $gStatus = $globalIpcr->status;
                                    if(str_contains($gStatus, 'Approved')) echo 'Current Approved';
                                    elseif(str_contains($gStatus, 'Submitted')) echo 'Pending Review';
                                    else echo $gStatus;
                                @endphp
                            </p>
                            <p class="text-xs text-gray-400 font-medium leading-relaxed mb-6">
                                Your latest IPCR ({{ $globalIpcr->year }} S{{ $globalIpcr->semester }}) is current.
                            </p>
                            <button class="py-3 px-6 bg-white text-black rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-gray-100 transition-all shadow-lg"
                                    onclick="window.location.href='/ipcr'">
                                View Current
                            </button>
                        @else
                            <p class="text-2xl font-black mb-2 italic">Get Started</p>
                            <p class="text-xs text-gray-400 font-medium leading-relaxed mb-6">
                                You haven't created any IPCR records yet. start your performance journey.
                            </p>
                            <button class="py-3 px-6 bg-orange-500 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20"
                                    onclick="window.location.href='/ipcr'">
                                Create First IPCR
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function applyFilters() {
        const year = document.getElementById('dashboardYear').value;
        const sem = document.getElementById('dashboardSemester').value;
        window.location.href = `{{ route('dashboard.new') }}?year=${year}&semester=${sem}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const historyData = @json($history);
        
        if (historyData.length > 0) {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            
            let gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(240, 106, 56, 0.2)');
            gradient.addColorStop(1, 'rgba(240, 106, 56, 0)');

            const labels = historyData.map(h => `${h.year}-S${h.semester}`);
            const ratings = historyData.map(h => h.final_rating);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Official Rating',
                        data: ratings,
                        borderColor: '#f06a38',
                        borderWidth: 4,
                        fill: true,
                        backgroundColor: gradient,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#f06a38',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a1e23',
                            padding: 12,
                            titleFont: { size: 10, weight: 'bold' },
                            bodyFont: { size: 12, weight: '900' },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rating: ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10, weight: 'bold' }, color: '#9ca3af' }
                        },
                        y: {
                            min: 0,
                            max: 5,
                            grid: { color: '#f3f4f6', drawBorder: false },
                            ticks: { font: { size: 10, weight: 'bold' }, color: '#9ca3af' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
