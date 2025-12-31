@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Ringkasan statistik dan aktivitas sistem')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg shadow-blue-500/20">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-blue-100 mt-1">Berikut ringkasan aktivitas sistem hari ini</p>
            </div>
            <div class="relative">
                <button onclick="toggleExportDropdown('dashboardExport')" class="bg-white/20 hover:bg-white/30 backdrop-blur text-white px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all duration-200 font-medium">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Export Laporan
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                <div id="dashboardExport" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                    <a href="{{ route('reports.export.dashboard', ['format' => 'pdf']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                        <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
                        <span class="font-medium">Export PDF</span>
                    </a>
                    <a href="{{ route('reports.export.dashboard', ['format' => 'csv']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                        <i data-lucide="table" class="w-4 h-4 text-green-500"></i>
                        <span class="font-medium">Export CSV</span>
                    </a>
                    <a href="{{ route('reports.export.dashboard', ['format' => 'word']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                        <i data-lucide="file-type" class="w-4 h-4 text-blue-500"></i>
                        <span class="font-medium">Export Word</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total SKPD -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total SKPD</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['total_skpd'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Unit terdaftar</p>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="building-2" class="w-7 h-7 text-blue-500"></i>
                </div>
            </div>
        </div>

        <!-- Konten Bulan Ini -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Konten Bulan Ini</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['total_content_this_month'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3 h-3"></i>
                        Publikasi aktif
                    </p>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="file-text" class="w-7 h-7 text-green-500"></i>
                </div>
            </div>
        </div>

        <!-- Pending Verifikasi -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending Verifikasi</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['pending_content'] ?? 0 }}</p>
                    <p class="text-xs text-yellow-500 mt-1 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        Menunggu review
                    </p>
                </div>
                <div class="w-14 h-14 bg-yellow-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="clock" class="w-7 h-7 text-yellow-500"></i>
                </div>
            </div>
        </div>

        <!-- SKPD Warning -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">SKPD Warning</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['non_compliant_skpd'] ?? 0 }}</p>
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                        Belum memenuhi kuota
                    </p>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-7 h-7 text-red-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Approved Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['approved_content_this_month'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Rejected Bulan Ini</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['rejected_content_this_month'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="database" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Konten (Semua)</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_content_all_time'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Kepatuhan SKPD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Status Kepatuhan SKPD</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Monitoring kuota publikasi bulanan</p>
                </div>
                <a href="{{ route('reports.skpd-performance') }}" class="text-blue-600 text-sm hover:text-blue-700 font-medium flex items-center gap-1">
                    Lihat Semua
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
                @forelse($skpdPerformance->take(5) ?? [] as $skpd)
                <div class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="building" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $skpd->nama_skpd }}</p>
                            <p class="text-sm text-gray-500">{{ $skpd->approved_count }}/{{ $skpd->kuota_bulanan }} konten</p>
                        </div>
                    </div>
                    @php
                        $statusClass = $skpd->is_compliant ? 'bg-green-100 text-green-700' : ($skpd->compliance_percentage >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                        $statusText = $skpd->is_compliant ? 'Compliant' : ($skpd->compliance_percentage >= 50 ? 'Warning' : 'Critical');
                        $statusIcon = $skpd->is_compliant ? 'check-circle' : ($skpd->compliance_percentage >= 50 ? 'alert-triangle' : 'x-circle');
                    @endphp
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $statusClass }} flex items-center gap-1">
                        <i data-lucide="{{ $statusIcon }}" class="w-3.5 h-3.5"></i>
                        {{ $statusText }}
                    </span>
                </div>
                @empty
                <div class="text-center py-8">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500">Tidak ada data SKPD</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Aktivitas Terkini -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Aktivitas Terkini</h2>
                <p class="text-sm text-gray-500 mt-0.5">Log aktivitas sistem terbaru</p>
            </div>
            <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
                @forelse($recentActivities ?? [] as $activity)
                <div class="flex gap-4 p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i data-lucide="activity" class="w-5 h-5 text-blue-600"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 text-sm">{{ $activity->user->name ?? 'System' }}</p>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $activity->action }} - {{ $activity->description }}</p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i data-lucide="activity" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500">Tidak ada aktivitas terbaru</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Menu Cepat</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.skpd.index') }}" class="group flex flex-col items-center p-5 bg-blue-50 hover:bg-blue-100 rounded-2xl transition-all duration-200">
                <div class="w-14 h-14 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <i data-lucide="building-2" class="w-7 h-7 text-blue-600"></i>
                </div>
                <span class="text-sm font-semibold text-blue-800">Kelola SKPD</span>
                <span class="text-xs text-blue-600 mt-1">{{ $stats['total_skpd'] ?? 0 }} unit</span>
            </a>
            <a href="{{ route('admin.user.index') }}" class="group flex flex-col items-center p-5 bg-green-50 hover:bg-green-100 rounded-2xl transition-all duration-200">
                <div class="w-14 h-14 bg-green-100 group-hover:bg-green-200 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <i data-lucide="users" class="w-7 h-7 text-green-600"></i>
                </div>
                <span class="text-sm font-semibold text-green-800">Kelola User</span>
                <span class="text-xs text-green-600 mt-1">Manajemen akun</span>
            </a>
            <a href="{{ route('admin.kategori.index') }}" class="group flex flex-col items-center p-5 bg-purple-50 hover:bg-purple-100 rounded-2xl transition-all duration-200">
                <div class="w-14 h-14 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <i data-lucide="tags" class="w-7 h-7 text-purple-600"></i>
                </div>
                <span class="text-sm font-semibold text-purple-800">Kelola Kategori</span>
                <span class="text-xs text-purple-600 mt-1">Jenis konten</span>
            </a>
            <a href="{{ route('reports.content-history') }}" class="group flex flex-col items-center p-5 bg-orange-50 hover:bg-orange-100 rounded-2xl transition-all duration-200">
                <div class="w-14 h-14 bg-orange-100 group-hover:bg-orange-200 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <i data-lucide="history" class="w-7 h-7 text-orange-600"></i>
                </div>
                <span class="text-sm font-semibold text-orange-800">Riwayat Konten</span>
                <span class="text-xs text-orange-600 mt-1">Lihat semua</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    function toggleExportDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.classList.toggle('hidden');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('button[onclick*="toggleExportDropdown"]')) {
            document.querySelectorAll('[id$="Export"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });
</script>
@endpush
