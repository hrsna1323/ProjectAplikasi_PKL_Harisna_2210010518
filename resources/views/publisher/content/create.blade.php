@extends('layouts.app')

@section('title', 'Tambah Konten')
@section('page-title', 'Tambah Konten Baru')
@section('page-subtitle', 'Laporkan konten yang telah dipublikasikan')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('publisher.content.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            Daftar Konten
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
        <span class="text-gray-800 font-medium">Tambah Konten</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="file-plus" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Form Konten Baru</h2>
                            <p class="text-sm text-gray-500">Isi detail konten yang akan disubmit</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('publisher.content.store') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="judul" class="block text-sm font-semibold text-gray-700 mb-2">
                                Judul Konten <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('judul') border-red-500 @enderror"
                                   placeholder="Masukkan judul konten">
                            @error('judul')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                                Deskripsi <span class="text-red-500">*</span>
                            </label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" required
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('deskripsi') border-red-500 @enderror"
                                      placeholder="Jelaskan isi konten secara singkat">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="kategori_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <select id="kategori_id" name="kategori_id" required
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('kategori_id') border-red-500 @enderror">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('kategori_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="tanggal_publikasi" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Publikasi <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="tanggal_publikasi" name="tanggal_publikasi" 
                                       value="{{ old('tanggal_publikasi', date('Y-m-d')) }}" required
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('tanggal_publikasi') border-red-500 @enderror">
                                @error('tanggal_publikasi')
                                    <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="url_publikasi" class="block text-sm font-semibold text-gray-700 mb-2">
                                URL Publikasi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i data-lucide="link" class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                                <input type="url" id="url_publikasi" name="url_publikasi" value="{{ old('url_publikasi') }}" 
                                       placeholder="https://example.com/artikel" required
                                       class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('url_publikasi') border-red-500 @enderror">
                            </div>
                            <p class="mt-1.5 text-sm text-gray-500 flex items-center gap-1">
                                <i data-lucide="info" class="w-4 h-4"></i>
                                Masukkan URL lengkap konten yang sudah dipublikasikan
                            </p>
                            @error('url_publikasi')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
                                <i data-lucide="send" class="w-5 h-5"></i>
                                Simpan & Kirim untuk Verifikasi
                            </button>
                            <a href="{{ route('publisher.content.index') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Panduan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="lightbulb" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Panduan</h3>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">Pastikan konten sudah dipublikasikan di website SKPD</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">URL harus dapat diakses oleh publik</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">Pilih kategori yang sesuai dengan jenis konten</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-blue-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">Konten akan diverifikasi oleh Operator sebelum dihitung dalam kuota</p>
                    </div>
                </div>
            </div>

            <!-- Status Kuota -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="target" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-semibold">Status Kuota</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-indigo-100">Kuota Bulanan</span>
                        <span class="font-semibold">{{ auth()->user()->skpd->kuota_bulanan ?? 3 }} konten</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-indigo-100">Sudah Disetujui</span>
                        <span class="font-semibold">{{ $approvedCount ?? 0 }} konten</span>
                    </div>
                    <div class="w-full bg-white/30 rounded-full h-2 mt-2">
                        @php
                            $quota = auth()->user()->skpd->kuota_bulanan ?? 3;
                            $approved = $approvedCount ?? 0;
                            $percentage = $quota > 0 ? min(($approved / $quota) * 100, 100) : 0;
                        @endphp
                        <div class="bg-white h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
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
