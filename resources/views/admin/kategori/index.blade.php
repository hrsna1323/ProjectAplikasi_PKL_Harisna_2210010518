@extends('layouts.app')

@section('title', 'Daftar Kategori')
@section('page-title', 'Kelola Kategori')
@section('page-subtitle', 'Manajemen kategori konten publikasi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Kategori</h1>
            <p class="text-gray-500 mt-1">Total {{ count($kategoris ?? []) }} kategori terdaftar</p>
        </div>
        <a href="{{ route('admin.kategori.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Tambah Kategori
        </a>
    </div>

    <!-- Kategori Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="tags" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Kategori Konten</h2>
                    <p class="text-sm text-gray-500">Jenis-jenis konten yang dapat dipublikasikan</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Konten</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kategoris as $index => $kategori)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="tag" class="w-5 h-5 text-purple-600"></i>
                                </div>
                                <p class="font-medium text-gray-800">{{ $kategori->nama_kategori }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($kategori->deskripsi, 50) ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                {{ $kategori->contents_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($kategori->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.kategori.show', $kategori->id) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.kategori.edit', $kategori->id) }}" class="p-2 text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('admin.kategori.destroy', $kategori->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $kategori->is_active ? 'menonaktifkan' : 'mengaktifkan' }} kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-500 hover:text-{{ $kategori->is_active ? 'orange' : 'green' }}-600 hover:bg-{{ $kategori->is_active ? 'orange' : 'green' }}-50 rounded-lg transition-colors" title="{{ $kategori->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i data-lucide="{{ $kategori->is_active ? 'toggle-right' : 'toggle-left' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="tags" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada kategori terdaftar</p>
                                <p class="text-gray-400 text-sm mt-1">Tambahkan kategori baru untuk memulai</p>
                                <a href="{{ route('admin.kategori.create') }}" class="mt-4 inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Tambah Kategori
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
