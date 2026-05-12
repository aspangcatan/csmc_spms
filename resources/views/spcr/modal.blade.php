<div class="modal fade" id="spcrModal" tabindex="-1" aria-labelledby="spcrModalLabel" aria-hidden="true"
     style="display: none;">
    <div class="modal-dialog modal-xxl modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 rounded-3xl shadow-2xl">
            <div class="modal-body p-0 bg-white">
                
                {{-- ✅ Modern Status Timeline (Exactly matching IPCR 8-stage flow) --}}
                <div class="bg-gray-50/80 border-b border-gray-100 p-8">
                    <div class="flex justify-between items-center text-xs font-bold relative max-w-7xl mx-auto px-4">
                        @php
                            $statuses = [
                                'Draft Target', 'Target Submitted', 'Target Approved',
                                'Draft Accomplishment', 'Accomplishment Submitted',
                                'Supervisor Approved', 'Division Head Approved', 'PMT Approved'
                            ];
                        @endphp

                        @foreach ($statuses as $index => $status)
                            <div class="flex items-center flex-1 status-step relative group" data-status="{{ $status }}" data-index="{{ $index }}">
                                <div class="relative flex flex-col items-center w-full z-10">
                                    <div class="w-7 h-7 flex items-center justify-center rounded-full border-2 border-gray-200 bg-white text-gray-400 status-circle transition-all duration-300 shadow-sm font-bold text-[10px]">
                                        {{ $index + 1 }}
                                    </div>
                                    <span class="mt-3 text-gray-400 text-center text-[9px] uppercase tracking-widest leading-none w-24 mx-auto block opacity-80 group-hover:opacity-100 transition-opacity">
                                        {{ $status }}
                                    </span>
                                </div>
                                @if (!$loop->last)
                                    <div class="absolute top-[14px] left-[50%] w-full h-[2px] bg-gray-200 -z-0 status-line-bg"></div>
                                    <div class="absolute top-[14px] left-[50%] h-[2px] bg-emerald-500 -z-0 status-line hidden shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex justify-between items-center mb-10">
                        <div>
                            <h2 id="modalTitle" class="text-2xl font-black text-gray-900 tracking-tight leading-none mb-2">
                                STRATEGIC PERFORMANCE REVIEW
                            </h2>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Division Performance Tracking System</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 flex items-center gap-4">
                            <div id="spcrDateWrapper">
                                <label class="block text-[9px] text-gray-400 uppercase font-black mb-1 tracking-widest">Date Created</label>
                                <input type="date" id="spcrDate" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer">
                            </div>
                            <div id="spcrDateDoneWrapper" class="hidden">
                                <label class="block text-[9px] text-gray-400 uppercase font-black mb-1 tracking-widest">Date Accomplished</label>
                                <input type="date" id="spcrDateDone" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer">
                            </div>
                            <div class="hidden">
                                <label class="block text-[9px] text-gray-400 uppercase font-black mb-1 tracking-widest">Division Head</label>
                                <p class="text-xs font-bold text-gray-900" id="raterName">{{ Auth::user()->name }}</p>
                            </div>
                            <div class="text-right hidden">
                                <p class="text-[9px] text-gray-400 uppercase font-black mb-0.5 tracking-widest">Document ID</p>
                                <p class="text-xs font-bold text-gray-900" id="displaySpcrId">NEW_DOCUMENT</p>
                            </div>
                        </div>
                    </div>


                    <!-- Hidden fields -->
                    <input type="hidden" id="spcrId" value="">
                    <input type="hidden" id="statusValue" value="Draft Target">


                    {{-- SPCR Table --}}
                    <div class="card-modern overflow-hidden border-0">
                        <table class="w-full text-[11px]">
                            <thead>
                            <tr class="bg-gray-900 text-white font-bold text-[10px] uppercase tracking-widest text-center">
                                <th class="p-4 border-r border-gray-800 w-[14%]">Strategic Goals & Objectives</th>
                                <th class="p-4 border-r border-gray-800 w-[14%]">Success Indicator</th>
                                <th class="p-4 border-r border-gray-800 w-[12%]">Individual Accountable</th>
                                <th class="p-4 border-r border-gray-800 accomplishment-col w-[14%]">Actual Accomplishment</th>
                                <th class="p-4 border-r border-gray-800 rate-col w-[8%]">Accomplishment Rate</th>
                                <th colspan="4" class="p-2 border-r border-gray-800 rating-col w-[180px]">
                                    <div class="border-b border-gray-800 pb-1 mb-1">Rating</div>
                                    <div class="flex justify-center text-center">
                                        <span class="w-10 opacity-60">Q</span>
                                        <span class="w-10 opacity-60">E</span>
                                        <span class="w-10 opacity-60">T</span>
                                        <span class="w-12">AVG</span>
                                    </div>
                                </th>
                                <th class="p-4 remarks-col w-[11%]">Remarks</th>
                                <th class="p-4 w-12"></th>
                            </tr>
                            </thead>

                            {{-- CORE FUNCTIONS --}}
                            <tbody id="core-entries">
                            <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <td colspan="11" class="px-6 py-3 relative">
                                    <div class="flex justify-between items-center">
                                        <span class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 bg-orange-500 rounded-full"></div>
                                            Core Functions
                                        </span>
                                        <div class="dropdown">
                                            <button class="text-gray-300 hover:text-orange-500 transition-colors add-row-btn" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-plus-circle text-lg"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-2xl p-2">
                                                <li><a class="dropdown-item rounded-xl py-2 px-4 text-xs font-bold" href="#" onclick="appendNewRow('#core-entries'); return false;">Add Single Row</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li class="px-3 py-2">
                                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                                        <input type="number" class="form-control text-xs w-16 rounded-lg border-gray-200 bulk-row-input" value="5" min="1" id="core-rows-input">
                                                        <button class="btn btn-orange text-[10px] whitespace-nowrap px-4" type="button" onclick="appendMultipleRows('#core-entries', '#core-rows-input')">Bulk Add</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>

                            {{-- SUPPORT FUNCTIONS --}}
                            <tbody id="support-entries">
                            <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <td colspan="11" class="px-6 py-3 relative">
                                    <div class="flex justify-between items-center">
                                        <span class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 bg-cyan-500 rounded-full"></div>
                                            Support Functions
                                        </span>
                                        <div class="dropdown">
                                            <button class="text-gray-300 hover:text-cyan-500 transition-colors add-row-btn" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-plus-circle text-lg"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-2xl p-2">
                                                <li><a class="dropdown-item rounded-xl py-2 px-4 text-xs font-bold" href="#" onclick="appendNewRow('#support-entries'); return false;">Add Single Row</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li class="px-3 py-2">
                                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                                        <input type="number" class="form-control text-xs w-16 rounded-lg border-gray-200 bulk-row-input" value="5" min="1" id="support-rows-input">
                                                        <button class="btn btn-orange bg-cyan-500 hover:bg-cyan-600 text-[10px] whitespace-nowrap px-4 border-0" type="button" onclick="appendMultipleRows('#support-entries', '#support-rows-input')">Bulk Add</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>

                            {{-- STRATEGIC FUNCTIONS --}}
                            <tbody id="strategic-entries">
                            <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <td colspan="11" class="px-6 py-3 relative">
                                    <div class="flex justify-between items-center">
                                        <span class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 bg-purple-500 rounded-full"></div>
                                            Strategic Functions
                                        </span>
                                        <div class="dropdown">
                                            <button class="text-gray-300 hover:text-purple-500 transition-colors add-row-btn" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-plus-circle text-lg"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-2xl p-2">
                                                <li><a class="dropdown-item rounded-xl py-2 px-4 text-xs font-bold" href="#" onclick="appendNewRow('#strategic-entries'); return false;">Add Single Row</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li class="px-3 py-2">
                                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                                        <input type="number" class="form-control text-xs w-16 rounded-lg border-gray-200 bulk-row-input" value="5" min="1" id="strategic-rows-input">
                                                        <button class="btn btn-orange bg-purple-500 hover:bg-purple-600 text-[10px] whitespace-nowrap px-4 border-0" type="button" onclick="appendMultipleRows('#strategic-entries', '#strategic-rows-input')">Bulk Add</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50/50 p-8 border-t border-gray-100 flex justify-between items-center rounded-b-3xl mt-6">
                        <button class="btn btn-outline-modern bg-white text-gray-500 border-gray-200 px-8 py-3" data-bs-dismiss="modal">
                            Close Editor
                        </button>
                        
                        <div class="flex gap-3">
                            <button id="printBtn" class="btn btn-outline-modern bg-white border-gray-200 px-6 py-3" onclick="printSpcr()">
                                <i class="fas fa-print mr-2 opacity-50"></i> Print Preview
                            </button>
                            <button id="approveBtn" class="btn btn-orange bg-emerald-500 hover:bg-emerald-600 px-8 py-3 shadow-lg shadow-emerald-500/20" style="display: none;" onclick="approveSpcr()">
                                Approve Document
                            </button>
                            <button id="handleSaveBtn" class="btn btn-orange px-10 py-3 shadow-lg shadow-orange-500/20" onclick="handleSaveOrSubmit()">
                                <span id="saveBtnText">Submit Target</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template for dynamic row --}}
<template id="spcrRowTemplate">
    @include('spcr.partials.row')
</template>


@push('scripts')
<script>
    window.spcrApiBaseUrl = window.spcrApiBaseUrl || @json(url('/api/spcr'));
    window.spcrPrintBaseUrl = window.spcrPrintBaseUrl || @json(url('/spcr/print'));
    // currentSpcrId is handled by the index page to avoid duplicate declaration
    function isStaffReviewMode() {
        return window.SPCR_CONTEXT === 'staff' || window.location.pathname.includes(@json(route('spcr.staff', [], false)));
    }

    function createSpcr() {
        if (typeof currentSpcrId !== 'undefined' && currentSpcrId) {
            showAlert(
                'Existing Document Found',
                `An SPCR document already exists for ${currentYear}, ${currentSemester === 1 ? '1st Semester' : '2nd Semester'}. Please open the existing document instead of creating a new one.`,
                'info'
            );
            return;
        }

        currentSpcrId = null;
        $('#spcrId').val('');
        $('#spcrDate').val(new Date().toISOString().split('T')[0]);
        $('#spcrDateDone').val('');
        $('#displaySpcrId').text('NEW_DOCUMENT');
        $('#modalTitle').text(`CREATE SPCR ${currentYear} - ${currentSemester == 1 ? '1st' : '2nd'} Sem`);
        
        // Clear tables
        $('#core-entries tr:not(:first), #support-entries tr:not(:first), #strategic-entries tr:not(:first)').remove();
        
        // Add skeleton rows
        appendNewRow('#core-entries');
        appendNewRow('#support-entries');
        appendNewRow('#strategic-entries');
        
        updateStatusIndicator('Draft Target');
        toggleSemesterFields('Draft Target');
        updateButtonText('Draft Target');
        
        $('#approveBtn').hide();
        $('#handleSaveBtn').show();
        $('#spcrModal').modal('show');
    }

    function appendNewRow(containerId) {
        const template = $('#spcrRowTemplate').html();
        $(containerId).append(template);
        const status = $('#statusValue').val() || 'Draft Target';
        toggleSemesterFields(status);
    }

    function appendMultipleRows(containerId, inputId) {
        const count = parseInt($(inputId).val(), 10);

        if (!Number.isFinite(count) || count < 1) {
            showAlert('Invalid Input', 'Please enter a valid number of rows to add.', 'warning');
            return;
        }

        if (count > 200) {
            showAlert('Input Too Large', 'Please limit bulk add to 200 rows at a time.', 'warning');
            return;
        }

        for (let i = 0; i < count; i++) {
            appendNewRow(containerId);
        }
    }

    function removeRow(btn) {
        confirmAction('Delete Row?', 'Are you sure you want to remove this row?', 'DELETE', () => {
            $(btn).closest('tr').remove();
        });
    }

    function handleSaveOrSubmit() {
        saveSpcr(true); // Always submit based on user request "no more saving of draft"
    }

    function isValidRatingValue(rawValue) {
        if (rawValue === '' || rawValue === null || typeof rawValue === 'undefined') return true;
        const value = Number(rawValue);
        return !Number.isNaN(value) && value >= 0 && value <= 5;
    }

    function validateAllRatings(showError = true) {
        let isValid = true;
        $('.q-rating, .e-rating, .t-rating').each(function() {
            const input = $(this);
            const rawValue = input.val();
            const ok = isValidRatingValue(rawValue);
            input.toggleClass('text-red-500', !ok);
            if (!ok) isValid = false;
        });

        if (!isValid && showError) {
            showAlert('Invalid Rating', 'Ratings must be between 0 and 5 only.', 'error');
        }
        return isValid;
    }

    function saveSpcr(isSubmit = true) {
        if (!validateAllRatings(true)) return;

        const coreEntries = getEntriesFromTable('#core-entries');
        const supportEntries = getEntriesFromTable('#support-entries');
        const strategicEntries = getEntriesFromTable('#strategic-entries');
        const requiredFieldErrors = validateRequiredSpcrFields({
            core_entries: coreEntries,
            support_entries: supportEntries,
            strategic_entries: strategicEntries
        });

        if (requiredFieldErrors.length > 0) {
            showSpcrRequiredFieldAlert(requiredFieldErrors);
            return;
        }
        const missingSections = [];

        if (coreEntries.length < 1) missingSections.push('Core Entries');
        if (supportEntries.length < 1) missingSections.push('Support Entries');
        if (strategicEntries.length < 1) missingSections.push('Strategic Entries');

        if (missingSections.length > 0) {
            showAlert(
                'Incomplete SPCR Entries',
                `Please provide at least one entry for the following section(s): ${missingSections.join(', ')}. Empty rows are not saved.`,
                'warning'
            );
            return;
        }

        let currentStatus = $('#statusValue').val();
        let targetStatus = currentStatus;

        if (isSubmit) {
            if (currentStatus === 'Draft Target') {
                targetStatus = 'Target Submitted';
            } else if (currentStatus === 'Target Approved' || currentStatus === 'Draft Accomplishment') {
                targetStatus = 'Accomplishment Submitted';
            }
        }

        const payload = {
            year: currentYear,
            semester: currentSemester,
            spcr_date: $('#spcrDate').val(),
            date_done: $('#spcrDateDone').val(),
            status: targetStatus,
            core_entries: coreEntries,
            support_entries: supportEntries,
            strategic_entries: strategicEntries,
        };

        const method = currentSpcrId ? 'PUT' : 'POST';
        const url = currentSpcrId ? `${window.spcrApiBaseUrl}/${currentSpcrId}` : window.spcrApiBaseUrl;

        if (isSubmit) {
            const phase = targetStatus.includes('Target') ? 'Target' : 'Accomplishment';
            confirmAction(`Submit ${phase}?`, `Are you sure you want to submit this ${phase} for review?`, 'SUBMIT', () => performSave(url, method, payload, true));
        } else {
            performSave(url, method, payload, false);
        }
    }

    function performSave(url, method, payload, isSubmit) {
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    let errMsg = '';
                    for (const field in data.errors) {
                        errMsg += `- ${data.errors[field].join(', ')}\n`;
                    }
                    throw new Error(errMsg.trim() || 'Please complete all required SPCR entries.');
                }
                throw new Error(data.message || 'Failed to save SPCR record.');
            }
            return data;
        })
        .then(data => {
            toast(isSubmit ? 'SPCR Submitted!' : 'SPCR Saved Successfully');
            $('#spcrModal').modal('hide');
            if (typeof loadSpcrBySemester === 'function') loadSpcrBySemester();
        })
        .catch(err => {
            console.error(err);
            showAlert('Error', err.message || 'Failed to save SPCR record.', 'error');
        });
    }

    function getEntriesFromTable(containerId) {
        const entries = [];
        $(containerId + ' tr').each(function() {
            if ($(this).find('td[colspan]').length) return;
            const row = $(this);
            const entry = {
                id: row.find('.row-id').val(),
                output: (row.find('.output-field').val() || '').trim(),
                success_indicator: (row.find('.indicator-field').val() || '').trim(),
                accountability: (row.find('.accountability-field').val() || '').trim(),
                actual_accomplishment: (row.find('.accomplishment-field').val() || '').trim(),
                accomplishment_rate: (row.find('.acc-rate-field').val() || '').trim(),
                quantity_rating: (row.find('.q-rating').val() || '').toString().trim(),
                efficiency_rating: (row.find('.e-rating').val() || '').toString().trim(),
                timeliness_rating: (row.find('.t-rating').val() || '').toString().trim(),
                remarks: (row.find('.remarks-field').val() || '').trim(),
            };
            const hasContent = [
                entry.output,
                entry.success_indicator,
                entry.accountability,
                entry.actual_accomplishment,
                entry.accomplishment_rate,
                entry.quantity_rating,
                entry.efficiency_rating,
                entry.timeliness_rating,
                entry.remarks
            ].some(value => value !== null && value !== undefined && value.toString().trim() !== '');

            if (hasContent) entries.push(entry);
        });
        return entries;
    }

    function validateRequiredSpcrFields(groupedRows) {
        const labels = {
            core_entries: 'Core Entries',
            support_entries: 'Support Entries',
            strategic_entries: 'Strategic Entries'
        };

        const errors = [];

        Object.keys(labels).forEach((key) => {
            const rows = groupedRows[key] || [];
            rows.forEach((row, index) => {
                const rowNumber = index + 1;
                if (!row.output || row.output.trim() === '') {
                    errors.push(`${labels[key]} row ${rowNumber}: Output is required.`);
                }
                if (!row.success_indicator || row.success_indicator.trim() === '') {
                    errors.push(`${labels[key]} row ${rowNumber}: Success Indicator is required.`);
                }
                if (!row.accountability || row.accountability.trim() === '') {
                    errors.push(`${labels[key]} row ${rowNumber}: Individual Accountable is required.`);
                }
            });
        });

        return errors;
    }

    function showSpcrRequiredFieldAlert(requiredFieldErrors) {
        const htmlList = `<ul class="text-left list-disc pl-6 space-y-1">${requiredFieldErrors
            .map(error => `<li>${$('<div>').text(error).html()}</li>`)
            .join('')}</ul>`;

        Swal.fire({
            title: 'Incomplete Required Fields',
            html: htmlList,
            icon: 'warning',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'swal2-popup-modern shadow-2xl border border-gray-100',
                title: 'swal2-title-modern',
                confirmButton: 'swal2-confirm-modern'
            },
            buttonsStyling: false
        });
    }

    function viewSpcr(id) {
        currentSpcrId = id;
        fetch(`${window.spcrApiBaseUrl}/${id}`)
            .then(res => res.json())
            .then(spcr => {
                $('#spcrId').val(spcr.id);
                $('#spcrDate').val(spcr.spcr_date || '');
                $('#spcrDateDone').val(spcr.date_done || '');
                $('#displaySpcrId').text(`SPCR-${String(spcr.id).padStart(5, '0')}`);
                $('#modalTitle').text(`SPCR ${spcr.year} - ${spcr.semester == 1 ? '1st' : '2nd'} Semester`);
                $('#raterName').text(spcr.division_head?.name || '---');

                populateTable('#core-entries', spcr.entries.filter(e => e.category === 'core'));
                populateTable('#support-entries', spcr.entries.filter(e => e.category === 'support'));
                populateTable('#strategic-entries', spcr.entries.filter(e => e.category === 'strategic'));

                updateStatusIndicator(spcr.status);
                updateButtonText(spcr.status);
                toggleSemesterFields(spcr.status);

                const userId = {{ Auth::id() }};
                const userIsPmt = {{ auth()->user()->isPmt() ? 'true' : 'false' }};
                const isSupervisor = spcr.supervisor_id == userId;
                const isDivHead = spcr.division_head_id == userId;
                const isPmt = (spcr.pmt_id == userId) || userIsPmt;
                
                let canApprove = false;
                if (spcr.status === 'Target Submitted' || spcr.status === 'Accomplishment Submitted') {
                    canApprove = isSupervisor;
                } else if (spcr.status === 'Supervisor Approved') {
                    canApprove = isPmt;
                }

                if (isStaffReviewMode()) {
                    const isEditableByStaff = spcr.status !== 'PMT Approved';
                    if (window.isSpcrStaffEditMode && isEditableByStaff) {
                        $('#handleSaveBtn').show();
                        $('#saveBtnText').text('Save Changes');
                    } else {
                        $('#handleSaveBtn').hide();
                    }
                    if (canApprove) {
                        $('#approveBtn').show();
                    } else {
                        $('#approveBtn').hide();
                    }
                } else {
                    if (canApprove) {
                        $('#handleSaveBtn').hide();
                        $('#approveBtn').show();
                    } else if (['Draft Target', 'Target Submitted', 'Target Approved', 'Draft Accomplishment'].includes(spcr.status)) {
                        $('#handleSaveBtn').show();
                        $('#approveBtn').hide();
                    } else {
                        $('#handleSaveBtn, #approveBtn').hide();
                    }
                }

                $('#spcrModal').modal('show');
            });
    }

    function populateTable(containerId, entries) {
        const container = $(containerId);
        container.find('tr:not(:first)').remove();
        
        entries.forEach(e => {
            const row = $($('#spcrRowTemplate').html());
            row.find('.row-id').val(e.id);
            row.find('.output-field').val(e.output);
            row.find('.indicator-field').val(e.success_indicator);
            row.find('.accountability-field').val(e.accountability);
            row.find('.accomplishment-field').val(e.actual_accomplishment);
            row.find('.acc-rate-field').val(e.accomplishment_rate);
            row.find('.q-rating').val(e.quantity_rating);
            row.find('.e-rating').val(e.efficiency_rating);
            row.find('.t-rating').val(e.timeliness_rating);
            row.find('.a-rating').val(Number(e.average_rating || 0).toFixed(2));
            row.find('.remarks-field').val(e.remarks);
            container.append(row);
        });
    }

    function toggleSemesterFields(status) {
        const isTargetDraft  = ['Draft Target', 'Target Submitted'].includes(status);
        const isAccompDraft  = ['Target Approved', 'Draft Accomplishment'].includes(status);
        const isReadOnly     = ['Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved', 'PMT Approved'].includes(status);

        // Show the correct date field based on phase
        $('#spcrDateWrapper').toggle(isTargetDraft);
        $('#spcrDateDoneWrapper').toggle(!isTargetDraft);

        const settingInputs    = $('.output-field, .indicator-field, .accountability-field');
        const evaluationInputs = $('.accomplishment-field, .acc-rate-field, .remarks-field');
        const ratingInputs     = $('.q-rating, .e-rating, .t-rating');
        const bulkRowInputs    = $('.bulk-row-input');

        // Reset all
        $('#spcrModal textarea, #spcrModal input').prop('disabled', true).addClass('bg-gray-50/50 cursor-not-allowed');
        $('.delete-row-btn, .add-row-btn').hide();

        if (isTargetDraft) {
            settingInputs.prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            bulkRowInputs.prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            $('#spcrDate').prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            $('.delete-row-btn, .add-row-btn').show();
        } else if (isAccompDraft || (isReadOnly && window.isSpcrStaffEditMode)) {
            // Accomplishment phase, or staff editor bypassing read-only lock
            evaluationInputs.prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            bulkRowInputs.prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            $('#spcrDateDone').prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            if (!isStaffReviewMode() || window.isSpcrStaffEditMode) {
                ratingInputs.prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            }
            if (window.isSpcrStaffEditMode) {
                $('.delete-row-btn, .add-row-btn').show();
            }
        } else if (isReadOnly) {
            // Standard approver: allow rating input at applicable stages
            const currentStatus = $('#statusValue').val();
            if (['Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved'].includes(currentStatus)) {
                ratingInputs.prop('disabled', false).removeClass('bg-gray-50/50 cursor-not-allowed');
            }
        }
        $('.a-rating').prop('disabled', true);
    }

    function updateButtonText(status) {
        const btnText = $('#saveBtnText');
        if (status === 'Draft Target') {
            btnText.text('Submit Target');
        } else if (status === 'Target Submitted') {
            btnText.text('Save Changes');
        } else if (status === 'Target Approved' || status === 'Draft Accomplishment') {
            btnText.text('Submit Accomplishment');
        }
    }

    function approveSpcr() {
        if (!validateAllRatings(true)) return;

        const coreEntries = getEntriesFromTable('#core-entries');
        const supportEntries = getEntriesFromTable('#support-entries');
        const strategicEntries = getEntriesFromTable('#strategic-entries');
        const requiredFieldErrors = validateRequiredSpcrFields({
            core_entries: coreEntries,
            support_entries: supportEntries,
            strategic_entries: strategicEntries
        });

        if (requiredFieldErrors.length > 0) {
            showSpcrRequiredFieldAlert(requiredFieldErrors);
            return;
        }
        const missingSections = [];

        if (coreEntries.length < 1) missingSections.push('Core Entries');
        if (supportEntries.length < 1) missingSections.push('Support Entries');
        if (strategicEntries.length < 1) missingSections.push('Strategic Entries');

        if (missingSections.length > 0) {
            showAlert(
                'Incomplete SPCR Entries',
                `Please provide at least one entry for the following section(s): ${missingSections.join(', ')}. Empty rows are not saved.`,
                'warning'
            );
            return;
        }

        const currentStatus = $('#statusValue').val();
        let confirmMsg = 'Approve Document?';
        if (currentStatus === 'Target Submitted') confirmMsg = 'Approve Targets?';
        else if (currentStatus === 'Accomplishment Submitted') confirmMsg = 'Approve as Supervisor?';
        else if (currentStatus === 'Supervisor Approved') confirmMsg = 'Approve as Division Head?';
        else if (currentStatus === 'Division Head Approved') confirmMsg = 'Final PMT Approval?';
        
        confirmAction(confirmMsg, 'Are you sure you want to approve this SPCR stage?', 'APPROVE', () => {
            const payload = {
                spcr_date: $('#spcrDate').val(),
                date_done: $('#spcrDateDone').val(),
                core_entries: coreEntries,
                support_entries: supportEntries,
                strategic_entries: strategicEntries,
            };

            fetch(`${window.spcrApiBaseUrl}/${currentSpcrId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(async (res) => {
                if (!res.ok) {
                    let msg = 'Failed to update SPCR before approval.';
                    try {
                        const err = await res.json();
                        if (err.message) msg = err.message;
                    } catch (e) {}
                    throw new Error(msg);
                }
                return fetch(`${window.spcrApiBaseUrl}/${currentSpcrId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                });
            })
            .then(async (res) => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Approval failed.');
                }
                toast(data.message || 'SPCR Approved Successfully');
                $('#spcrModal').modal('hide');
                if (typeof loadSpcrBySemester === 'function') {
                    loadSpcrBySemester();
                } else if (isStaffReviewMode()) {
                    window.location.reload();
                }
            })
            .catch((err) => {
                showAlert('Error', err.message || 'Failed to approve SPCR.', 'error');
            });
        });
    }

    function printSpcr() {
        const spcrId = $('#spcrId').val() || currentSpcrId;
        if (!spcrId) {
            showAlert('Action Required', 'Please save the SPCR first before printing.', 'info');
            return;
        }
        window.open(`${window.spcrPrintBaseUrl}/${spcrId}`, '_blank');
    }

    function updateStatusIndicator(currentStatus) {
        const statuses = [
            'Draft Target', 'Target Submitted', 'Target Approved',
            'Draft Accomplishment', 'Accomplishment Submitted', 
            'Supervisor Approved', 'Division Head Approved', 'PMT Approved'
        ];
        $('#statusValue').val(currentStatus);
        const currentIndex = statuses.indexOf(currentStatus);
        const isApproved = currentStatus.includes('Approved');

        $('.status-step').each(function (index) {
            const circle = $(this).find('.status-circle');
            const line = $(this).find('.status-line');
            const label = $(this).find('span');

            circle.removeClass('bg-orange-500 border-orange-500 bg-emerald-500 border-emerald-500 bg-white border-gray-200 text-white text-gray-400 scale-110 shadow-lg');
            label.removeClass('text-orange-600 text-emerald-600 text-gray-400 font-black opacity-100');
            line.addClass('hidden');

            // Approved statuses mean the current step is already done — render it green
            const isCurrentCompleted = isApproved && index === currentIndex;

            if (index < currentIndex || isCurrentCompleted || currentStatus === 'PMT Approved') {
                circle.addClass('bg-emerald-500 border-emerald-500 text-white').html('<i class="fas fa-check text-[10px]"></i>');
                label.addClass('text-emerald-600 font-bold opacity-100');
                if (index < currentIndex || (currentStatus === 'PMT Approved' && index < statuses.length - 1)) {
                    line.removeClass('hidden');
                }
            } else if (index === currentIndex) {
                // Current in-progress (Draft / Submitted states)
                circle.addClass('bg-orange-500 border-orange-500 text-white scale-110 shadow-lg shadow-orange-500/20').text(index + 1);
                label.addClass('text-orange-600 font-black opacity-100');
            } else {
                circle.addClass('bg-white border-gray-200 text-gray-300').text(index + 1);
                label.addClass('text-gray-400');
            }
        });
    }

    // Auto-compute Average Rating
    $(document).on('input', '.q-rating, .e-rating, .t-rating', function() {
        const row = $(this).closest('tr');
        const current = $(this);
        if (!isValidRatingValue(current.val())) {
            current.addClass('text-red-500');
            row.find('.a-rating').val('0.00');
            return;
        }
        current.removeClass('text-red-500');

        const qRaw = row.find('.q-rating').val();
        const eRaw = row.find('.e-rating').val();
        const tRaw = row.find('.t-rating').val();
        const q = isValidRatingValue(qRaw) ? (parseFloat(qRaw) || 0) : 0;
        const e = isValidRatingValue(eRaw) ? (parseFloat(eRaw) || 0) : 0;
        const t = isValidRatingValue(tRaw) ? (parseFloat(tRaw) || 0) : 0;

        const ratings = [q, e, t].filter(v => v > 0);
        const average = ratings.length > 0 ? (ratings.reduce((a, b) => a + b) / ratings.length) : 0;
        
        row.find('.a-rating').val(average > 0 ? average.toFixed(2) : '0.00');
    });

    $(document).on('change', '.q-rating, .e-rating, .t-rating', function() {
        if (!isValidRatingValue($(this).val())) {
            showAlert('Invalid Rating', 'Ratings must be between 0 and 5 only.', 'error');
        }
    });
</script>
@endpush
