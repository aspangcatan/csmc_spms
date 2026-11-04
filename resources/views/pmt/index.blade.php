@extends('layouts.app')

@section('title', 'PMT Approvals')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">PMT Approvals</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Final Approval Queue for IPCR and SPCR</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pmt.dashboard', request()->query()) }}"
               class="px-4 py-2.5 rounded-2xl bg-white border border-gray-100 shadow-sm text-[10px] font-black text-gray-500 uppercase tracking-widest hover:text-orange-500 transition-all flex items-center gap-2">
                <i class="fas fa-chart-bar"></i> Dashboard
            </a>
            <div class="bg-white px-4 py-2.5 rounded-2xl border border-gray-100 shadow-sm text-right">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Awaiting You</p>
                <p class="text-sm font-black text-gray-900 leading-none">{{ $pendingIpcrs->count() + $pendingSpcrs->count() }}</p>
            </div>
        </div>
    </div>

    @include('pmt.partials.filters', ['action' => route('pmt.approvals')])

    {{-- View tabs; the filters above carry over --}}
    @php
        $tabs = [
            'pending'  => ['Awaiting My Signature', 'fa-pen'],
            'mine'     => ['Approved by Me', 'fa-signature'],
            'approved' => ['All PMT Approved', 'fa-check-circle'],
        ];
        $tabQuery = request()->except(['show', 'page']);
    @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($tabs as $key => [$tabLabel, $tabIcon])
            <a href="{{ route('pmt.approvals', $key === 'pending' ? $tabQuery : $tabQuery + ['show' => $key]) }}"
               class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm
                      {{ $show === $key ? 'bg-gray-900 text-white' : 'bg-white border border-gray-100 text-gray-500 hover:text-gray-900' }}">
                <i class="fas {{ $tabIcon }}"></i> {{ $tabLabel }}
                <span class="py-0.5 px-2 rounded-full {{ $show === $key ? 'bg-white/20' : 'bg-gray-100' }}">{{ $tabCounts[$key] }}</span>
            </a>
        @endforeach
    </div>

    @if($show === 'pending')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">IPCR Awaiting Your Signature</p>
            <p class="text-3xl font-black text-gray-900">{{ $pendingIpcrs->count() }}</p>
        </div>
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">SPCR Awaiting Your Signature</p>
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
                                <p class="text-xs font-bold text-gray-700">FY {{ $ipcr->year }} - {{ Period::shortLabel($ipcr->semester) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center py-1.5 px-3 rounded-full bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-purple-100">
                                    {{ $ipcr->status }}
                                </span>
                                <span class="ml-1 inline-flex items-center py-1.5 px-3 rounded-full bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-amber-100" title="PMT members who have approved">
                                    PMT {{ $ipcr->pmt_approved_count }}/{{ $pmtTotal }}
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
                                            onclick="openReview('ipcr', {{ $ipcr->id }}, @js($ipcr->user->name ?? 'Unknown User'), @js('FY ' . $ipcr->year . ' - ' . Period::shortLabel($ipcr->semester)))"
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
                                <p class="text-xs font-bold text-gray-700">FY {{ $spcr->year }} - {{ Period::shortLabel($spcr->semester) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center py-1.5 px-3 rounded-full bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-purple-100">
                                    {{ $spcr->status }}
                                </span>
                                <span class="ml-1 inline-flex items-center py-1.5 px-3 rounded-full bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-amber-100" title="PMT members who have approved">
                                    PMT {{ $spcr->pmt_approved_count }}/{{ $pmtTotal }}
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
                                            onclick="openReview('spcr', {{ $spcr->id }}, @js($spcr->user->name ?? 'Unknown User'), @js('FY ' . $spcr->year . ' - ' . Period::shortLabel($spcr->semester)))"
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

    @else
    {{-- "Approved by Me" / "All PMT Approved": read-only history of signed and fully approved records --}}
    <div class="card-modern overflow-hidden border-0 bg-white">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">{{ $tabs[$show][0] }}</h2>
                <p class="text-[10px] text-gray-400 font-bold mt-1">
                    {{ $show === 'mine' ? 'Records you signed, including those still waiting for other PMT members' : 'Records approved by every PMT member' }}
                </p>
            </div>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $results->total() }} records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">{{ $show === 'mine' ? 'You Signed' : 'Approved' }}</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($results as $record)
                        @php $isDone = $record->status === 'PMT Approved'; @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-xs font-black text-gray-500 uppercase">{{ $record->record_type }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $record->user->name ?? 'Unknown User' }}</p>
                                <p class="text-[10px] text-gray-400 font-medium">{{ $record->user->division_name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-700">FY {{ $record->year }} - {{ Period::shortLabel($record->semester) }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 py-1.5 px-3 rounded-full text-[10px] font-black uppercase tracking-widest ring-1 ring-inset
                                             {{ $isDone ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-amber-50 text-amber-600 ring-amber-100' }}">
                                    <i class="fas {{ $isDone ? 'fa-check-circle' : 'fa-hourglass-half' }}"></i>
                                    {{ $isDone ? 'PMT Approved' : 'Waiting for others' }}
                                </span>
                                @if($record->pmt_badge)
                                    <span class="ml-1 inline-flex items-center py-1.5 px-3 rounded-full bg-gray-50 text-gray-500 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset ring-gray-100" title="PMT members who have approved">
                                        {{ $record->pmt_badge }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($isDone)
                                    <p class="text-xs font-black text-gray-900">{{ number_format((float) $record->final_rating, 2) }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium">{{ $record->final_rating_adjective }}</p>
                                @else
                                    <p class="text-xs text-gray-300">—</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-500 whitespace-nowrap">
                                {{ ($show === 'mine' && $record->my_signed_at ? $record->my_signed_at : $record->updated_at)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route($record->record_type . '.print', $record->id) }}" target="_blank"
                                       class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-100 transition-all shadow-sm"
                                       title="View/Print {{ strtoupper($record->record_type) }}">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="text-sm font-bold text-gray-500">
                                    {{ $show === 'mine' ? 'You have not approved any records for these filters.' : 'No PMT-approved records for these filters.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($results->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $results->links() }}
            </div>
        @endif
    </div>
    @endif
</div>

@include('partials.approval_review', ['decideUrl' => route('pmt.decide'), 'roleLabel' => 'PMT', 'requireSignature' => true])
@endsection
