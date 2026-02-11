<div class="modal fade" id="ipcrModal" tabindex="-1" aria-labelledby="ipcrModalLabel" aria-hidden="true"
     style="display: none;">
    <div class="modal-dialog modal-xxl modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 rounded-3xl shadow-2xl">
            <div class="modal-body p-0 bg-white">
                
                {{-- ✅ Modern Status Timeline --}}
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
                                INDIVIDUAL PERFORMANCE REVIEW
                            </h2>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Employee Performance Tracking System</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 flex items-center gap-4">
                            <div>
                                <label class="block text-[9px] text-gray-400 uppercase font-black mb-1 tracking-widest">Revision Date</label>
                                <input type="date" id="dateDone" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer">
                            </div>
                            <div class="h-8 w-px bg-gray-200"></div>
                            <div class="text-right">
                                <p class="text-[9px] text-gray-400 uppercase font-black mb-0.5 tracking-widest">Document ID</p>
                                <p class="text-xs font-bold text-gray-900" id="displayIpcrId">NEW_DOCUMENT</p>
                            </div>
                        </div>
                    </div>


                    <!-- Hidden fields -->
                    <input type="hidden" id="ipcrSemester" value="1">
                    <input type="hidden" id="ipcrYear" value="{{ date('Y') }}">
                    <input type="hidden" id="ipcrId" value="">


                    {{-- IPCR Table --}}
                    <div class="card-modern overflow-hidden border-0">
                        <table class="w-full text-[11px]">
                            <thead>
                            <tr class="bg-gray-900 text-white font-bold text-[10px] uppercase tracking-widest text-center">
                                <th class="p-4 border-r border-gray-800 w-[15%]">Output</th>
                                <th class="p-4 border-r border-gray-800 w-[15%]">Success Indicator</th>
                                <th class="p-4 border-r border-gray-800 accomplishment-col w-[35%]">Accomplishment</th>
                                <th colspan="4" class="p-2 border-r border-gray-800 rating-col w-[200px]">
                                    <div class="border-b border-gray-800 pb-1 mb-1">Rating</div>
                                    <div class="flex justify-center text-center">
                                        <span class="w-12 opacity-60">Q</span>
                                        <span class="w-12 opacity-60">E</span>
                                        <span class="w-12 opacity-60">T</span>
                                        <span class="w-14">AVG</span>
                                    </div>
                                </th>
                                <th class="p-4 remarks-col w-[15%]">Remarks</th>
                                <th class="p-4 w-12"></th>
                            </tr>
                            </thead>

                            {{-- CORE FUNCTIONS --}}
                            <tbody id="core-functions">
                            <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <td colspan="9" class="px-6 py-3 relative">
                                    <div class="flex justify-between items-center">
                                        <span class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 bg-orange-500 rounded-full"></div>
                                            Core Functions
                                        </span>
                                        <div class="dropdown">
                                            <button class="text-gray-300 hover:text-orange-500 transition-colors" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-plus-circle text-lg"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-2xl p-2">
                                                <li><a class="dropdown-item rounded-xl py-2 px-4 text-xs font-bold" href="#" onclick="appendNewRow('#core-functions')">Add Single Row</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li class="px-3 py-2">
                                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                                        <input type="number" class="form-control text-xs w-16 rounded-lg border-gray-200" value="5" id="core-rows-input">
                                                        <button class="btn btn-orange text-[10px] whitespace-nowrap px-4" onclick="appendMultipleRows('#core-functions', '#core-rows-input')">Bulk Add</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @include('ipcr.partials.row')
                            </tbody>

                            {{-- SUPPORT FUNCTIONS --}}
                            <tbody id="support-functions">
                            <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <td colspan="9" class="px-6 py-3 relative">
                                    <div class="flex justify-between items-center">
                                        <span class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 bg-cyan-500 rounded-full"></div>
                                            Support Functions
                                        </span>
                                        <div class="dropdown">
                                            <button class="text-gray-300 hover:text-cyan-500 transition-colors" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-plus-circle text-lg"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-2xl p-2">
                                                <li><a class="dropdown-item rounded-xl py-2 px-4 text-xs font-bold" href="#" onclick="appendNewRow('#support-functions')">Add Single Row</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li class="px-3 py-2">
                                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                                        <input type="number" class="form-control text-xs w-16 rounded-lg border-gray-200" value="5" id="support-rows-input">
                                                        <button class="btn btn-orange bg-cyan-500 hover:bg-cyan-600 text-[10px] whitespace-nowrap px-4 border-0" onclick="appendMultipleRows('#support-functions', '#support-rows-input')">Bulk Add</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @include('ipcr.partials.row')
                            </tbody>

                            {{-- STRATEGIC FUNCTIONS --}}
                            <tbody id="strategic-functions">
                            <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <td colspan="9" class="px-6 py-3 relative">
                                    <div class="flex justify-between items-center">
                                        <span class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 bg-purple-500 rounded-full"></div>
                                            Strategic Functions
                                        </span>
                                        <div class="dropdown">
                                            <button class="text-gray-300 hover:text-purple-500 transition-colors" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-plus-circle text-lg"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-2xl p-2">
                                                <li><a class="dropdown-item rounded-xl py-2 px-4 text-xs font-bold" href="#" onclick="appendNewRow('#strategic-functions')">Add Single Row</a></li>
                                                <li><hr class="dropdown-divider opacity-50"></li>
                                                <li class="px-3 py-2">
                                                    <div class="flex gap-2" onclick="event.stopPropagation()">
                                                        <input type="number" class="form-control text-xs w-16 rounded-lg border-gray-200" value="5" id="strategic-rows-input">
                                                        <button class="btn btn-orange bg-purple-500 hover:bg-purple-600 text-[10px] whitespace-nowrap px-4 border-0" onclick="appendMultipleRows('#strategic-functions', '#strategic-rows-input')">Bulk Add</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @include('ipcr.partials.row')
                            </tbody>
                        </table>
                    </div>

                    {{-- 🔹 Supervisor Comments Section --}}
                    <div id="supervisorCommentSection" class="mt-6 p-6 bg-orange-50/50 rounded-2xl border border-orange-100 hidden">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-comment-dots text-orange-500"></i>
                            <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Supervisor's Remarks & Feedback <span class="text-red-500">*</span></h4>
                        </div>
                        <textarea id="supervisor_comments" 
                                  class="w-full p-4 bg-white border border-gray-100 rounded-xl text-xs font-bold text-gray-700 focus:ring-4 focus:ring-orange-500/5 focus:border-orange-500 outline-none transition-all placeholder:text-gray-300"
                                  placeholder="Enter mandatory comments or feedback here before approving..."
                                  rows="3"></textarea>
                    </div>

                <div class="bg-gray-50/50 p-8 border-t border-gray-100 flex justify-between items-center rounded-b-3xl">
                    <button class="btn btn-outline-modern bg-white text-gray-500 border-gray-200 px-8 py-3" data-bs-dismiss="modal">
                        Close Editor
                    </button>
                    
                    <div class="flex gap-3">
                        <button id="printBtn" class="btn btn-outline-modern bg-white border-gray-200 px-6 py-3" onclick="printIpcr()">
                            <i class="fas fa-print mr-2 opacity-50"></i> Print Preview
                        </button>
                        <button id="approveBtn" class="btn btn-orange bg-emerald-500 hover:bg-emerald-600 px-8 py-3 shadow-lg shadow-emerald-500/20" style="display: none;" onclick="handleApprove()">
                            Approve Now
                        </button>
                        <button id="handleSaveBtn" class="btn btn-orange px-10 py-3 shadow-lg shadow-orange-500/20" onclick="handleSaveOrSubmit()">
                            <span id="saveBtnText">Save Document</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template for dynamic row --}}
<template id="function-row-template">
    @include('ipcr.partials.row')
</template>


@pushOnce('scripts')
<script>
    window.authUserId = window.authUserId || {{ auth()->id() ?? 1 }};

    $(document).ready(function () {

        // Handle modal shown event
        $('#ipcrModal').on('shown.bs.modal', function () {
            // If creating new IPCR, set semester and year from index page
            if (window.currentCreatingSemester && window.currentCreatingYear) {
                $('#ipcrSemester').val(window.currentCreatingSemester);
                $('#ipcrYear').val(window.currentCreatingYear);
                $('#ipcrId').val('');
                $('#dateDone').val(new Date().toISOString().split('T')[0]);
                
                const semesterText = window.currentCreatingSemester == 1 ? '1st Semester - Target Setting' : '2nd Semester - Accomplishment';
                $('#modalTitle').text(`IPCR ${window.currentCreatingYear} - ${semesterText}`);
                
                updateStatusIndicator('Draft Target');
                toggleSemesterFields('Draft Target');
                updateButtonText('Draft Target');
                
                // Clear existing rows
                $("#core-functions tr:gt(0)").remove();
                $("#support-functions tr:gt(0)").remove();
                $("#strategic-functions tr:gt(0)").remove();
                
                // Add initial row
                appendNewRow('#core-functions');
                appendNewRow('#support-functions');
                appendNewRow('#strategic-functions');

                // loadSupervisors(); // Removed as we use automated values
            }
        });

        function loadSupervisors(selectedSupervisorId = null, selectedDHId = null, selectedPMTId = null) {
            fetch('/api/ipcr/supervisors')
                .then(res => res.json())
                .then(users => {
                    const svSelect = $('#supervisor_id');
                    const dhSelect = $('#division_head');
                    const pmtSelect = $('#highest_supervisor');

                    svSelect.empty().append('<option value="">Select Supervisor</option>');
                    dhSelect.empty().append('<option value="">Select Division Head</option>');
                    pmtSelect.empty().append('<option value="">Select PMT</option>');

                    users.forEach(user => {
                        svSelect.append(`<option value="${user.id}" ${selectedSupervisorId == user.id ? 'selected' : ''}>${user.name}</option>`);
                        dhSelect.append(`<option value="${user.id}" ${selectedDHId == user.id ? 'selected' : ''}>${user.name}</option>`);
                        pmtSelect.append(`<option value="${user.id}" ${selectedPMTId == user.id ? 'selected' : ''}>${user.name}</option>`);
                    });
                });
        }


        $(document).on("click", ".delete-row-btn", function () {
            confirmAction(
                'Delete Row?', 
                'Are you sure you want to remove this row? This cannot be undone.', 
                'DELETE', 
                () => { $(this).closest('tr').remove(); }
            );
        });

        // 🟢 Auto-compute Average Rating
        $(document).on('input', '.q-rating, .e-rating, .t-rating', function() {
            const row = $(this).closest('tr');
            const q = parseFloat(row.find('.q-rating').val()) || 0;
            const e = parseFloat(row.find('.e-rating').val()) || 0;
            const t = parseFloat(row.find('.t-rating').val()) || 0;

            const ratings = [q, e, t].filter(v => v > 0);
            const average = ratings.length > 0 ? (ratings.reduce((a, b) => a + b) / ratings.length) : 0;
            
            row.find('.a-rating').val(average > 0 ? average.toFixed(2) : '0.0');
        });
    });

    function handleApprove() {
        const ipcrId = $('#ipcrId').val();
        const status = $('#statusValue').val();
        let action = 'APPROVE';
        
        if (status === 'Target Submitted') action = 'APPROVE TARGET';
        else if (status === 'Accomplishment Submitted') action = 'PERFORM SUPERVISOR APPROVAL';
        else if (status === 'Supervisor Approved') action = 'PERFORM DIVISION HEAD APPROVAL';
        else if (status === 'Division Head Approved') action = 'PERFORM FINAL PMT APPROVAL';
        
        const comments = $('#supervisor_comments').val().trim();
        
        // Comments are mandatory only during accomplishment/rating approval stages
        const isEvaluationPhase = ['Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved'].includes(status);
        
        if (isEvaluationPhase && !comments) {
            showAlert('Missing Feedback', 'Please provide a comment or feedback before approving this assessment. This is mandatory for the evaluation phase.', 'warning');
            $('#supervisor_comments').focus();
            return;
        }

        confirmAction(
            'Confirm Approval',
            `Are you sure you want to ${action} for this IPCR?`,
            'APPROVE',
            () => {
                fetch(`/api/ipcr/${ipcrId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        user_id: authUserId,
                        comments: comments
                    })
                })
                .then(res => res.json())
                .then(data => {
                    toast('IPCR Approved successfully!');
                    $('#ipcrModal').modal('hide');
                    if (typeof loadIpcrBySemester === 'function') loadIpcrBySemester();
                    if (typeof fetchApprovals === 'function') fetchApprovals();
                })
                .catch(err => {
                    console.error('Error approving IPCR:', err);
                    showAlert('Error', 'Failed to approve IPCR.', 'error');
                });
            }
        );
    }

    function handleSaveOrSubmit() {
        const btnText = $('#saveBtnText').text();
        const isSubmit = btnText.includes('Submit');
        saveChanges(isSubmit);
    }

    function saveChanges(isSubmit = false) {
        const semester = parseInt($('#ipcrSemester').val());
        const year = parseInt($('#ipcrYear').val());
        const ipcrId = $('#ipcrId').val();
        
        let payload = {
            ipcr: {
                userid: window.authUserId,
                // Supervisor fields handled in backend
                // supervisor_id: ..., 
                // division_head: ...,
                // highest_supervisor: ...,
                period_from: "2025-12-12",
                period_to:"2025-12-15",
                year: year,
                semester: semester,
                date_done: $('#dateDone').val(),
                status: $('#statusValue').val() || "Draft Target",
                core_percentage_distribution: 50,
                support_percentage_distribution: 10,
                strategic_percentage_distribution: 40
            },
            core_functions: collectRows("#core-functions"),
            support_functions: collectRows("#support-functions"),
            strategic_functions: collectRows("#strategic-functions")
        };

        console.log("Saving IPCR payload:", payload);

        const method = ipcrId ? "PUT" : "POST";
        const url = ipcrId ? `/api/ipcr/${ipcrId}` : "/api/ipcr";

        fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    if (res.status === 422) {
                        console.error("Validation errors:", data.errors);
                        let errMsg = "";
                        for (let field in data.errors) {
                            errMsg += `• ${data.errors[field].join(', ')}\n`;
                        }
                        showAlert('Validation Failed', errMsg, 'error');
                    } else {
                        throw new Error(data.message || "Failed to save IPCR");
                    }
                    return;
                }
                return data;
            })
            .then(data => {
                if (data) {
                    if (isSubmit) {
                        return fetch(`/api/ipcr/${data.id || ipcrId}/submit`, {
                            method: 'POST',
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                                "Accept": "application/json"
                            }
                        }).then(res => res.json());
                    }
                    return data;
                }
            })
            .then(data => {
                if (data) {
                    toast(isSubmit ? "IPCR submitted successfully!" : "IPCR saved successfully!");
                    $('#ipcrModal').modal('hide');
                    if (typeof loadIpcrBySemester === 'function') {
                        loadIpcrBySemester();
                    }
                }
            })
            .catch(err => {
                console.error("Error saving/submitting IPCR:", err);
                showAlert('System Error', err.message, 'error');
            });
    }

    function printIpcr() {
        const ipcrId = $('#ipcrId').val();
        if (!ipcrId) {
            showAlert('Action Required', 'Please save the IPCR first before printing.', 'info');
            return;
        }
        // Open print view in new tab
        window.open(`/ipcr/print/${ipcrId}`, '_blank');
    }


    function collectRows(tbodyId) {
        let rows = [];
        $(tbodyId + " tr").each(function () {
            // Skip section header rows or any row with a colspan td
            if ($(this).find("td[colspan]").length) return;
            
            let tds = $(this).find("td");
            if (tds.length) {
                rows.push({
                    id: $(this).find(".row-id").val() || null,
                    output: $(tds[0]).find("textarea").val() || '',
                    success_indicator: $(tds[1]).find("textarea").val() || '',
                    actual_accomplishment: $(tds[2]).find("textarea").val() || '',
                    quantity_rating: $(tds[3]).find("input").val() || '',
                    efficiency_rating: $(tds[4]).find("input").val() || '',
                    timeliness_rating: $(tds[5]).find("input").val() || '',
                    average_rating: $(tds[6]).find("input").val() || '',
                    remarks: $(tds[7]).find("textarea").val() || ''
                });
            }
        });
        return rows;
    }


    function loadIPCR(ipcrId) {
        fetch(`/api/ipcr/${ipcrId}`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch IPCR details');
                return response.json();
            })
            .then(data => {
                const ipcr = data;
                
                // Set hidden fields
                $('#ipcrId').val(ipcr.id);
                $('#displayIpcrId').text(ipcr.id || 'NEW_DOCUMENT');
                $('#ipcrSemester').val(ipcr.semester || 1);
                $('#ipcrYear').val(ipcr.year || new Date().getFullYear());
                
                $('#dateDone').val(ipcr.date_done || '');
                
                // Update modal title
                const semesterText = ipcr.semester == 1 ? '1st Semester' : '2nd Semester';
                $('#modalTitle').text(`IPCR ${ipcr.year || ''} - ${semesterText}`);
                
                // Toggle fields and status
                const isOwner = (ipcr.userid == authUserId);
                updateStatusIndicator(ipcr.status);
                updateButtonText(ipcr.status);

                $("#core-functions tr:gt(0)").remove();
                $("#support-functions tr:gt(0)").remove();
                $("#strategic-functions tr:gt(0)").remove();

                populateFunctionRows("#core-functions", ipcr.core_functions || ipcr.coreFunctions || []);
                populateFunctionRows("#support-functions", ipcr.support_functions || ipcr.supportFunctions || []);
                populateFunctionRows("#strategic-functions", ipcr.strategic_functions || ipcr.strategicFunctions || []);
                
                // 🔐 Apply field locking AFTER rows are in the DOM
                toggleSemesterFields(ipcr.status, isOwner);

                // loadSupervisors(ipcr.supervisor_id, ipcr.division_head, ipcr.highest_supervisor);
                
                // --- Supervisor Comments Logic ---
                $('#supervisor_comments').val(ipcr.comments || '');
                
                // Comments only visible after Accomplishment Submitted
                const showComments = ['Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved', 'PMT Approved'].includes(ipcr.status);
                
                // Show/Hide Approve button based on current role in the chain
                let canApprove = false;
                if (ipcr.status === 'Target Submitted' || ipcr.status === 'Accomplishment Submitted') {
                    canApprove = (ipcr.supervisor_id == authUserId);
                } else if (ipcr.status === 'Supervisor Approved') {
                    canApprove = (ipcr.division_head == authUserId);
                } else if (ipcr.status === 'Division Head Approved') {
                    canApprove = (ipcr.highest_supervisor == authUserId);
                }

                if (canApprove) {
                    $('#approveBtn').show();
                    // Don't show comment field if only approving Targets
                    if (ipcr.status !== 'Target Submitted') {
                        $('#supervisorCommentSection').removeClass('hidden');
                        $('#supervisor_comments').prop('disabled', false).removeClass('bg-gray-100 cursor-not-allowed');
                    } else {
                        $('#supervisorCommentSection').addClass('hidden');
                    }
                } else {
                    $('#approveBtn').hide();
                    if (showComments && ipcr.comments) {
                        $('#supervisorCommentSection').removeClass('hidden');
                        $('#supervisor_comments').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
                    } else {
                        $('#supervisorCommentSection').addClass('hidden');
                    }
                }
                
                // Disable save button if not owner and not in editable status
                const isEditableByOwner = ['Draft Target', 'Target Approved', 'Draft Accomplishment'].includes(ipcr.status);
                if (ipcr.userid != authUserId || !isEditableByOwner) {
                    $('#handleSaveBtn').hide();
                } else {
                    $('#handleSaveBtn').show();
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    function toggleSemesterFields(status, isOwner = true) {
        // Always ensure all columns are visible
        $('.accomplishment-col, .rating-col, .remarks-col').show();
        $('.accomplishment-field, .rating-field, .remarks-field').show();
        
        // Reset colspan for section headers to 9 (full width)
        $('#ipcrModal table tbody tr').each(function() {
            if ($(this).find("td[colspan]").length) {
                 $(this).find("td[colspan]").attr('colspan', 9);
            }
        });
        
        // Column widths are now handled by classes/styles in the HTML template.

        // 👮 Ownership Check: If NOT the creator, strictly lock EVERYTHING Table-related
        if (!isOwner) {
            $('#ipcrModal table input, #ipcrModal table textarea, #ipcrModal table select').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
            $('.delete-row-btn, .dropdown').hide(); // Hide Add/Delete buttons
            return;
        }

        // Show modification buttons for owners
        $('.delete-row-btn, .dropdown').show();
        
        // Target Phase Logic
        const isTargetPhase = status === 'Draft Target';
        const isEvaluationPhase = ['Target Approved', 'Draft Accomplishment', 'Accomplishment Submitted'].includes(status);
        const isReadOnly = ['Target Submitted', 'Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved', 'PMT Approved'].includes(status);

        // Inputs for the "Success Indicators" / "Output" (Setting phase)
        const settingInputs = $('td:nth-child(1) textarea, td:nth-child(2) textarea');
        
        // Inputs for accomplishments and ratings
        const targetContainers = $('.accomplishment-field, .rating-field, .remarks-field');
        const targetInputs = targetContainers.find('input, textarea, select');
        
        if (isReadOnly) {
            // Check if we can still approve (supervisor case)
            const ipcrId = $('#ipcrId').val();
            // We'll rely on loadIPCR's canApprove logic for the comments field specifically
            // but we disable table inputs globally for read-only
            $('#ipcrModal table input, #ipcrModal table textarea, #ipcrModal table select').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
            return;
        }

        if (status === 'Draft Target') {
            // Can edit targets, but STRCTLY NOT accomplishments or ratings
            settingInputs.prop('disabled', false).removeClass('bg-gray-100 cursor-not-allowed');
            // We use a broader selector to ensure all evaluation fields are locked
            $('.accomplishment-field textarea, .rating-field input, .remarks-field textarea').prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
        } else if (status === 'Target Approved' || status === 'Draft Accomplishment') {
            // Targets are LOCKED, but can edit accomplishments
            settingInputs.prop('disabled', true).addClass('bg-gray-100 cursor-not-allowed');
            targetInputs.prop('disabled', false).removeClass('bg-gray-100 cursor-not-allowed');
        }
    }

    function updateButtonText(status) {
        const btnText = $('#saveBtnText');
        if (status === 'Draft Target') {
            btnText.text('Submit Target').show().closest('button').show();
        } else if (status === 'Target Approved' || status === 'Draft Accomplishment') {
            btnText.text('Submit Accomplishment').show().closest('button').show();
        } else if (['Target Submitted', 'Accomplishment Submitted', 'Supervisor Approved', 'Division Head Approved', 'PMT Approved'].includes(status)) {
            btnText.hide().closest('button').hide();
        } else {
            btnText.text('Save Changes').show().closest('button').show();
        }
    }

    function updateStatusIndicator(currentStatus) {
        const statuses = [
            'Draft Target',
            'Target Submitted',
            'Target Approved',
            'Draft Accomplishment',
            'Accomplishment Submitted',
            'Supervisor Approved',
            'Division Head Approved',
            'PMT Approved'
        ];
        
        // Update hidden status value for payload
        if (!$('#statusValue').length) {
            $('body').append(`<input type="hidden" id="statusValue" value="${currentStatus}">`);
        } else {
            $('#statusValue').val(currentStatus);
        }

        const currentIndex = statuses.indexOf(currentStatus);

        $('.status-step').each(function (index) {
            const circle = $(this).find('.status-circle');
            const line = $(this).find('.status-line');
            const label = $(this).find('span');

            // Reset
            circle.removeClass('bg-orange-500 border-orange-500 bg-emerald-500 border-emerald-500 bg-white border-gray-200 text-white text-gray-400 scale-110 shadow-lg');
            label.removeClass('text-orange-600 text-emerald-600 text-gray-400 font-black opacity-100');
            line.addClass('hidden');

            if (index < currentIndex || currentStatus === 'PMT Approved') {
                // Completed
                circle.addClass('bg-emerald-500 border-emerald-500 text-white').html('<i class="fas fa-check text-[10px]"></i>');
                label.addClass('text-emerald-600 font-bold opacity-100');
                if (index < currentIndex || (currentStatus === 'PMT Approved' && index < statuses.length - 1)) {
                    line.removeClass('hidden');
                }
            } else if (index === currentIndex) {
                // Current
                circle.addClass('bg-orange-500 border-orange-500 text-white scale-110 shadow-lg shadow-orange-500/20');
                label.addClass('text-orange-600 font-black opacity-100');
            } else {
                // Pending
                circle.addClass('bg-white border-gray-200 text-gray-300').text(index + 1);
                label.addClass('text-gray-400');
            }
        });
    }

    function populateFunctionRows(containerId, rows) {
        rows.forEach(row => {
            const newRow = $($("#function-row-template").html());
            const tds = newRow.find("td");

            newRow.find(".row-id").val(row.id);
            $(tds[0]).find("textarea").val(row.output);
            $(tds[1]).find("textarea").val(row.success_indicator);
            $(tds[2]).find("textarea").val(row.actual_accomplishment);
            newRow.find(".q-rating").val(row.quantity_rating);
            newRow.find(".e-rating").val(row.efficiency_rating);
            newRow.find(".t-rating").val(row.timeliness_rating);
            newRow.find(".a-rating").val(row.average_rating);
            $(tds[7]).find("textarea").val(row.remarks);

            $(containerId).append(newRow);
        });
    }

    function appendNewRow(id) {
        $(id).append($("#function-row-template").html());
        // Re-apply field restrictions to the new row
        const status = $('#statusValue').val() || 'Draft Target';
        toggleSemesterFields(status);
    }

    function appendMultipleRows(id, inputId) {
        const count = parseInt($(inputId).val());
        if (count > 0) {
            for (let i = 0; i < count; i++) {
                $(id).append($("#function-row-template").html());
            }
            // Re-apply field restrictions to all new rows
            const status = $('#statusValue').val() || 'Draft Target';
            toggleSemesterFields(status);
            
            // Close dropdown
            $(inputId).closest('.dropdown-menu').removeClass('show');
            $(inputId).closest('.dropdown').find('[data-bs-toggle="dropdown"]').removeClass('show');
        }
    }
</script>
@endpushOnce
