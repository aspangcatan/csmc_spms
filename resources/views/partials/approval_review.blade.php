{{--
    Shared review/approve/reject UI for approval queues.
    Params: $decideUrl (POST endpoint taking type, decision, ids, remarks, signature), $roleLabel (e.g. "PMT"),
    $requireSignature (optional; approvals then need a drawn signature, printed in the CSMC PMT box).
    Expects per-type markup: .{type}-checkbox, #{type}SelectAll, #{type}BulkActions, .{type}-selected-count,
    and buttons calling openReview(type, id, name, period) / bulkDecide(type, decision).
--}}

{{-- Review Modal --}}
<div id="reviewModal" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl h-[92vh] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p id="reviewType" class="text-[10px] font-black text-gray-400 uppercase tracking-widest"></p>
                <h3 id="reviewTitle" class="text-lg font-black text-gray-900"></h3>
            </div>
            <div class="flex items-center gap-2">
                <a id="reviewPrintLink" href="#" target="_blank"
                   class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:border-blue-100 transition-all shadow-sm"
                   title="Open in new tab">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <button type="button" onclick="closeReview()"
                        class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-900 transition-all shadow-sm"
                        title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 flex flex-col lg:flex-row min-h-0">
            <div class="flex-1 min-h-[300px] bg-gray-100">
                <iframe id="reviewFrame" src="about:blank" class="w-full h-full border-0"></iframe>
            </div>

            <div class="w-full lg:w-96 border-t lg:border-t-0 lg:border-l border-gray-100 flex flex-col min-h-0">
                <div class="flex-1 overflow-y-auto p-6">
                    {{-- PMT x/N progress with approved/pending members; filled only for records in the PMT phase --}}
                    <div id="reviewPmtProgress" class="hidden mb-6"></div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">History</p>
                    <div id="reviewLogs" class="space-y-3"></div>
                </div>
                <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                    <label for="reviewRemarks" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        {{ $roleLabel }} Remarks <span class="normal-case font-bold tracking-normal">(required when rejecting)</span>
                    </label>
                    <textarea id="reviewRemarks" rows="4" maxlength="2000"
                              class="mt-2 w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring-emerald-400"
                              placeholder="Enter remarks..."></textarea>
                    @if(!empty($requireSignature))
                        <div class="mt-3">
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    Your Signature <span class="normal-case font-bold tracking-normal">(required to approve)</span>
                                </p>
                                <button type="button" onclick="reviewSignaturePad.clear()" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500">
                                    Clear
                                </button>
                            </div>
                            <canvas id="reviewSignature" class="mt-2 w-full h-28 rounded-xl border border-dashed border-gray-300 bg-white cursor-crosshair"></canvas>
                        </div>
                    @endif
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button" onclick="submitReview('reject')"
                                class="h-10 rounded-xl bg-white border border-red-200 text-red-500 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-undo"></i> Reject
                        </button>
                        <button type="button" onclick="submitReview('approve')"
                                class="h-10 rounded-xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const decideUrl = @json($decideUrl);
    const roleLabel = @json($roleLabel);
    const requireSignature = @json(!empty($requireSignature));
    const apiBaseUrls = {
        ipcr: @json(url('/api/ipcr')),
        spcr: @json(url('/api/spcr')),
    };
    const printUrls = {
        ipcr: @json(url('/ipcr/print')),
        spcr: @json(url('/spcr/print')),
    };

    // ── Checkbox selection ────────────────────────────────────────────────────

    function updateBulkActions(type) {
        const count = $(`.${type}-checkbox:checked`).length;
        $(`.${type}-selected-count`).text(count);
        count > 0 ? $(`#${type}BulkActions`).removeClass('hidden').addClass('flex')
                  : $(`#${type}BulkActions`).addClass('hidden').removeClass('flex');
    }

    ['ipcr', 'spcr'].forEach((type) => {
        $(`#${type}SelectAll`).on('change', function () {
            $(`.${type}-checkbox`).prop('checked', this.checked);
            updateBulkActions(type);
        });

        $(document).on('change', `.${type}-checkbox`, function () {
            const total = $(`.${type}-checkbox`).length;
            const checked = $(`.${type}-checkbox:checked`).length;
            $(`#${type}SelectAll`).prop('indeterminate', checked > 0 && checked < total);
            $(`#${type}SelectAll`).prop('checked', checked === total);
            updateBulkActions(type);
        });
    });

    // ── Fetch helpers ─────────────────────────────────────────────────────────

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

    function escapeHtml(text) {
        return $('<div>').text(text ?? '').html();
    }

    // ── Signature pad (PMT approvals) ──────────────────────────────────────────

    // The last signature drawn on this page, reused to pre-fill later approvals.
    let lastSignature = null;

    function createSignaturePad(canvas) {
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let dirty = false;

        const point = (e) => {
            const rect = canvas.getBoundingClientRect();
            return [e.clientX - rect.left, e.clientY - rect.top];
        };

        canvas.style.touchAction = 'none';
        canvas.addEventListener('pointerdown', (e) => {
            drawing = true;
            dirty = true;
            canvas.setPointerCapture(e.pointerId);
            const [x, y] = point(e);
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.lineTo(x + 0.1, y + 0.1);
            ctx.stroke();
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!drawing) return;
            const [x, y] = point(e);
            ctx.lineTo(x, y);
            ctx.stroke();
        });
        ['pointerup', 'pointercancel'].forEach((name) => canvas.addEventListener(name, () => { drawing = false; }));

        const pad = {
            // Sizes the backing store to the displayed size; call once the canvas is visible. Clears it.
            reset() {
                const ratio = window.devicePixelRatio || 1;
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#111827';
                dirty = false;
            },
            clear() {
                pad.reset();
            },
            load(dataUrl) {
                if (!dataUrl) return;
                const img = new Image();
                img.onload = () => {
                    const rect = canvas.getBoundingClientRect();
                    ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    dirty = true;
                };
                img.src = dataUrl;
            },
            isEmpty: () => !dirty,
            toDataURL: () => canvas.toDataURL('image/png'),
        };
        return pad;
    }

    const reviewSignaturePad = requireSignature ? createSignaturePad(document.getElementById('reviewSignature')) : null;

    function sendDecision(type, decision, ids, remarks, signature = null) {
        if (signature) lastSignature = signature;
        return postWithCsrf(decideUrl, { type, decision, ids, remarks: remarks || null, signature })
            .then((data) => {
                const verb = decision === 'approve' ? 'approved' : 'rejected';
                if (data.failed.length) {
                    const reasons = data.failed.map((f) => `#${f.id}: ${f.reason}`).join('\n');
                    showAlert('Partially completed', `${data.done.length} ${verb}, ${data.failed.length} failed.\n${reasons}`, 'warning');
                    setTimeout(() => window.location.reload(), 2500);
                    return;
                }
                toast(`${data.done.length} ${type.toUpperCase()} record(s) ${verb}.`);
                window.location.reload();
            })
            .catch((err) => showAlert('Error', err.message, 'error'));
    }

    // ── Review modal (view details + remarks + approve/reject) ────────────────

    let currentReview = null;

    function openReview(type, id, name, period) {
        currentReview = { type, id };
        $('#reviewType').text(`${type.toUpperCase()} Review`);
        $('#reviewTitle').text(`${name} · ${period}`);
        $('#reviewPrintLink').attr('href', `${printUrls[type]}/${id}`);
        $('#reviewFrame').attr('src', `${printUrls[type]}/${id}`);
        $('#reviewRemarks').val('');
        $('#reviewLogs').html('<p class="text-xs text-gray-400">Loading...</p>');
        $('#reviewPmtProgress').addClass('hidden').empty();
        $('#reviewModal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        if (reviewSignaturePad) {
            reviewSignaturePad.reset();
            reviewSignaturePad.load(lastSignature);
        }

        fetch(`${apiBaseUrls[type]}/${id}`, { headers: { 'Accept': 'application/json' } })
            .then((res) => res.ok ? res.json() : null)
            .then((record) => {
                if (!currentReview || currentReview.id !== id) return;
                renderPmtProgress(record);
            })
            .catch(() => {});

        fetch(`${apiBaseUrls[type]}/${id}/logs`, { headers: { 'Accept': 'application/json' } })
            .then((res) => res.ok ? res.json() : [])
            .then((logs) => {
                if (!currentReview || currentReview.id !== id) return;
                if (!logs.length) {
                    $('#reviewLogs').html('<p class="text-xs text-gray-400">No history yet.</p>');
                    return;
                }
                $('#reviewLogs').html(logs.map((log) => `
                    <div class="rounded-xl border border-gray-100 p-3">
                        <p class="text-xs font-black text-gray-900">${escapeHtml(log.subject)}</p>
                        <p class="text-xs text-gray-600 mt-1 whitespace-pre-line">${escapeHtml(log.content)}</p>
                        <p class="text-[10px] text-gray-400 font-bold mt-2">
                            ${escapeHtml(log.user?.name || 'System')} · ${escapeHtml(new Date(log.created_at).toLocaleString())}
                        </p>
                    </div>`).join(''));
            })
            .catch(() => $('#reviewLogs').html('<p class="text-xs text-gray-400">Unable to load history.</p>'));
    }

    // Every PMT member must approve, so show the x/N count and who is still pending.
    function renderPmtProgress(record) {
        const progress = record?.pmt_progress;
        if (!progress || !progress.total || !['Division Head Approved', 'PMT Approved'].includes(record.status)) return;

        const nameList = (names, icon, color) => names.length
            ? names.map((n) => `<li class="flex items-center gap-2 text-xs text-gray-700"><i class="fas ${icon} ${color} text-[10px]"></i>${escapeHtml(n)}</li>`).join('')
            : '<li class="text-xs text-gray-400">None</li>';
        const done = record.status === 'PMT Approved';

        $('#reviewPmtProgress').html(`
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">PMT Approval</p>
                <span class="py-1 px-3 rounded-full ${done ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-amber-50 text-amber-600 ring-amber-100'} text-[10px] font-black uppercase tracking-widest ring-1 ring-inset">
                    PMT ${progress.approved}/${progress.total}
                </span>
            </div>
            <div class="rounded-xl border border-gray-100 p-3 space-y-3">
                <div>
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Approved</p>
                    <ul class="space-y-1">${nameList(progress.approvers, 'fa-check-circle', 'text-emerald-500')}</ul>
                </div>
                ${done ? '' : `<div>
                    <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Pending</p>
                    <ul class="space-y-1">${nameList(progress.pending, 'fa-clock', 'text-amber-500')}</ul>
                </div>`}
            </div>`).removeClass('hidden');
    }

    function closeReview() {
        currentReview = null;
        $('#reviewModal').addClass('hidden').removeClass('flex');
        $('#reviewFrame').attr('src', 'about:blank');
        $('body').removeClass('overflow-hidden');
    }

    function submitReview(decision) {
        if (!currentReview) return;
        const { type, id } = currentReview;
        const remarks = $('#reviewRemarks').val().trim();

        if (decision === 'reject' && !remarks) {
            showAlert('Remarks required', 'Please enter remarks explaining why this record is being rejected.', 'warning');
            $('#reviewRemarks').focus();
            return;
        }

        let signature = null;
        if (decision === 'approve' && reviewSignaturePad) {
            if (reviewSignaturePad.isEmpty()) {
                showAlert('Signature required', 'Please draw your signature before approving.', 'warning');
                return;
            }
            signature = reviewSignaturePad.toDataURL();
        }

        const label = type.toUpperCase();
        confirmAction(
            decision === 'approve' ? `Approve ${label}?` : `Reject ${label}?`,
            decision === 'approve'
                ? `This will record ${roleLabel} approval${signature ? ' and your signature' : ''} for this ${label}.`
                : `This will return the ${label} to the employee for revision.`,
            decision === 'approve' ? 'APPROVE' : 'REJECT',
            () => sendDecision(type, decision, [id], remarks, signature)
        );
    }

    $('#reviewModal').on('click', function (e) {
        if (e.target === this) closeReview();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && currentReview) closeReview();
    });

    // ── Bulk approve / reject with shared remarks ─────────────────────────────

    function bulkDecide(type, decision) {
        const ids = $(`.${type}-checkbox:checked`).map(function () { return parseInt($(this).val()); }).get();
        if (!ids.length) return;

        const label = type.toUpperCase();
        const isApprove = decision === 'approve';
        const withSignature = isApprove && requireSignature;
        let bulkPad = null;

        Swal.fire({
            title: isApprove ? `Bulk Approve ${label}?` : `Bulk Reject ${label}?`,
            html: (isApprove
                ? `This will record ${roleLabel} approval for <b>${ids.length}</b> selected ${label} record(s).<br>The remarks below will be added to each record.`
                : `This will return <b>${ids.length}</b> selected ${label} record(s) to the employees for revision.<br>The remarks below will be added to each record.`)
                + (withSignature
                    ? `<div class="mt-4 text-left">
                           <div class="flex items-center justify-between">
                               <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Your signature (applied to each record)</span>
                               <button type="button" id="bulkSignatureClear" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-red-500">Clear</button>
                           </div>
                           <canvas id="bulkSignature" class="mt-2 w-full h-28 rounded-xl border border-dashed border-gray-300 bg-white cursor-crosshair"></canvas>
                       </div>`
                    : ''),
            didOpen: () => {
                if (!withSignature) return;
                bulkPad = createSignaturePad(document.getElementById('bulkSignature'));
                bulkPad.reset();
                bulkPad.load(lastSignature);
                document.getElementById('bulkSignatureClear').addEventListener('click', () => bulkPad.clear());
            },
            preConfirm: (value) => {
                if (bulkPad && bulkPad.isEmpty()) {
                    Swal.showValidationMessage('Please draw your signature before approving.');
                    return false;
                }
                return { remarks: (value || '').trim(), signature: bulkPad ? bulkPad.toDataURL() : null };
            },
            icon: 'warning',
            input: 'textarea',
            inputLabel: isApprove ? `${roleLabel} Remarks (optional)` : `${roleLabel} Remarks (required)`,
            inputPlaceholder: 'Enter remarks...',
            inputAttributes: { maxlength: 2000 },
            showCancelButton: true,
            confirmButtonText: isApprove ? 'APPROVE ALL' : 'REJECT ALL',
            cancelButtonText: 'CANCEL',
            inputValidator: (value) => {
                if (!isApprove && !value.trim()) return 'Remarks are required when rejecting.';
            },
            customClass: {
                popup: 'swal2-popup-modern shadow-2xl border border-gray-100',
                title: 'swal2-title-modern',
                confirmButton: 'swal2-confirm-modern mx-2',
                cancelButton: 'btn-outline-modern mx-2 px-6 py-2 text-[11px] uppercase tracking-widest'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) sendDecision(type, decision, ids, result.value.remarks, result.value.signature);
        });
    }
</script>
@endpush
