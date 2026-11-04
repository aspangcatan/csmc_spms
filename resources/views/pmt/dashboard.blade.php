@extends('layouts.app')

@section('title', 'PMT Dashboard')

@section('content')
@php
    $statuses = ['Draft Target', 'Target Submitted', 'Target Approved', 'Draft Accomplishment', 'Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved', 'PMT Approved'];
    $filterQuery = array_filter(compact('year', 'month', 'division', 'section', 'search'), fn ($value) => $value !== null && $value !== '');
    $statusStyle = function ($status) {
        if ($status === 'PMT Approved') return ['bg-emerald-50 text-emerald-600 ring-emerald-100', 'fa-check-circle'];
        if ($status === 'Division Head Approved') return ['bg-amber-50 text-amber-600 ring-amber-100', 'fa-hourglass-half'];
        if (str_contains($status, 'Submitted')) return ['bg-orange-50 text-orange-600 ring-orange-100', 'fa-paper-plane'];
        if (str_contains($status, 'Draft')) return ['bg-blue-50 text-blue-600 ring-blue-100', 'fa-pencil-alt'];
        return ['bg-purple-50 text-purple-600 ring-purple-100', 'fa-check'];
    };
    $periodText = fn ($record) => 'FY ' . $record->year . ' - ' . Period::shortLabel($record->semester);
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">PMT Dashboard</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Approval Status of IPCR and SPCR Submissions</p>
        </div>
        <a href="{{ route('pmt.approvals', $filterQuery) }}"
           class="px-5 py-3 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm flex items-center gap-2">
            <i class="fas fa-stamp"></i> Open Approval Queue
        </a>
    </div>

    @include('pmt.partials.filters', ['action' => route('pmt.dashboard')])

    {{-- Summary tiles --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Total Submissions</p>
            <p class="text-3xl font-black text-gray-900 leading-none">{{ $summary['total'] }}</p>
            <p class="text-[10px] text-gray-400 mt-4 border-t border-gray-50 pt-3">IPCR and SPCR records matching the filters</p>
        </div>
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i> PMT Approved
            </p>
            <p class="text-3xl font-black text-gray-900 leading-none">{{ $summary['pmt_approved'] }}</p>
            <p class="text-[10px] text-gray-400 mt-4 border-t border-gray-50 pt-3">Fully approved by all {{ $pmtTotal }} PMT members</p>
        </div>
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fas fa-hourglass-half text-amber-500"></i> Pending PMT Approval
            </p>
            {{-- Per member: records this member already signed no longer count as pending for them --}}
            <p class="text-3xl font-black {{ $summary['awaiting_me'] > 0 ? 'text-orange-500' : 'text-gray-900' }} leading-none">{{ $summary['awaiting_me'] }}</p>
            <p class="text-[10px] text-gray-400 mt-4 border-t border-gray-50 pt-3">
                Awaiting your signature · {{ $summary['signed_awaiting_others'] }} signed by you, waiting for other members
            </p>
        </div>
        <div class="card-modern p-6 border-0">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fas fa-spinner text-blue-500"></i> Not Yet at PMT
            </p>
            <p class="text-3xl font-black text-gray-900 leading-none">{{ $summary['in_progress'] }}</p>
            <p class="text-[10px] text-gray-400 mt-4 border-t border-gray-50 pt-3">Drafts and earlier approval stages</p>
        </div>
    </div>

    {{-- Status breakdown --}}
    <div class="card-modern overflow-hidden border-0 bg-white mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Status Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">IPCR</th>
                        <th class="px-6 py-4 text-right">SPCR</th>
                        <th class="px-6 py-4 text-right">Total</th>
                        <th class="px-6 py-4 w-1/3">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($statuses as $status)
                        @php
                            $ipcrCount = $statusCounts['ipcr'][$status] ?? 0;
                            $spcrCount = $statusCounts['spcr'][$status] ?? 0;
                            $rowTotal = $ipcrCount + $spcrCount;
                            $share = $summary['total'] > 0 ? $rowTotal / $summary['total'] * 100 : 0;
                            [$style, $icon] = $statusStyle($status);
                        @endphp
                        <tr>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full {{ $style }} text-[10px] font-black uppercase tracking-widest ring-1 ring-inset">
                                    <i class="fas {{ $icon }}"></i> {{ $status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-bold text-gray-700 tabular-nums">{{ $ipcrCount }}</td>
                            <td class="px-6 py-3 text-right text-sm font-bold text-gray-700 tabular-nums">{{ $spcrCount }}</td>
                            <td class="px-6 py-3 text-right text-sm font-black text-gray-900 tabular-nums">{{ $rowTotal }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-1.5 flex-1 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gray-400 rounded-full" style="width: {{ $share }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-500 w-10 text-right tabular-nums">{{ number_format($share, 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Pending PMT approval --}}
        <div class="card-modern overflow-hidden border-0 bg-white">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Pending PMT Approval</h2>
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $summary['awaiting_me'] }} awaiting you</span>
            </div>
            <div class="divide-y divide-gray-50 max-h-[480px] overflow-y-auto">
                @forelse($pending['ipcr']->map(fn ($r) => [$r, 'IPCR'])->concat($pending['spcr']->map(fn ($r) => [$r, 'SPCR']))->reject(fn ($item) => $item[0]->signed_by_me)->sortByDesc(fn ($item) => $item[0]->updated_at) as [$record, $type])
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $record->user->name ?? 'Unknown User' }}</p>
                            <p class="text-[10px] text-gray-400 font-medium">{{ $type }} · {{ $periodText($record) }} · {{ $record->user->section_name ?? '' }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="py-1 px-3 rounded-full bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100 text-[10px] font-black uppercase tracking-widest" title="PMT members who have approved">
                                PMT {{ $record->pmt_approved_count }}/{{ $pmtTotal }}
                            </span>
                            <span class="py-1 px-3 rounded-full bg-orange-50 text-orange-600 ring-1 ring-inset ring-orange-100 text-[10px] font-black uppercase tracking-widest">
                                <i class="fas fa-pen"></i> Needs your signature
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-12 text-center text-sm font-bold text-gray-400">Nothing is waiting for your signature.</p>
                @endforelse
            </div>
        </div>

        {{-- Recently approved --}}
        <div class="card-modern overflow-hidden border-0 bg-white">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Recently PMT Approved</h2>
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Latest 10 per type</span>
            </div>
            <div class="divide-y divide-gray-50 max-h-[480px] overflow-y-auto">
                @forelse($approved['ipcr']->map(fn ($r) => [$r, 'IPCR', route('ipcr.print', $r->id)])->concat($approved['spcr']->map(fn ($r) => [$r, 'SPCR', route('spcr.print', $r->id)]))->sortByDesc(fn ($item) => $item[0]->updated_at) as [$record, $type, $printUrl])
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $record->user->name ?? 'Unknown User' }}</p>
                            <p class="text-[10px] text-gray-400 font-medium">{{ $type }} · {{ $periodText($record) }} · Approved {{ $record->updated_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="py-1 px-3 rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100 text-[10px] font-black uppercase tracking-widest">
                                <i class="fas fa-check-circle"></i> {{ $record->final_rating_adjective ?? 'Approved' }}
                            </span>
                            <a href="{{ $printUrl }}" target="_blank"
                               class="w-9 h-9 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-100 transition-all shadow-sm"
                               title="View/Print {{ $type }}">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-12 text-center text-sm font-bold text-gray-400">No PMT-approved records for these filters.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
