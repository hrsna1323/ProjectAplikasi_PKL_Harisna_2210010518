@extends('layouts.app')

@section('title', 'Dashboard Publisher')
@section('page-title', 'Dashboard Publisher')
@section('page-subtitle', auth()->user()->skpd->nama_skpd ?? 'Kelola konten publikasi Anda')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner with Quota Progress -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-indigo-100 mt-1">{{ auth()->user()->skpd->nama_skpd ?? 'SKPD' }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="bg-white/20 backdrop-blur rounded-xl p-4 min-w-[200px]">
                    <p class="text-sm text-indigo-100 mb-2">Progress Kuota Bulan Ini</p>
                    <div class="flex items-end gap-2 mb-2">
                        <span class="text-3xl font-bold">{{ $quotaProgress['approved'] ?? 0 }}</span>
                        <span class="text-xl text-indigo-200">/ {{ $quotaProgress['quota'] ?? 3 }}</span>
                    </div>
                    <div class="w-full bg-white/30 rounded-full h-2">
                        @php
                            $percentage = min(($quotaProgress['percentage'] ?? 0), 100);
                        @endphp
                        <div class="bg-white h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                    </div>
                    <p class="text-xs text-indigo-100 mt-2">
                        @if(($quotaProgress['is_fulfilled'] ?? false))
                            ✓ Kuota terpenuhi!
                        @else
                            {{ $quotaProgress['remaining'] ?? 0 }} konten lagi untuk memenuhi kuota
                        @endif
                    </p>
                </div>
                <a href="{{ route('publisher.content.create') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 px-5 py-3 rounded-xl flex items-center gap-2 transition-all duration-200 font-semibold shadow-lg">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Input Konten Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Approved -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Approved</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $contentStats['approved'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 mt-1 flex items-center gap-1">
                        <i data-lucide="check" class="w-3 h-3"></i>
                        Konten disetujui
                    </p>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-7 h-7 text-green-500"></i>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pending</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $contentStats['pending'] ?? 0 }}</p>
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

        <!-- Rejected -->
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Rejected</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $contentStats['rejected'] ?? 0 }}</p>
                    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                        <i data-lucide="x" class="w-3 h-3"></i>
                        Perlu revisi
                    </p>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-7 h-7 text-red-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Konten Saya -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Konten Saya</h2>
                <p class="text-sm text-gray-500 mt-0.5">Daftar konten yang telah Anda submit</p>
            </div>
            <a href="{{ route('publisher.content.index') }}" class="text-blue-600 text-sm hover:text-blue-700 font-medium flex items-center gap-1">
                Lihat Semua
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentContents ?? [] as $content)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-semibold text-lg text-gray-800 truncate">{{ $content->judul }}</h3>
                            @php
                                $statusConfig = match($content->status) {
                                    'Approved' => ['class' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
                                    'Pending' => ['class' => 'bg-yellow-100 text-yellow-700', 'icon' => 'clock'],
                                    'Rejected' => ['class' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
                                    'Draft' => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'file'],
                                    default => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'file']
                                };
                            @endphp
                            <span class="px-2.5 py-1 {{ $statusConfig['class'] }} text-xs font-semibold rounded-lg flex items-center gap-1 flex-shrink-0">
                                <i data-lucide="{{ $statusConfig['icon'] }}" class="w-3.5 h-3.5"></i>
                                {{ $content->status }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="folder" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->kategori->nama_kategori ?? '-' }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->tanggal_publikasi?->format('d M Y') ?? '-' }}
                            </span>
                            @if($content->latestVerification)
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="user-check" class="w-4 h-4 text-gray-400"></i>
                                {{ $content->latestVerification->verifier->name ?? '-' }}
                            </span>
                            @endif
                        </div>
                        @if($content->latestVerification && $content->latestVerification->catatan)
                        @php
                            $noteClass = match($content->status) {
                                'Approved' => 'bg-green-50 border-green-200 text-green-700',
                                'Rejected' => 'bg-red-50 border-red-200 text-red-700',
                                default => 'bg-gray-50 border-gray-200 text-gray-700'
                            };
                        @endphp
                        <div class="mt-3 p-3 rounded-xl text-sm border {{ $noteClass }}">
                            <span class="font-medium">Catatan:</span> {{ $content->latestVerification->catatan }}
                        </div>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('publisher.content.show', $content->id) }}" class="p-2.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" title="Lihat Detail">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </a>
                        @if($content->status === 'Draft' || $content->status === 'Rejected')
                        <a href="{{ route('publisher.content.edit', $content->id) }}" class="p-2.5 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors" title="Edit">
                            <i data-lucide="edit" class="w-5 h-5"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="file-plus" class="w-8 h-8 text-indigo-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Belum Ada Konten</h3>
                <p class="text-gray-500 mb-4">Mulai buat konten pertama Anda</p>
                <a href="{{ route('publisher.content.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Konten Baru
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Notifikasi -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="bell" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Notifikasi Terbaru</h2>
                    <p class="text-sm text-gray-500">Update status konten Anda</p>
                </div>
            </div>
        </div>
        <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
            @forelse($notifications ?? [] as $notification)
            <div class="flex gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors {{ !$notification->is_read ? 'border-l-4 border-blue-500' : '' }}">
                <div class="flex-shrink-0">
                    @if(!$notification->is_read)
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    @else
                    <div class="w-2 h-2 bg-gray-300 rounded-full mt-2"></div>
                    @endif
                </div>
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
                <p class="text-gray-500">Tidak ada notifikasi baru</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Tips -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="lightbulb" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Tips Publikasi Konten</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                        Pastikan URL konten dapat diakses publik
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                        Pilih kategori yang sesuai dengan jenis konten
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                        Isi deskripsi dengan jelas dan informatif
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                        Submit konten sebelum akhir bulan untuk memenuhi kuota
                    </li>
                </ul>
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
