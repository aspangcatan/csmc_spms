<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-SPMS System')</title>

    {{-- ✅ TailwindCSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ✅ jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- ✅ Bootstrap 5 (optional if you use modals, dropdowns, etc.) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- ✅ Font Awesome (optional icons) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{-- ✅ SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup-modern {
            border-radius: 24px !important;
            padding: 2rem !important;
            font-family: 'Inter', sans-serif !important;
        }
        .swal2-title-modern {
            font-weight: 800 !important;
            letter-spacing: -0.025em !important;
            color: #111827 !important;
        }
        .swal2-confirm-modern {
            background-color: #f06a38 !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            font-size: 11px !important;
            padding: 12px 24px !important;
            box-shadow: 0 10px 15px -3px rgba(240, 106, 56, 0.2) !important;
        }
    </style>

    {{-- ✅ CSRF Token for AJAX / Fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f8f9;
            color: #1a1e23;
        }

        .text-primary-orange { color: #f06a38; }
        .bg-primary-orange { background-color: #f06a38; }
        
        .navbar-modern {
            background-color: white;
            border-bottom: 1px solid #edf1f5;
            padding: 0.75rem 1.5rem;
        }

        .nav-link-modern {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            transition: color 0.2s;
            padding: 0.5rem 1rem;
        }

        .nav-link-modern:hover {
            color: #f06a38;
        }

        .nav-link-modern.active {
            color: #f06a38;
            background: #fff5f2;
            border-radius: 8px;
        }

        .card-modern {
            background: white;
            border-radius: 16px;
            border: 1px solid #f1f1f1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .btn-orange {
            background-color: #f06a38;
            color: white;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.5rem 1rem;
            transition: opacity 0.2s;
            border: none;
        }

        .btn-orange:hover {
            opacity: 0.9;
            color: white;
        }

        .btn-outline-modern {
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.5rem 1rem;
        }

        .btn-outline-modern:hover {
            background: #f9fafb;
        }

        #row-context-menu {
            position: absolute;
            min-width: 120px;
            font-size: 13px;
        }

        .modal-xxl{
            max-width: 95%;
        }

        /* 🔹 Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #edf1f5;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            display: flex;
            items-center: center;
            gap: 0.75rem;
            border-bottom: 1px solid #f9fafb;
        }

        .sidebar-content {
            padding: 1.5rem 1rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            color: #64748b;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
            text-decoration: none;
        }

        .sidebar-item i {
            font-size: 1.125rem;
            width: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .sidebar-item:hover {
            background: #f8fafc;
            color: #f06a38;
            transform: translateX(4px);
        }

        .sidebar-item:hover i {
            transform: scale(1.1);
        }

        .sidebar-item.active {
            background: #fff5f2;
            color: #f06a38;
            box-shadow: 0 4px 12px rgba(240, 106, 56, 0.08);
        }

        .sidebar-item.active i {
            color: #f06a38;
        }

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .top-navbar {
            background: white;
            border-bottom: 1px solid #edf1f5;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 20px 0 50px rgba(0,0,0,0.1);
            }
            .main-content {
                margin-left: 0;
            }
            .top-navbar {
                justify-content: space-between;
                padding: 0 1.5rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen flex flex-col">

{{-- 🔹 Sidebar --}}
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="bg-primary-orange w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg shadow-orange-500/20">
            <i class="fas fa-layer-group text-lg"></i>
        </div>
        <span class="font-black text-gray-900 tracking-tighter text-2xl">E-SPMS</span>
    </div>
    
    <div class="sidebar-content">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-4">Main Menu</p>
        
        <a href="/dashboard-new" class="sidebar-item {{ Request::is('dashboard-new*') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <a href="/ipcr" class="sidebar-item {{ Request::is('ipcr') ? 'active' : '' }}">
            <i class="far fa-file-alt"></i>
            <span>IPCR</span>
        </a>

        @if(auth()->user()->isSectionHead())
        <a href="/spcr" class="sidebar-item {{ Request::is('spcr*') ? 'active' : '' }}">
            <i class="fas fa-sitemap"></i>
            <span>SPCR</span>
        </a>
        @endif

        @if(auth()->user()->isSupervisor())
        <a href="{{ route('ipcr.staff') }}" class="sidebar-item {{ Request::routeIs('ipcr.staff') ? 'active' : '' }}">
            <i class="fas fa-users-cog"></i>
            <span>Staff IPCR</span>
        </a>
        @endif

        @if(auth()->user()->isDivisionHead())
        <a href="{{ route('spcr.staff') }}" class="sidebar-item {{ Request::routeIs('spcr.staff') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i>
            <span>Staff SPCR</span>
        </a>
        @endif

        <div class="my-6 border-t border-gray-50 mx-4"></div>
        
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-4">Others</p>
        
        <a href="#" class="sidebar-item">
            <i class="far fa-calendar-check"></i>
            <span>Tasks</span>
        </a>
    </div>

    <div class="p-4 mt-auto">
        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">System Version</p>
            <p class="text-[11px] font-bold text-gray-900 leading-none">v2.4.0-build</p>
        </div>
    </div>
</div>

<div class="main-content">
    {{-- 🔹 Top Navbar --}}
    <header class="top-navbar">
        <button class="lg:hidden w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 mr-4" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="flex items-center gap-2">
            <div class="dropdown">
                <button class="flex items-center gap-3 p-1 rounded-2xl hover:bg-gray-50 transition-all border border-transparent hover:border-gray-100 group" type="button" data-bs-toggle="dropdown">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden shrink-0 shadow-sm transition-transform group-active:scale-95">
                        <img src="{{ Auth::user()->profile_photo_url }}" class="w-full h-full object-cover">
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-[9px] font-black text-gray-400 uppercase leading-none mb-1 tracking-wider">Account</p>
                        <p class="text-xs font-bold text-gray-900 leading-none">{{ Auth::user()->name }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] text-gray-300 ml-1 group-hover:text-orange-500 transition-colors hidden sm:block"></i>
                </button>
                
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-2xl rounded-3xl p-2 mt-2 min-w-[220px]">
                    <li class="px-4 py-3 border-b border-gray-50 mb-2">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Logged as</p>
                        <p class="text-xs font-bold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ Auth::user()->designation_name }}</p>
                    </li>
                    
                    <li>
                        <a class="dropdown-item rounded-2xl py-2.5 px-4 text-xs font-bold text-gray-700 hover:bg-orange-50 hover:text-orange-600 flex items-center gap-3" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                                <i class="fas fa-key text-[10px]"></i>
                            </div>
                            Change Password
                        </a>
                    </li>
                    
                    <li><hr class="dropdown-divider opacity-50 my-2"></li>
                    
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item rounded-2xl py-2.5 px-4 text-xs font-bold text-red-500 hover:bg-red-50 flex items-center gap-3 w-full">
                                <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center text-red-500">
                                    <i class="fas fa-sign-out-alt text-[10px]"></i>
                                </div>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

{{-- 🔹 Change Password Modal --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-3xl shadow-2xl overflow-hidden">
            <div class="modal-body p-0">
                <div class="bg-gray-900 p-8 text-white relative">
                    <div class="relative z-10">
                        <h3 class="text-xl font-black tracking-tight mb-1">Update Security</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Change your account password</p>
                    </div>
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <i class="fas fa-shield-alt text-6xl rotate-12"></i>
                    </div>
                </div>
                
                <form id="changePasswordForm" class="p-8 space-y-6 bg-white">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Current Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 text-[10px]"></i>
                            <input type="password" name="current_password" required
                                   class="w-full pl-12 pr-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-orange-50 focus:bg-white focus:border-orange-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">New Password</label>
                        <div class="relative">
                            <i class="fas fa-key absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 text-[10px]"></i>
                            <input type="password" name="new_password" required
                                   class="w-full pl-12 pr-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-orange-50 focus:bg-white focus:border-orange-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Confirm New Password</label>
                        <div class="relative">
                            <i class="fas fa-check-circle absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 text-[10px]"></i>
                            <input type="password" name="new_password_confirmation" required
                                   class="w-full pl-12 pr-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-orange-50 focus:bg-white focus:border-orange-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" class="flex-1 py-4 bg-gray-50 text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-gray-100 transition-all outline-none" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="flex-[2] py-4 bg-gray-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-black hover:shadow-xl hover:shadow-gray-200 transition-all outline-none">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const originalText = btn.text();
            
            btn.prop('disabled', true).text('Updating...');
            
            $.ajax({
                url: '/password/update',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    toast('Password updated successfully!');
                    $('#changePasswordModal').modal('hide');
                    $('#changePasswordForm')[0].reset();
                },
                error: function(xhr) {
                    const message = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    showAlert('Error', message, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
    }

    // Close sidebar on window resize if larger than lg
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('sidebar');
        const btn = document.querySelector('.lg\\:hidden');
        if (window.innerWidth <= 1024 && 
            !sidebar.contains(e.target) && 
            !btn.contains(e.target) && 
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });

    // ✅ Global SweetAlert Helpers
    window.toast = (title, icon = 'success') => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            },
            customClass: {
                popup: 'swal2-popup-modern shadow-2xl border border-gray-100',
                title: 'swal2-title-modern !text-sm !p-0'
            }
        });
        Toast.fire({ icon: icon, title: title });
    };

    window.confirmAction = (title, text, confirmText, callback) => {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmText || 'CONFIRM',
            cancelButtonText: 'CANCEL',
            customClass: {
                popup: 'swal2-popup-modern shadow-2xl border border-gray-100',
                title: 'swal2-title-modern',
                confirmButton: 'swal2-confirm-modern mx-2',
                cancelButton: 'btn-outline-modern mx-2 px-6 py-2 text-[11px] uppercase tracking-widest'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) callback();
        });
    };

    window.showAlert = (title, text, icon = 'info') => {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'OK',
            customClass: {
                popup: 'swal2-popup-modern shadow-2xl border border-gray-100',
                title: 'swal2-title-modern',
                confirmButton: 'swal2-confirm-modern'
            },
            buttonsStyling: false
        });
    };
</script>

    {{-- 🔹 Page Content --}}
    <main class="flex-grow p-4 md:p-8">
        @yield('content')
    </main>

    {{-- 🔹 Footer --}}
    <footer class="bg-white border-t border-gray-100 text-gray-400 text-center py-8 text-[10px] font-bold uppercase tracking-[0.2em]">
        &copy; {{ date('Y') }} E-SPMS System | Strategic Performance Management System
    </footer>
</div>

@stack('scripts')
</body>
</html>
