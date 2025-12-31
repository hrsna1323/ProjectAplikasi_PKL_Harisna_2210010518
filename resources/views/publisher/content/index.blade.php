@extends('layouts.app')

@section('title', 'Daftar Konten')
@section('page-title', 'Konten Saya')
@section('page-subtitle', 'Kelola konten publikasi SKPD Anda')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Konten</h1>
            <p class="text-gray-500 mt-1">Kelola konten publikasi SKPD Anda</p>
        </div>
        <a href="{{ route('publisher.content.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Tambah Konten
        </a>
    </div>

    <!-- Quota Progress Card -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h3 class="font-semibold text-lg mb-2">Progress Kuota Bulan Ini</h3>
                <div class="flex items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <div class="w-full bg-white/30 rounded-full h-3">
                            @php
                                $percentage = min(($quotaProgress['percentage'] ?? 0), 100);
                            @endphp
                            <div class="bg-white h-3 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="text-sm text-indigo-100 mt-2">
                            @if(($quotaProgress['is_fulfilled'] ?? false))
                                ✓ Kuota terpenuhi!
                            @else
                                {{ $quotaProgress['remaining'] ?? 0 }} konten lagi untuk memenuhi kuota
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex gap-6">
                <div class="text-center bg-white/10 backdrop-blur rounded-xl px-6 py-3">
                    <p class="text-3xl font-bold">{{ $quotaProgress['approved'] ?? 0 }}</p>
                    <p class="text-sm text-indigo-100">Disetujui</p>
                </div>
                <div class="text-center bg-white/10 backdrop-blur rounded-xl px-6 py-3">
                    <p class="text-3xl font-bold">{{ $quotaProgress['pending'] ?? 0 }}</p>
                    <p class="text-sm text-indigo-100">Pending</p>
                </div>
                <div class="text-center bg-white/10 backdrop-blur rounded-xl px-6 py-3">
                    <p class="text-3xl font-bold">{{ $quotaProgress['quota'] ?? 3 }}</p>
                    <p class="text-sm text-indigo-100">Kuota</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('publisher.content.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select name="kategori_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ ($filters['kategori_id'] ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="Judul..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
                <a href="{{ route('publisher.content.index') }}" class="inline-flex items-center justify-center p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors" title="Reset">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Content List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse($contents as $content)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <a href="{{ route('publisher.content.show', $content->id) }}" class="font-semibold text-lg text-gray-800 hover:text-blue-600 truncate">
                                {{ $content->judul }}
                            </a>
                            @php
                                $statusConfig = match($content->status) {
                                    'Approved' => ['class' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
                                    'Pending' => ['class' => 'bg-yellow-100 text-yellow-700', 'icon' => 'clock'],
                                    'Rejected' => ['class' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
                                    'Draft' => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'file'],
                                    'Published' => ['class' => 'bg-blue-100 text-blue-700', 'icon' => 'globe'],
                                    default => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'file']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusConfig['class'] }} flex-shrink-0">
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
                    <div class="flex gap-1 flex-shrink-0">
                        <a href="{{ route('publisher.content.show', $content->id) }}" class="p-2.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" title="Lihat">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </a>
                        @if($content->canBeEdited())
                        <a href="{{ route('publisher.content.edit', $content->id) }}" class="p-2.5 text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 rounded-xl transition-colors" title="Edit">
                            <i data-lucide="edit" class="w-5 h-5"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-16 text-center">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="file-plus" class="w-10 h-10 text-indigo-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Konten</h3>
                <p class="text-gray-500 mb-6">Mulai dengan membuat konten pertama Anda</p>
                <a href="{{ route('publisher.content.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Tambah Konten Baru
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
