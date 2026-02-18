@extends('layouts.app')

@section('title', 'Staff SPCR')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Staff Strategic Assessments</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Strategic Performance Management Dashboard (SPCR)</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="bg-white px-4 py-2.5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Fiscal Year</p>
                    <select id="yearFilter" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-pointer">
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="h-8 w-px bg-gray-100"></div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Records</p>
                    <p class="text-sm font-black text-gray-900 leading-none">{{ count($staffData) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions & Search -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="relative flex-grow">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
            <input type="text" id="staffSearch" placeholder="Search division head or division name..." 
                   class="w-full pl-12 pr-5 py-4 bg-white border border-gray-100 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-orange-50 focus:bg-white focus:border-orange-500 outline-none transition-all shadow-sm">
        </div>
        
        <button class="px-6 py-4 bg-gray-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-black hover:shadow-xl hover:shadow-gray-200 transition-all flex items-center justify-center gap-2"
                onclick="window.location.reload()">
            <i class="fas fa-sync-alt rotate-180"></i> Update List
        </button>
    </div>

    <!-- Table Section -->
    <div class="card-modern overflow-hidden border-0 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-8 py-5">Division / Head</th>
                        <th class="px-6 py-5">Rating Period</th>
                        <th class="px-6 py-5">Date Submitted</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-8 py-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="staffTableBody">
                    @forelse($staffData as $data)
                        <tr class="hover:bg-gray-50/50 transition-colors group staff-row">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 border-2 border-white shadow-sm ring-1 ring-gray-100">
                                        <img src="{{ $data['user']->profile_photo_url }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm leading-none mb-1.5 employee-name">{{ $data['user']->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium leading-none department-name">{{ $data['user']->division_name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if($data['spcr'])
                                    <p class="text-xs font-bold text-gray-700">FY {{ $data['spcr']->year }} - {{ $data['spcr']->semester == 1 ? '1st' : '2nd' }} Sem</p>
                                @else
                                    <p class="text-xs font-bold text-gray-300 italic uppercase">Not Started</p>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $data['date_submitted'] }}</p>
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $status = $data['status'];
                                    $style = 'bg-gray-100 text-gray-500';
                                    if(str_contains($status, 'Target Approved') || str_contains($status, 'PMT Approved')) $style = 'bg-emerald-50 text-emerald-600 ring-emerald-100';
                                    elseif(str_contains($status, 'Submitted')) $style = 'bg-orange-50 text-orange-600 ring-orange-100';
                                    elseif(str_contains($status, 'Draft')) $style = 'bg-blue-50 text-blue-600 ring-blue-100';
                                    elseif(str_contains($status, 'Approved')) $style = 'bg-purple-50 text-purple-600 ring-purple-100';
                                @endphp
                                <span class="inline-flex items-center py-1.5 px-3 rounded-full {{ $style }} text-[10px] font-black uppercase tracking-widest ring-1 ring-inset">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($data['spcr'])
                                        <button onclick="viewSpcr({{ $data['spcr']->id }})" 
                                                class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-orange-500 hover:border-orange-100 transition-all shadow-sm"
                                                title="View/Approve">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('spcr.print', $data['spcr']->id) }}" target="_blank"
                                           class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-100 transition-all shadow-sm"
                                           title="Print SPCR">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @else
                                        <span class="text-[10px] font-bold text-gray-300 italic uppercase">No Record</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-4 border border-gray-100">
                                        <i class="fas fa-project-diagram text-2xl text-gray-200"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">No strategic assessments found.</p>
                                    <p class="text-xs text-gray-400 mt-1">Division-level reviews that you oversee will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                Showing {{ count($staffData) }} of {{ count($staffData) }} assessments
            </p>
            <div class="flex gap-1">
                <button class="w-8 h-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:bg-gray-50 transition-all"><i class="fas fa-chevron-left text-[10px]"></i></button>
                <button class="w-8 h-8 rounded-lg bg-gray-900 border border-gray-900 flex items-center justify-center text-white text-[10px] font-bold">1</button>
                <button class="w-8 h-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:bg-gray-50 transition-all"><i class="fas fa-chevron-right text-[10px]"></i></button>
            </div>
        </div>
    </div>
</div>

@include('spcr.modal')

@endsection

@push('scripts')
<script>
    window.SPCR_CONTEXT = 'staff';
    const currentYear = {{ $year }};
    const currentSemester = {{ (date('n') <= 6 ? 1 : 2) }};
    let currentSpcrId = null; 
    
    $(document).ready(function() {
        // Search functionality
        $('#staffSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $(".staff-row").filter(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });
        });

        // Year filter
        $('#yearFilter').on('change', function() {
            const year = $(this).val();
            window.location.href = `{{ route('spcr.staff') }}?year=${year}`;
        });
    });
</script>
@endpush
