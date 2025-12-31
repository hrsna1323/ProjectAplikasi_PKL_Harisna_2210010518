@extends('layouts.app')

@section('title', 'Dashboard Operator')
@section('page-title', 'Dashboard Operator')
@section('page-subtitle', 'Verifikasi dan monitoring konten SKPD')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-6 text-white shadow-lg shadow-green-500/20">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-green-100 mt-1">Ada {{ $stats['pending_content'] ?? 0 }} konten menunggu verifikasi Anda</p>
            </div>
            <a href="{{ route('operator.monitoring.index') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur text-white px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all duration-200 font-medium">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                Lihat Monitoring
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pending -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['pending_content'] ?? 0 }}</p>
                    <p class="text-xs text-yellow-500 mt-1 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        Menunggu verifikasi
                    </p>
                </div>
                <div class="w-14 h-14 bg-yellow-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="clock" class="w-7 h-7 text-yellow-500"></i>
                </div>
            </div>
        </div>

        <!-- Approved Hari Ini -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Approved Hari Ini</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['approved_today'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 mt-1 flex items-center gap-1">
                        <i data-lucide="check" class="w-3 h-3"></i>
                        Disetujui
                    </p>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-7 h-7 text-green-500"></i>
                </div>
            </div>
        </div>

        <!-- Rejected Hari Ini -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Rejected Hari Ini</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['rejected_today'] ?? 0 }}</p>
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i data-lucide="x" class="w-3 h-3"></i>
                        Ditolak
                    </p>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-7 h-7 text-red-500"></i>
                </div>
            </div>
        </div>

        <!-- Total Verified -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Verified</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['total_verified'] ?? 0 }}</p>
                    <p class="text-xs text-blue-500 mt-1 flex items-center gap-1">
                        <i data-lucide="file-check" class="w-3 h-3"></i>
                        Semua waktu
                    </p>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="file-text" class="w-7 h-7 text-blue-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Pending Verifikasi -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Konten Pending Verifikasi</h2>
                <p class="text-sm text-gray-500 mt-0.5">Konten yang menunggu review Anda</p>
            </div>
            <a href="{{ route('operator.verification.index') }}" class="text-blue-600 text-sm hover:text-blue-700 font-medium flex items-center gap-1">
                Lihat Semua
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($pendingContents ?? [] as $content)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-semibold text-lg text-gray-800 truncate">{{ $content->judul }}</h3>
                            <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-lg flex-shrink-0">
                                Pending
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->skpd->nama_skpd ?? '-' }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->user->name ?? '-' }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="folder" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->kategori->nama_kategori ?? '-' }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->tanggal_publikasi?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                        @if($content->url_konten)
                        <a href="{{ $content->url_konten }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-blue-600 text-sm mt-3 hover:text-blue-700 hover:underline">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            {{ Str::limit($content->url_konten, 50) }}
                        </a>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('operator.verification.show', $content->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-colors font-medium text-sm">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Review
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-circle" class="w-8 h-8 text-green-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Semua Terverifikasi!</h3>
                <p class="text-gray-500">Tidak ada konten yang menunggu verifikasi</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- SKPD Belum Memenuhi Kuota -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">SKPD Belum Memenuhi Kuota</h2>
                        <p class="text-sm text-gray-500">Perlu perhatian khusus</p>
                    </div>
                </div>
                <a href="{{ route('operator.monitoring.index') }}" class="text-blue-600 text-sm hover:text-blue-700 font-medium">
                    Monitoring
                </a>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($nonCompliantSkpds ?? [] as $skpd)
                <div class="flex items-center justify-between p-4 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="building" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $skpd->nama_skpd }}</p>
                            <p class="text-sm text-gray-600">{{ $skpd->approved_count }}/{{ $skpd->kuota_bulanan }} konten</p>
                        </div>
                    </div>
                    @php
                        $statusClass = $skpd->compliance_percentage >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700';
                    @endphp
                    <span class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $statusClass }}">
                        {{ $skpd->compliance_percentage }}%
                    </span>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="check-circle" class="w-7 h-7 text-green-500"></i>
                    </div>
                    <p class="text-green-600 font-medium">Semua SKPD memenuhi kuota</p>
                    <p class="text-sm text-gray-500 mt-1">Kerja bagus!</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Notifikasi Terbaru -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="bell" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Notifikasi Terbaru</h2>
                        <p class="text-sm text-gray-500">Update terkini untuk Anda</p>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($notifications ?? [] as $notification)
                <div class="flex gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors {{ !$notification->is_read ? 'border-l-4 border-blue-500' : '' }}">
                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-800">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="bell-off" class="w-7 h-7 text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Tidak ada notifikasi terbaru</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
