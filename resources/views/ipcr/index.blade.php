@extends('layouts.app')

@section('title', 'IPCR')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Top Bar with Page Title and Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">IPCR Management</h1>
            <p class="text-xs text-gray-400 font-medium">Individual Performance Commitment and Review</p>
        </div>
        <div class="flex gap-2 items-center">
            <button class="btn btn-outline-modern flex items-center gap-2" onclick="location.reload()">
                <i class="fas fa-sync-alt text-gray-400"></i> Refresh
            </button>
            <button class="btn btn-orange flex items-center gap-2 shadow-sm" onclick="createIpcr()">
                <i class="fas fa-plus"></i> Create IPCR
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="card-modern p-8 flex flex-col items-center text-center relative overflow-hidden">
                <!-- Decorative background blob -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-orange-50 rounded-full blur-3xl opacity-60"></div>
                
                <div class="w-32 h-32 mb-6 relative z-10">
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}"
                         class="rounded-full w-full h-full object-cover border-4 border-white shadow-md">
                    <div class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full"></div>
                </div>
                
                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ Auth::user()->name }}</h2>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.2em] mb-6">{{ Auth::user()->designation_name }}</p>
                
                <div class="w-full space-y-4 pt-6 mt-2 border-t border-gray-100">
                    <div class="flex items-center gap-4 bg-gray-50/50 p-3 rounded-2xl border border-gray-50/50">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                            <i class="fas fa-sitemap text-sm"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Section / Department</p>
                            <p class="text-xs font-bold text-gray-700 leading-tight">{{ Auth::user()->section_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 bg-gray-50/50 p-3 rounded-2xl border border-gray-50/50">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-500 shrink-0">
                            <i class="fas fa-building text-sm"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Division / Office</p>
                            <p class="text-xs font-bold text-gray-700 leading-tight">{{ Auth::user()->division_name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section: Filters & Logs -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Filter Card -->
            <div class="card-modern p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <div class="flex-1 sm:flex-none">
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Year</label>
                            <select id="yearFilter" class="form-select text-sm border-gray-200 rounded-lg focus:ring-orange-200 focus:border-orange-500 w-full">
                                @for ($y = date('Y'); $y >= 2023; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex-1 sm:flex-none">
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Semester</label>
                            <select id="semesterFilter" class="form-select text-sm border-gray-200 rounded-lg focus:ring-orange-200 focus:border-orange-500 w-full">
                                <option value="1">1st Semester</option>
                                <option value="2">2nd Semester</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full sm:w-auto justify-end">
                        <button id="viewIpcrBtn" class="flex-1 sm:flex-none btn btn-outline-modern flex items-center justify-center gap-2 px-6">
                            <i class="fas fa-eye text-primary-orange"></i> View
                        </button>
                        <button id="deleteIpcrBtn" class="flex-1 sm:flex-none btn btn-outline-modern border-red-100 hover:bg-red-50 text-red-500 flex items-center justify-center gap-2 px-6" onclick="deleteIpcr()">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Logs Card -->
            <div class="card-modern">
                <div class="p-4 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center">
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">History Logs</h3>
                    <span class="text-[10px] text-gray-400">Showing recent activity</span>
                </div>
                
                <div id="ipcrLogsContainer" class="p-6">
                    <div class="text-center text-gray-300 text-sm py-12">
                        <i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i>
                        <p class="font-medium">No IPCR selected for this period.</p>
                        <p class="text-xs">Select a year and semester to view history.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container -->
@include('ipcr.modal')


@endsection

@push('scripts')
    <script>
        window.ipcrApiBaseUrl = window.ipcrApiBaseUrl || @json(url('/api/ipcr'));
        window.ipcrBySemesterUrl = window.ipcrBySemesterUrl || @json(url('/api/ipcr/by-semester'));
        let currentIpcrId = null;
        let currentYear = new Date().getFullYear();
        let currentSemester = 1;

        $(document).ready(function () {
            // Initialize filters
            currentYear = parseInt($('#yearFilter').val());
            currentSemester = parseInt($('#semesterFilter').val());
            
            // Load IPCR when filters change
            $('#yearFilter, #semesterFilter').on('change', function() {
                currentYear = parseInt($('#yearFilter').val());
                currentSemester = parseInt($('#semesterFilter').val());
                loadIpcrBySemester();
            });

            $('#viewIpcrBtn').click(function () {
                if (currentIpcrId) {
                    window.ipcrModalMode = 'view';
                    loadIPCR(currentIpcrId);
                    $('#ipcrModal').modal('show');
                } else {
                    showAlert('No Document', 'No IPCR found for the selected year and semester.', 'info');
                }
            });

            // Load on page load
            loadIpcrBySemester();
        });

        function loadIpcrBySemester() {
            const userId = {{ Auth::id() }};
            const container = $('#ipcrLogsContainer');
            
            fetch(`${window.ipcrBySemesterUrl}?user_id=${userId}&year=${currentYear}&semester=${currentSemester}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.id) {
                        currentIpcrId = data.id;
                        loadIpcrLogs(data.id);
                    } else {
                        currentIpcrId = null;
                        displayNoIpcrMessage();
                    }
                })
                .catch(err => {
                    console.error('Error loading IPCR:', err);
                    currentIpcrId = null;
                    displayNoIpcrMessage();
                });
        }

        function loadIpcrLogs(ipcrId) {
            fetch(`${window.ipcrApiBaseUrl}/${ipcrId}/logs`)
                .then(response => response.json())
                .then(logs => {
                    displayLogs(logs);
                })
                .catch(err => {
                    console.error('Error loading logs:', err);
                    displayNoIpcrMessage();
                });
        }

        function displayLogs(logs) {
            const container = $('#ipcrLogsContainer');
            container.empty();
            
            if (logs.length === 0) {
                container.html('<div class="text-center text-gray-300 text-sm py-12"><p>No logs available for this IPCR.</p></div>');
                return;
            }

            const timelineHtml = `
                <div class="relative space-y-6 before:absolute before:inset-0 before:ml-4 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-orange-100 before:via-gray-100 before:to-transparent">
                    ${logs.map(log => {
                        const createdAt = new Date(log.created_at);
                        const formattedDate = createdAt.toLocaleString('en-US', {
                            month: 'short', day: '2-digit', year: 'numeric'
                        });
                        const formattedTime = createdAt.toLocaleString('en-US', {
                            hour: '2-digit', minute: '2-digit', hour12: true
                        });

                        return `
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
                            <!-- Icon -->
                            <div class="flex items-center justify-center w-8 h-8 rounded-full border border-white bg-white shadow-sm shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                                <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                            </div>
                            <!-- Card -->
                            <div class="w-[calc(100%-3rem)] md:w-[calc(100%-4rem)] bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                                <div class="flex items-center justify-between space-x-2 mb-1">
                                    <div class="font-bold text-gray-900 text-xs">${log.subject}</div>
                                    <time class="text-[10px] font-bold text-gray-400 uppercase">${formattedDate} ${formattedTime}</time>
                                </div>
                                <div class="text-gray-500 text-[11px] leading-relaxed">${log.content || ''}</div>
                            </div>
                        </div>`;
                    }).join('')}
                </div>`;
            
            container.html(timelineHtml);
        }

        function displayNoIpcrMessage() {
            $('#ipcrLogsContainer').html(
                '<div class="text-center text-gray-300 text-sm py-12"><i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i><p class="font-medium">No IPCR found for this period.</p><p class="text-xs">Click CREATE IPCR to get started or select another semester.</p></div>'
            );
        }

        function createIpcr(){
            window.ipcrModalMode = 'create';
            window.currentCreatingSemester = currentSemester;
            window.currentCreatingYear = currentYear;
            $('#ipcrId').val('');
            $('#ipcrModal').modal('show');
        }

        function deleteIpcr() {
            if (!currentIpcrId) {
                showAlert('Action Required', 'No IPCR found for the selected year and semester.', 'info');
                return;
            }

            confirmAction(
                'Delete IPCR?',
                'Are you sure you want to delete this IPCR and all its targets/accomplishments? This action cannot be undone.',
                'DELETE',
                () => {
                    fetch(`${window.ipcrApiBaseUrl}/${currentIpcrId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        toast(data.message || 'IPCR deleted successfully.');
                        loadIpcrBySemester();
                    })
                    .catch(err => {
                        console.error('Error deleting IPCR:', err);
                        showAlert('Error', 'Failed to delete IPCR: ' + err.message, 'error');
                    });
                }
            );
        }

    </script>
@endpush
