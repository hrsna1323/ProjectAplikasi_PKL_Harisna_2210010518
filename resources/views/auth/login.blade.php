<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Konten SKPD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 bg-pattern">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 items-center justify-center p-12">
            <div class="max-w-lg text-center">
                <div class="animate-float mb-8">
                    <div class="w-32 h-32 bg-white/10 backdrop-blur rounded-3xl flex items-center justify-center mx-auto shadow-2xl">
                        <i data-lucide="layout-dashboard" class="w-16 h-16 text-white"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-white mb-4">SKPD Content</h1>
                <h2 class="text-2xl font-semibold text-blue-200 mb-6">Management System</h2>
                <p class="text-blue-100 text-lg leading-relaxed">
                    Sistem manajemen konten terpadu untuk memantau dan mengelola publikasi konten SKPD secara efisien dan terstruktur.
                </p>
                <div class="mt-12 flex items-center justify-center gap-8">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="building-2" class="w-7 h-7 text-white"></i>
                        </div>
                        <p class="text-white font-semibold">Multi SKPD</p>
                        <p class="text-blue-200 text-sm">Kelola banyak unit</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="check-circle" class="w-7 h-7 text-white"></i>
                        </div>
                        <p class="text-white font-semibold">Verifikasi</p>
                        <p class="text-blue-200 text-sm">Proses approval</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="bar-chart-3" class="w-7 h-7 text-white"></i>
                        </div>
                        <p class="text-white font-semibold">Monitoring</p>
                        <p class="text-blue-200 text-sm">Pantau kuota</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <!-- Mobile Header -->
                    <div class="lg:hidden bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="layout-dashboard" class="w-8 h-8 text-white"></i>
                        </div>
                        <h1 class="text-xl font-bold text-white">SKPD Content</h1>
                        <p class="text-blue-100 text-sm">Management System</p>
                    </div>

                    <!-- Form -->
                    <div class="p-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-800">Selamat Datang! 👋</h2>
                            <p class="text-gray-500 mt-2">Masuk ke akun Anda untuk melanjutkan</p>
                        </div>

                        @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                            @foreach($errors->all() as $error)
                            <p class="flex items-center gap-2 text-sm">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                {{ $error }}
                            </p>
                            @endforeach
                        </div>
                        @endif

                        <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                           class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white"
                                           placeholder="nama@email.com">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="password" id="password" name="password" required
                                           class="w-full pl-12 pr-12 py-3.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white"
                                           placeholder="••••••••">
                                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                        <i data-lucide="eye" id="eyeIcon" class="w-5 h-5 text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-600">Ingat saya</span>
                                </label>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3.5 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40">
                                <i data-lucide="log-in" class="w-5 h-5"></i>
                                Masuk
                            </button>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 pb-8 text-center">
                        <div class="border-t border-gray-100 pt-6">
                            <p class="text-sm text-gray-500">Kabupaten Tanah Bumbu</p>
                            <p class="text-xs text-gray-400 mt-1">&copy; {{ date('Y') }} SKPD Content Management System</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
