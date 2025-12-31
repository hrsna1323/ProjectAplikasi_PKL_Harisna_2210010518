<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Manajemen Konten SKPD')</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css'])
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-transition { transition: width 0.3s ease, transform 0.3s ease; }
        .content-transition { transition: margin-left 0.3s ease; }
        @media (max-width: 768px) {
            .sidebar-collapsed { transform: translateX(-100%); }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Overlay for Mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition fixed md:relative w-64 h-full bg-gradient-to-b from-blue-800 to-blue-900 text-white flex flex-col z-50">
            <div class="p-6 border-b border-blue-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">SKPD Content</h1>
                        <p class="text-xs text-blue-200">Management System</p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 mt-4 overflow-y-auto">
                @auth
                    <div class="px-4 mb-2">
                        <p class="text-xs text-blue-300 uppercase font-semibold tracking-wider">Menu Utama</p>
                    </div>
                    
                    <div class="space-y-1 px-3">
                        @if(auth()->user()->hasRole('Admin'))
                            <a href="{{ route('admin.dashboard') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="home" class="w-5 h-5"></i>
                                <span class="font-medium">Dashboard</span>
                            </a>
                            <a href="{{ route('admin.skpd.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.skpd.*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                                <span class="font-medium">SKPD</span>
                            </a>
                            <a href="{{ route('admin.user.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.user.*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="users" class="w-5 h-5"></i>
                                <span class="font-medium">User</span>
                            </a>
                            <a href="{{ route('admin.kategori.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.kategori.*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="tags" class="w-5 h-5"></i>
                                <span class="font-medium">Kategori</span>
                            </a>
                            
                            <div class="px-4 my-3">
                                <div class="border-t border-blue-700/50"></div>
                            </div>
                            <div class="px-4 mb-2">
                                <p class="text-xs text-blue-300 uppercase font-semibold tracking-wider">Laporan</p>
                            </div>
                            <a href="{{ route('reports.skpd-performance') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('reports.skpd-performance') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                                <span class="font-medium">Performa SKPD</span>
                            </a>
                            <a href="{{ route('reports.content-history') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('reports.content-history') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="history" class="w-5 h-5"></i>
                                <span class="font-medium">Riwayat Konten</span>
                            </a>
                        @endif

                        @if(auth()->user()->hasRole('Operator'))
                            <a href="{{ route('operator.dashboard') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('operator.dashboard') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="home" class="w-5 h-5"></i>
                                <span class="font-medium">Dashboard</span>
                            </a>
                            <a href="{{ route('operator.verification.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('operator.verification.*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                <span class="font-medium">Verifikasi</span>
                                @php
                                    $pendingCount = \App\Models\Content::where('status', 'Pending')->count();
                                @endphp
                                @if($pendingCount > 0)
                                <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('operator.monitoring.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('operator.monitoring.*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                                <span class="font-medium">Monitoring</span>
                            </a>
                            <a href="{{ route('operator.verification.history.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('operator.verification.history*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="history" class="w-5 h-5"></i>
                                <span class="font-medium">Riwayat</span>
                            </a>
                        @endif

                        @if(auth()->user()->hasRole('Publisher'))
                            <a href="{{ route('publisher.dashboard') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('publisher.dashboard') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="home" class="w-5 h-5"></i>
                                <span class="font-medium">Dashboard</span>
                            </a>
                            <a href="{{ route('publisher.content.index') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('publisher.content.*') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                                <span class="font-medium">Konten Saya</span>
                            </a>
                            <a href="{{ route('publisher.content.create') }}" 
                               class="w-full flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('publisher.content.create') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }} transition-all duration-200">
                                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                <span class="font-medium">Tambah Konten</span>
                            </a>
                        @endif
                    </div>
                @endauth
            </nav>
            
            <!-- User Info & Logout -->
            @auth
            <div class="p-4 border-t border-blue-700/50">
                <div class="flex items-center gap-3 mb-3 px-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold shadow-lg">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-300">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-200 transition-all duration-200">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Logout</span>
                    </button>
                </form>
            </div>
            @endauth
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden md:ml-0">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 py-3 md:px-6">
                    <div class="flex items-center gap-4">
                        <button onclick="toggleSidebar()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors md:hidden">
                            <i data-lucide="menu" class="w-6 h-6 text-gray-600"></i>
                        </button>
                        <div class="hidden md:block">
                            <h2 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                            <p class="text-sm text-gray-500">@yield('page-subtitle', 'Selamat datang di sistem manajemen konten')</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <!-- Search (Optional) -->
                        <div class="hidden lg:flex items-center">
                            <div class="relative">
                                <input type="text" placeholder="Cari..." class="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        @auth
                        @php
                            $unreadNotifications = auth()->user()->notifications()->where('is_read', false)->take(5)->get();
                            $unreadCount = auth()->user()->notifications()->where('is_read', false)->count();
                        @endphp
                        <div class="relative">
                            <button onclick="toggleNotifications()" class="p-2.5 hover:bg-gray-100 rounded-xl relative transition-colors">
                                <i data-lucide="bell" class="w-5 h-5 text-gray-600"></i>
                                @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-white"></span>
                                @endif
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                                <div class="p-4 border-b bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                                        @if($unreadCount > 0)
                                        <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full font-medium">{{ $unreadCount }} baru</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @forelse($unreadNotifications as $notification)
                                    <div class="p-4 border-b hover:bg-blue-50 transition-colors cursor-pointer">
                                        <div class="flex gap-3">
                                            <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-800">{{ $notification->title ?? 'Notifikasi' }}</p>
                                                <p class="text-sm text-gray-600 mt-0.5">{{ $notification->message }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="p-8 text-center">
                                        <i data-lucide="bell-off" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                        <p class="text-gray-500 text-sm">Tidak ada notifikasi baru</p>
                                    </div>
                                    @endforelse
                                </div>
                                @if($unreadCount > 0)
                                <div class="p-3 border-t bg-gray-50">
                                    <a href="#" class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium">Lihat semua notifikasi</a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endauth
                        
                        <!-- User Info -->
                        @auth
                        <div class="hidden sm:flex items-center gap-3 pl-3 border-l border-gray-200">
                            <div class="text-right">
                                <p class="font-medium text-sm text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->role }}</p>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-semibold shadow-lg shadow-blue-500/30">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 shadow-sm animate-fade-in">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">Berhasil!</p>
                        <p class="text-sm text-green-600">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 shadow-sm animate-fade-in">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">Error!</p>
                        <p class="text-sm text-red-600">{{ session('error') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <p class="font-medium">Terdapat kesalahan pada input:</p>
                    </div>
                    <ul class="list-disc list-inside ml-13 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="bg-white border-t px-6 py-3">
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} SKPD Content Management System</p>
                    <p>v1.0.0</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth < 768) {
                sidebar.classList.toggle('sidebar-collapsed');
                overlay.classList.toggle('hidden');
            } else {
                sidebar.classList.toggle('w-64');
                sidebar.classList.toggle('w-0');
                sidebar.classList.toggle('overflow-hidden');
            }
        }
        
        // Toggle notifications dropdown
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const bellButton = event.target.closest('button');
            if (!bellButton && dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Re-initialize icons after page load
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
