@extends('layouts.app')

@section('title', 'Review Konten')
@section('page-title', 'Review Konten')
@section('page-subtitle', 'Verifikasi konten yang disubmit publisher')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('operator.verification.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Verifikasi
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
            <span class="text-gray-800 font-medium">Review Konten</span>
        </nav>
        <a href="{{ route('operator.verification.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 font-medium">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Content Detail -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Detail Konten</h2>
                            <p class="text-sm text-gray-500">Informasi lengkap konten</p>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-semibold flex items-center gap-1">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        {{ $content->status }}
                    </span>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $content->judul }}</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">SKPD</p>
                            <div class="flex items-center gap-2">
                                <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                                <p class="text-gray-800 font-medium">{{ $content->skpd->nama_skpd ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Kategori</p>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">
                                <i data-lucide="folder" class="w-3.5 h-3.5"></i>
                                {{ $content->kategori->nama_kategori ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Publisher</p>
                            <div class="flex items-center gap-2">
                                <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                <p class="text-gray-800 font-medium">{{ $content->publisher->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tanggal Publikasi</p>
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                <p class="text-gray-800 font-medium">{{ $content->tanggal_publikasi?->format('d F Y') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Deskripsi</p>
                        <p class="text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-xl">{{ $content->deskripsi }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">URL Publikasi</p>
                        <a href="{{ $content->url_publikasi }}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 bg-blue-50 px-4 py-3 rounded-xl font-medium">
                            <i data-lucide="external-link" class="w-5 h-5"></i>
                            {{ $content->url_publikasi }}
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tanggal Submit</p>
                            <p class="text-gray-600 text-sm">{{ $content->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Terakhir Update</p>
                            <p class="text-gray-600 text-sm">{{ $content->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification History -->
            @if($verificationHistory->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="history" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Riwayat Verifikasi</h2>
                            <p class="text-sm text-gray-500">Log aktivitas verifikasi</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($verificationHistory as $verification)
                    <div class="flex gap-4 p-4 bg-gray-50 rounded-xl">
                        <div class="flex-shrink-0">
                            @if($verification->isApproved())
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="check" class="w-5 h-5 text-green-600"></i>
                                </div>
                            @else
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="x" class="w-5 h-5 text-red-600"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-gray-800">{{ $verification->status }}</span>
                                <span class="text-xs text-gray-500">{{ $verification->verified_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-600">oleh {{ $verification->verifikator->name ?? '-' }}</p>
                            @if($verification->alasan)
                                <p class="mt-2 text-sm text-gray-700 italic bg-white p-3 rounded-lg border border-gray-200">"{{ $verification->alasan }}"</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Verification Actions -->
        <div class="space-y-6">
            @if($content->isPending())
            <!-- Approve Form -->
            <div class="bg-white rounded-2xl shadow-sm border-2 border-green-200 overflow-hidden">
                <div class="p-5 bg-gradient-to-r from-green-500 to-emerald-500 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-semibold text-lg">Setujui Konten</h3>
                    </div>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('operator.verification.approve', $content->id) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="alasan_approve" class="block text-sm font-semibold text-gray-700 mb-2">Alasan (Opsional)</label>
                            <textarea name="alasan" id="alasan_approve" rows="3"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50"
                                      placeholder="Masukkan alasan persetujuan..."></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-green-500/30">
                            <i data-lucide="check" class="w-5 h-5"></i>
                            Setujui Konten
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reject Form -->
            <div class="bg-white rounded-2xl shadow-sm border-2 border-red-200 overflow-hidden">
                <div class="p-5 bg-gradient-to-r from-red-500 to-rose-500 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="x-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-semibold text-lg">Tolak Konten</h3>
                    </div>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('operator.verification.reject', $content->id) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="alasan_reject" class="block text-sm font-semibold text-gray-700 mb-2">
                                Alasan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="alasan" id="alasan_reject" rows="3" required
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-gray-50 @error('alasan') border-red-500 @enderror"
                                      placeholder="Masukkan alasan penolakan...">{{ old('alasan') }}</textarea>
                            <p class="mt-1.5 text-xs text-gray-500">Minimal 10 karakter</p>
                            @error('alasan')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-red-500/30">
                            <i data-lucide="x" class="w-5 h-5"></i>
                            Tolak Konten
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="info" class="w-8 h-8 text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konten Sudah Diverifikasi</h3>
                <p class="text-gray-500">Status saat ini:</p>
                @php
                    $statusConfig = match($content->status) {
                        'Approved' => ['class' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
                        'Rejected' => ['class' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
                        default => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'file']
                    };
                @endphp
                <span class="inline-flex items-center gap-2 px-4 py-2 {{ $statusConfig['class'] }} rounded-xl font-semibold mt-2">
                    <i data-lucide="{{ $statusConfig['icon'] }}" class="w-5 h-5"></i>
                    {{ $content->status }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
