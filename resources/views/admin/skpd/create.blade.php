@extends('layouts.app')

@section('title', 'Tambah SKPD')
@section('page-title', 'Tambah SKPD')
@section('page-subtitle', 'Daftarkan SKPD baru ke sistem')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.skpd.index') }}" class="text-gray-500 hover:text-blue-600 flex items-center gap-1">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                Daftar SKPD
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
            <span class="text-gray-800 font-medium">Tambah SKPD</span>
        </nav>
        <a href="{{ route('admin.skpd.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 font-medium">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Form Tambah SKPD</h2>
                            <p class="text-sm text-gray-500">Isi data SKPD yang akan didaftarkan</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.skpd.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="nama_skpd" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama SKPD <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_skpd" name="nama_skpd" value="{{ old('nama_skpd') }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('nama_skpd') border-red-500 @enderror"
                                   placeholder="Contoh: Dinas Pendidikan">
                            @error('nama_skpd')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="website_url" class="block text-sm font-semibold text-gray-700 mb-2">Website URL</label>
                            <div class="relative">
                                <i data-lucide="globe" class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                                <input type="url" id="website_url" name="website_url" value="{{ old('website_url') }}"
                                       class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('website_url') border-red-500 @enderror"
                                       placeholder="https://example.go.id">
                            </div>
                            @error('website_url')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Kontak</label>
                            <div class="relative">
                                <i data-lucide="mail" class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('email') border-red-500 @enderror"
                                       placeholder="email@example.go.id">
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="kuota_bulanan" class="block text-sm font-semibold text-gray-700 mb-2">Kuota Bulanan</label>
                                <input type="number" id="kuota_bulanan" name="kuota_bulanan" value="{{ old('kuota_bulanan', 3) }}" min="1" max="100"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('kuota_bulanan') border-red-500 @enderror">
                                <p class="mt-1.5 text-sm text-gray-500 flex items-center gap-1">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                    Minimum konten per bulan (default: 3)
                                </p>
                                @error('kuota_bulanan')
                                    <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                <select id="status" name="status"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors @error('status') border-red-500 @enderror">
                                    <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <p class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                Simpan SKPD
                            </button>
                            <a href="{{ route('admin.skpd.index') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Informasi</h3>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-blue-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">SKPD yang didaftarkan akan dapat memiliki Publisher untuk mengelola konten</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-blue-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">Kuota bulanan menentukan target minimum publikasi konten</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-blue-600"></i>
                        </div>
                        <p class="text-sm text-gray-600">Status Inactive akan menonaktifkan SKPD dari sistem</p>
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
