<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-SPMS</title>
    
    {{-- ✅ TailwindCSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- ✅ Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    {{-- ✅ Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style type="text/tailwindcss">
        @layer base {
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f7f8f9;
            }
        }

        @layer components {
            .bg-gradient-modern {
                background: linear-gradient(135deg, #f06a38 0%, #ff8e5d 100%);
            }

            .card-auth {
                background: white;
                border-radius: 32px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
                border: 1px solid #f1f1f1;
            }

            .input-modern {
                @apply w-full px-5 py-4 bg-white border-2 border-gray-100 rounded-2xl text-sm font-medium transition-all;
                @apply focus:ring-4 focus:ring-orange-50 focus:bg-white focus:border-orange-500 outline-none;
            }

            .btn-auth {
                @apply w-full py-4 bg-gray-900 border-2 border-gray-900 text-white rounded-2xl font-bold text-sm transition-all;
                @apply hover:bg-black hover:shadow-xl hover:shadow-gray-200 active:scale-[0.98];
            }

            .decorative-blob {
                position: absolute;
                width: 400px;
                height: 400px;
                background: rgba(240, 106, 56, 0.05);
                filter: blur(80px);
                border-radius: 50%;
                z-index: -1;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Decorative elements -->
    <div class="decorative-blob -top-20 -left-20"></div>
    <div class="decorative-blob -bottom-20 -right-20"></div>

    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        
        <!-- Left Side: Branding -->
        <div class="hidden lg:block space-y-10">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/img_csmc.jpg') }}" alt="CSMC Logo" class="w-16 h-16 object-contain rounded-2xl shadow-lg ring-4 ring-white">
                    <img src="{{ asset('img/img_doh.png') }}" alt="DOH Logo" class="w-16 h-16 object-contain">
                </div>
                <div class="h-12 w-px bg-gray-200 mx-2"></div>
                <span class="text-3xl font-black text-gray-900 tracking-tight">E-SPMS</span>
            </div>
            
            <div class="space-y-4">
                <p class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] ml-1">CEBU SOUTH MEDICAL CENTER</p>
                <h1 class="text-5xl font-black text-gray-900 leading-[1.1]">
                    Manage <span class="text-primary-orange">Performance</span> <br>With Confidence.
                </h1>
                <p class="text-gray-400 font-medium text-lg max-w-md">
                    The next generation Strategic Performance Management System for modern government agencies.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="card-auth p-10 lg:p-12">
                <div class="mb-10 lg:hidden flex justify-center items-center gap-4">
                    <img src="{{ asset('img/img_csmc.jpg') }}" alt="CSMC Logo" class="w-14 h-14 object-contain rounded-xl">
                    <img src="{{ asset('img/img_doh.png') }}" alt="DOH Logo" class="w-14 h-14 object-contain">
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Welcome back</h2>
                    <p class="text-gray-400 text-sm font-medium">Please enter your credentials to continue</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl">
                        @foreach($errors->all() as $error)
                            <p class="text-xs font-bold text-red-500">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Username</label>
                        <div class="relative">
                            <i class="far fa-user absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="username" class="input-modern pl-12">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Password</label>
                        </div>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="password" name="password" class="input-modern pl-12">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn-auth">
                            Login into System
                        </button>
                    </div>
                </form>
            </div>
            
            <p class="mt-8 text-center text-xs text-gray-400 font-medium">
                &copy; {{ date('Y') }} E-SPMS Project | Strategic Performance Intelligence
            </p>
        </div>
    </div>

</body>
</html>
