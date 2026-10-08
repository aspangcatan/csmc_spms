@extends('layouts.app')

@section('title', 'PMT Approvals')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">PMT Approvals</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Final Approval Queue for IPCR and SPCR</p>
        </div>
        <div class="bg-white px-4 py-2.5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Fiscal Year</p>
                <select id="yearFilter" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-pointer">
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="h-8 w-px bg-gray-100"></div>
            <div class="text-right">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Pending</p>
                <p class="text-sm font-black text-gray-900 leading-none">{{ $pendingIpcrs->count() + $pendingSpcrs->count() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">IPCR Awaiting PMT</p>
            <p class="text-3xl font-black text-gray-900">{{ $pendingIpcrs->count() }}</p>
        </div>
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">SPCR Awaiting PMT</p>
            <p class="text-3xl font-black text-gray-900">{{ $pendingSpcrs->count() }}</p>
        </div>
    </div>

    {{-- IPCR Queue --}}
    <div class="card-modern overflow-hidden border-0 bg-white mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">IPCR Queue</h2>
            @if($pendingIpcrs->count() > 0)
            <div id="ipcrBulkActions" class="hidden items-center gap-2">
                <button type="button" onclick="bulkDecide('ipcr', 'reject')"
                        class="px-5 h-9 rounded-xl bg-white border border-red-200 text-red-500 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-undo"></i>
                    Bulk Reject (<span class="ipcr-selected-count">0</span>)
                </button>
                <button type="button" onclick="bulkDecide('ipcr', 'approve')"
                        class="px-5 h-9 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-check-double"></i>
                    Bulk Approve (<span class="ipcr-selected-count">0</span>)
                </button>
            </div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="ipcrSelectAll"
                                   class="w-4 h-4 rounded border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-400">
                        </th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="ipcrTableBody">
                    @forelse($pendingIpcrs as $ipcr)
                        <tr class="hover:bg-gray-50/50 transition-colors ipcr-row">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="ipcr_ids[]" value="{{ $ipcr->id }}"
                                       class="ipcr-checkbox w-4 h-4 rounded border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-400">
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $ipcr->user->name ?? 'Unknown User' }}</p>
                                <p class="text-[10px] text-gray-400 font-medium">{{ $ipcr->user->division_name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-700">FY {{ $ipcr->year }} - {{ $ipcr->semester == 1 ? '1st' : '2nd' }} Sem</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center py-1.5 px-3 rounded-full bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-purple-100">
                                    {{ $ipcr->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('ipcr.print', $ipcr->id) }}" target="_blank"
                                       class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-100 transition-all shadow-sm"
                                       title="View/Print IPCR">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button type="button"
                                            onclick="openReview('ipcr', {{ $ipcr->id }}, @js($ipcr->user->name ?? 'Unknown User'), @js('FY ' . $ipcr->year . ' - ' . ($ipcr->semester == 1 ? '1st' : '2nd') . ' Sem'))"
                                            class="px-4 h-10 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm flex items-center gap-2"
                                            title="View details, add remarks, approve or reject">
                                        <i class="fas fa-eye"></i> Review
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-sm font-bold text-gray-500">No IPCR records awaiting PMT approval.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SPCR Queue --}}
    <div class="card-modern overflow-hidden border-0 bg-white">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">SPCR Queue</h2>
            @if($pendingSpcrs->count() > 0)
            <div id="spcrBulkActions" class="hidden items-center gap-2">
                <button type="button" onclick="bulkDecide('spcr', 'reject')"
                        class="px-5 h-9 rounded-xl bg-white border border-red-200 text-red-500 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-undo"></i>
                    Bulk Reject (<span class="spcr-selected-count">0</span>)
                </button>
                <button type="button" onclick="bulkDecide('spcr', 'approve')"
                        class="px-5 h-9 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-check-double"></i>
                    Bulk Approve (<span class="spcr-selected-count">0</span>)
                </button>
            </div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="spcrSelectAll"
                                   class="w-4 h-4 rounded border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-400">
                        </th>
                        <th class="px-6 py-4">Division Head</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="spcrTableBody">
                    @forelse($pendingSpcrs as $spcr)
                        <tr class="hover:bg-gray-50/50 transition-colors spcr-row">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="spcr_ids[]" value="{{ $spcr->id }}"
                                       class="spcr-checkbox w-4 h-4 rounded border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-400">
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $spcr->user->name ?? 'Unknown User' }}</p>
                                <p class="text-[10px] text-gray-400 font-medium">{{ $spcr->user->division_name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-700">FY {{ $spcr->year }} - {{ $spcr->semester == 1 ? '1st' : '2nd' }} Sem</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center py-1.5 px-3 rounded-full bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-purple-100">
                                    {{ $spcr->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('spcr.print', $spcr->id) }}" target="_blank"
                                       class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-100 transition-all shadow-sm"
                                       title="View/Print SPCR">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button type="button"
                                            onclick="openReview('spcr', {{ $spcr->id }}, @js($spcr->user->name ?? 'Unknown User'), @js('FY ' . $spcr->year . ' - ' . ($spcr->semester == 1 ? '1st' : '2nd') . ' Sem'))"
                                            class="px-4 h-10 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm flex items-center gap-2"
                                            title="View details, add remarks, approve or reject">
                                        <i class="fas fa-eye"></i> Review
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-sm font-bold text-gray-500">No SPCR records awaiting PMT approval.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('partials.approval_review', ['decideUrl' => route('pmt.decide'), 'roleLabel' => 'PMT'])
@endsection

@push('scripts')
<script>
    $('#yearFilter').on('change', function () {
        window.location.href = `{{ route('pmt.approvals') }}?year=${$(this).val()}`;
    });
</script>
@endpush
