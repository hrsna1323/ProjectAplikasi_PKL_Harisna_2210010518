@extends('layouts.app')

@section('title', 'Riwayat Konten')
@section('page-title', 'Riwayat Konten')
@section('page-subtitle', 'Lihat semua konten dengan filter')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Riwayat Konten</h1>
            <p class="text-gray-500 mt-1">Lihat semua konten dengan filter</p>
        </div>
        <div class="relative">
            <button onclick="toggleExportDropdown('contentExport')" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-green-500/30">
                <i data-lucide="download" class="w-5 h-5"></i>
                Export
                <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </button>
            <div id="contentExport" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                <a href="{{ route('reports.export.content', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
                    <span class="font-medium">Export PDF</span>
                </a>
                <a href="{{ route('reports.export.content', array_merge(request()->query(), ['format' => 'csv'])) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                    <i data-lucide="table" class="w-4 h-4 text-green-500"></i>
                    <span class="font-medium">Export CSV</span>
                </a>
                <a href="{{ route('reports.export.content', array_merge(request()->query(), ['format' => 'word'])) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                    <i data-lucide="file-type" class="w-4 h-4 text-blue-500"></i>
                    <span class="font-medium">Export Word</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.content-history') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SKPD</label>
                <select name="skpd_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    <option value="">Semua SKPD</option>
                    @foreach($skpds as $skpd)
                        <option value="{{ $skpd->id }}" {{ ($filters['skpd_id'] ?? '') == $skpd->id ? 'selected' : '' }}>
                            {{ $skpd->nama_skpd }}
                        </option>
                    @endforeach
                </select>
            </div>
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
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-medium transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
                <a href="{{ route('reports.content-history') }}" class="inline-flex items-center justify-center p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors" title="Reset">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Results Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Hasil Pencarian</h2>
                    <p class="text-sm text-gray-500">{{ $contents->count() }} konten ditemukan</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            @if($contents->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKPD</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Publisher</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($contents as $index => $content)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="max-w-xs">
                                <p class="font-medium text-gray-800 truncate">{{ $content->judul }}</p>
                                @if($content->url_publikasi)
                                    <a href="{{ $content->url_publikasi }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 text-xs mt-0.5">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        <span class="truncate max-w-[200px]">{{ $content->url_publikasi }}</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <i data-lucide="building" class="w-4 h-4 text-gray-400"></i>
                                <span class="text-sm text-gray-600 truncate max-w-[150px]">{{ $content->skpd->nama_skpd ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-medium">
                                <i data-lucide="tag" class="w-3 h-3"></i>
                                {{ $content->kategori->nama_kategori ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center text-white text-xs font-semibold">
                                    {{ $content->publisher ? strtoupper(substr($content->publisher->name, 0, 1)) : '-' }}
                                </div>
                                <span class="text-sm text-gray-600 truncate max-w-[120px]">{{ $content->publisher->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-sm text-gray-600">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                                {{ $content->tanggal_publikasi?->format('d/m/Y') ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusConfig = match($content->status) {
                                    'Draft' => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'file'],
                                    'Pending' => ['class' => 'bg-yellow-100 text-yellow-700', 'icon' => 'clock'],
                                    'Approved' => ['class' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
                                    'Rejected' => ['class' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
                                    'Published' => ['class' => 'bg-blue-100 text-blue-700', 'icon' => 'globe'],
                                    default => ['class' => 'bg-gray-100 text-gray-700', 'icon' => 'minus-circle']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $statusConfig['class'] }}">
                                <i data-lucide="{{ $statusConfig['icon'] }}" class="w-3.5 h-3.5"></i>
                                {{ $content->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                @if(auth()->user()->role === 'Operator')
                                    <a href="{{ route('operator.verification.show', $content->id) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="px-6 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="search-x" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 font-medium text-lg">Tidak ada konten ditemukan</p>
                    <p class="text-gray-400 text-sm mt-1">Coba ubah filter pencarian Anda</p>
                </div>
            </div>
            @endif
        </div>
        
        @if($contents->count() > 0)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Menampilkan {{ $contents->count() }} konten</span>
                </div>
            </div>
        </div>
        @endif
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
