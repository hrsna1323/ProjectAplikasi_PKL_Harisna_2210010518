@extends('layouts.app')

@section('title', 'Riwayat Verifikasi')
@section('page-title', 'Riwayat Verifikasi')
@section('page-subtitle', 'Semua riwayat verifikasi konten')

@section('content')
<div class="space-y-6">
    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('operator.verification.history.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ $filters['start_date'] ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ $filters['end_date'] ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- SKPD Filter -->
                <div>
                    <label for="skpd_id" class="block text-sm font-medium text-gray-700 mb-2">SKPD</label>
                    <select id="skpd_id" 
                            name="skpd_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua SKPD</option>
                        @foreach($skpds as $skpd)
                        <option value="{{ $skpd->id }}" {{ ($filters['skpd_id'] ?? '') == $skpd->id ? 'selected' : '' }}>
                            {{ $skpd->nama_skpd }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" 
                            name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ ($filters['status'] ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Judul</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari judul konten..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl flex items-center gap-2 transition-colors font-medium">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
                <a href="{{ route('operator.verification.history.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl flex items-center gap-2 transition-colors font-medium">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Verification History List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Riwayat Verifikasi</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Total {{ $verifications->total() }} verifikasi</p>
                </div>
            </div>
        </div>

        @forelse($verifications as $verification)
        <div class="p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="font-semibold text-lg text-gray-800 truncate">
                            {{ $verification->content->judul ?? 'Konten Dihapus' }}
                        </h3>
                        @if($verification->isApproved())
                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-lg flex-shrink-0">
                            Approved
                        </span>
                        @else
                        <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-lg flex-shrink-0">
                            Rejected
                        </span>
                        @endif
                    </div>
                    
                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-500 mb-2">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                            {{ $verification->content->skpd->nama_skpd ?? '-' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                            Verifikator: {{ $verification->verifikator->name ?? '-' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="folder" class="w-4 h-4 text-gray-400"></i>
                            {{ $verification->content->kategori->nama_kategori ?? '-' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                            {{ $verification->verified_at->format('d M Y H:i') }}
                        </span>
                    </div>

                    @if($verification->alasan)
                    <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600"><span class="font-medium">Alasan:</span> {{ $verification->alasan }}</p>
                    </div>
                    @endif
                </div>

                <div class="flex gap-2 flex-shrink-0">
                    @if($verification->content)
                    <a href="{{ route('operator.verification.show', $verification->content->id) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 transition-colors font-medium text-sm">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        Lihat Detail
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Tidak Ada Riwayat</h3>
            <p class="text-gray-500">Belum ada riwayat verifikasi yang sesuai dengan filter</p>
        </div>
        @endforelse

        <!-- Pagination -->
        @if($verifications->hasPages())
        <div class="p-6 border-t border-gray-100">
            {{ $verifications->links() }}
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
