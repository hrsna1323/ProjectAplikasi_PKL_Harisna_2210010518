@extends('layouts.app')

@section('title', 'Verifikasi Konten')
@section('page-title', 'Verifikasi Konten')
@section('page-subtitle', 'Review dan verifikasi konten yang disubmit publisher')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Verifikasi Konten</h1>
            <p class="text-gray-500 mt-1">Daftar konten yang menunggu verifikasi</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl font-semibold">
                <i data-lucide="clock" class="w-5 h-5"></i>
                {{ $contents->count() }} Pending
            </span>
            <a href="{{ route('operator.verification.history.index') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl font-medium transition-colors">
                <i data-lucide="history" class="w-4 h-4"></i>
                Riwayat
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('operator.verification.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="skpd_id" class="block text-sm font-medium text-gray-700 mb-2">SKPD</label>
                <select name="skpd_id" id="skpd_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    <option value="">Semua SKPD</option>
                    @foreach($skpds as $skpd)
                        <option value="{{ $skpd->id }}" {{ ($filters['skpd_id'] ?? '') == $skpd->id ? 'selected' : '' }}>
                            {{ $skpd->nama_skpd }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select name="kategori_id" id="kategori_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ ($filters['kategori_id'] ?? '') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" id="search" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="Cari judul atau deskripsi..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
                <a href="{{ route('operator.verification.index') }}" class="inline-flex items-center justify-center p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors" title="Reset">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Content List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($contents->isEmpty())
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-circle" class="w-10 h-10 text-green-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Semua Terverifikasi!</h3>
                <p class="text-gray-500">Tidak ada konten yang menunggu verifikasi</p>
                <a href="{{ route('operator.verification.history.index') }}" class="mt-4 inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    Lihat Riwayat Verifikasi
                </a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($contents as $content)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-semibold text-lg text-gray-800 truncate">{{ $content->judul }}</h3>
                                <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-lg flex-shrink-0">
                                    <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                                    Pending
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-3">{{ Str::limit($content->deskripsi, 120) }}</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                                    {{ $content->skpd->nama_skpd ?? '-' }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    {{ $content->publisher->name ?? '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-medium">
                                    <i data-lucide="folder" class="w-3 h-3"></i>
                                    {{ $content->kategori->nama_kategori ?? '-' }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                    {{ $content->created_at->format('d M Y H:i') }}
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
                            <a href="{{ route('operator.verification.show', $content->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 transition-colors font-medium shadow-lg shadow-blue-500/30">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Review
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
