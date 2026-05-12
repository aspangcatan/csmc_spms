@extends('layouts.app')

@section('title', 'Division Head Approvals')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Division Head Approvals</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">IPCR Queue for Division Head Review</p>
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
                <p class="text-sm font-black text-gray-900 leading-none">{{ $pendingIpcrs->count() }}</p>
            </div>
        </div>
    </div>

    <div class="card-modern overflow-hidden border-0 bg-white">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">IPCR Queue</h2>
            @if($pendingIpcrs->count() > 0)
            <button id="bulkApproveBtn" onclick="bulkApproveIpcrs()"
                    class="hidden px-5 h-9 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm items-center gap-2">
                <i class="fas fa-check-double"></i>
                Bulk Approve (<span id="selectedCount">0</span>)
            </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="selectAll"
                                   class="w-4 h-4 rounded border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-400">
                        </th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Section</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pendingIpcrs as $ipcr)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="ipcr_ids[]" value="{{ $ipcr->id }}"
                                       class="ipcr-checkbox w-4 h-4 rounded border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-400">
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $ipcr->user->name ?? 'Unknown User' }}</p>
                                <p class="text-[10px] text-gray-400 font-medium">{{ $ipcr->user->designation_name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-700">{{ $ipcr->user->section_name ?? 'N/A' }}</p>
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
                                    <button type="button" onclick="approveIpcrAsDivisionHead({{ $ipcr->id }})"
                                            class="px-4 h-10 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm"
                                            title="Approve as Division Head">
                                        Approve
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-sm font-bold text-gray-500">No IPCR records awaiting Division Head approval.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.ipcrApiBaseUrl = window.ipcrApiBaseUrl || @json(url('/api/ipcr'));

    // ── Checkbox selection ────────────────────────────────────────────────────

    function updateBulkBtn() {
        const count = $('.ipcr-checkbox:checked').length;
        $('#selectedCount').text(count);
        count > 0 ? $('#bulkApproveBtn').removeClass('hidden').addClass('flex')
                  : $('#bulkApproveBtn').addClass('hidden').removeClass('flex');
    }

    $('#selectAll').on('change', function () {
        $('.ipcr-checkbox').prop('checked', this.checked);
        updateBulkBtn();
    });

    $(document).on('change', '.ipcr-checkbox', function () {
        const total   = $('.ipcr-checkbox').length;
        const checked = $('.ipcr-checkbox:checked').length;
        $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
        $('#selectAll').prop('checked', checked === total);
        updateBulkBtn();
    });

    // ── Fetch helper ──────────────────────────────────────────────────────────

    function postWithCsrf(url, body = null) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                ...(body ? { 'Content-Type': 'application/json' } : {})
            },
            ...(body ? { body: JSON.stringify(body) } : {})
        }).then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Request failed.');
            return data;
        });
    }

    // ── Individual approve ────────────────────────────────────────────────────

    function approveIpcrAsDivisionHead(id) {
        confirmAction(
            'Approve IPCR?',
            'This will perform Division Head approval for the selected IPCR.',
            'APPROVE',
            () => {
                postWithCsrf(`${window.ipcrApiBaseUrl}/${id}/approve`)
                    .then(() => { toast('IPCR approved successfully.'); window.location.reload(); })
                    .catch((err) => showAlert('Error', err.message, 'error'));
            }
        );
    }

    // ── Bulk approve ──────────────────────────────────────────────────────────

    function bulkApproveIpcrs() {
        const ids = $('.ipcr-checkbox:checked').map(function () { return parseInt($(this).val()); }).get();
        if (!ids.length) return;

        confirmAction(
            'Bulk Approve IPCR?',
            `This will perform Division Head approval for ${ids.length} selected IPCR record(s). This cannot be undone.`,
            'APPROVE ALL',
            () => {
                postWithCsrf(`${window.ipcrApiBaseUrl}/bulk-approve`, { ids })
                    .then((data) => {
                        const msg = `${data.approved.length} approved` +
                                    (data.failed.length ? `, ${data.failed.length} failed.` : '.');
                        toast(msg);
                        window.location.reload();
                    })
                    .catch((err) => showAlert('Error', err.message, 'error'));
            }
        );
    }

    // ── Year filter ───────────────────────────────────────────────────────────

    $('#yearFilter').on('change', function () {
        window.location.href = `{{ route('division_head.approvals') }}?year=${$(this).val()}`;
    });
</script>
@endpush
