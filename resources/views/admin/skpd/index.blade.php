@extends('layouts.app')

@section('title', 'Daftar SKPD')
@section('page-title', 'Kelola SKPD')
@section('page-subtitle', 'Manajemen data Satuan Kerja Perangkat Daerah')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar SKPD</h1>
            <p class="text-gray-500 mt-1">Total {{ count($skpds ?? []) }} SKPD terdaftar</p>
        </div>
        <a href="{{ route('admin.skpd.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-blue-500/30">
            <i data-lucide="building-2" class="w-5 h-5"></i>
            Tambah SKPD
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('admin.skpd.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="Nama SKPD..." value="{{ request('search') }}">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Kepatuhan</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    <option value="memenuhi" {{ request('status') == 'memenuhi' ? 'selected' : '' }}>Memenuhi</option>
                    <option value="sebagian" {{ request('status') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                    <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Memenuhi</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-medium transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
                <a href="{{ route('admin.skpd.index') }}" class="inline-flex items-center justify-center p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors" title="Reset">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $totalSkpd = count($skpds ?? []);
            $memenuhi = collect($skpds)->where('compliance_status', 'Memenuhi')->count();
            $sebagian = collect($skpds)->where('compliance_status', 'Sebagian')->count();
            $belum = collect($skpds)->where('compliance_status', 'Belum Memenuhi')->count();
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="building-2" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalSkpd }}</p>
                    <p class="text-sm text-gray-500">Total SKPD</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ $memenuhi }}</p>
                    <p class="text-sm text-gray-500">Memenuhi Kuota</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-yellow-600">{{ $sebagian }}</p>
                    <p class="text-sm text-gray-500">Sebagian</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600">{{ $belum }}</p>
                    <p class="text-sm text-gray-500">Belum Memenuhi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SKPD Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Status Kepatuhan Kuota</h2>
                        <p class="text-sm text-gray-500">Periode {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Kuota konten per bulan</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKPD</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Kuota</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($skpds as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ strtoupper(substr($item['skpd']->nama_skpd, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 truncate max-w-[200px]">{{ $item['skpd']->nama_skpd }}</p>
                                    @if($item['skpd']->website_url)
                                        <a href="{{ $item['skpd']->website_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 text-xs mt-0.5">
                                            <i data-lucide="external-link" class="w-3 h-3"></i>
                                            Website
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @if($item['skpd']->email)
                                    <div class="flex items-center gap-1.5 text-sm text-gray-600">
                                        <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i>
                                        <span class="truncate max-w-[150px]">{{ $item['skpd']->email }}</span>
                                    </div>
                                @endif
                                @if($item['skpd']->telepon)
                                    <div class="flex items-center gap-1.5 text-sm text-gray-600">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
                                        <span>{{ $item['skpd']->telepon }}</span>
                                    </div>
                                @endif
                                @if(!$item['skpd']->email && !$item['skpd']->telepon)
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 rounded-xl text-sm font-bold text-gray-700">
                                {{ $item['quota'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-xl text-sm font-bold text-blue-700">
                                {{ $item['approved_count'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4" style="min-width: 180px;">
                            @php
                                $percentage = min($item['compliance_percentage'], 100);
                                $bgClass = $percentage >= 100 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">{{ $item['approved_count'] }}/{{ $item['quota'] }} konten</span>
                                    <span class="font-semibold {{ $percentage >= 100 ? 'text-green-600' : ($percentage >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ number_format($item['compliance_percentage'], 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $bgClass }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusConfig = match($item['compliance_status']) {
                                    'Memenuhi' => ['class' => 'bg-green-100 text-green-700 border-green-200', 'icon' => 'check-circle'],
                                    'Sebagian' => ['class' => 'bg-yellow-100 text-yellow-700 border-yellow-200', 'icon' => 'alert-triangle'],
                                    'Belum Memenuhi' => ['class' => 'bg-red-100 text-red-700 border-red-200', 'icon' => 'x-circle'],
                                    default => ['class' => 'bg-gray-100 text-gray-700 border-gray-200', 'icon' => 'minus-circle']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $statusConfig['class'] }}">
                                <i data-lucide="{{ $statusConfig['icon'] }}" class="w-3.5 h-3.5"></i>
                                {{ $item['compliance_status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.skpd.show', $item['skpd']->id) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.skpd.edit', $item['skpd']->id) }}" class="p-2 text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('admin.skpd.destroy', $item['skpd']->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SKPD ini? Semua data terkait akan ikut terhapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="building-2" class="w-10 h-10 text-gray-400"></i>
                                </div>
                                <p class="text-gray-600 font-medium text-lg">Belum ada data SKPD</p>
                                <p class="text-gray-400 text-sm mt-1 max-w-sm">Tambahkan SKPD baru untuk mulai mengelola kuota konten publikasi</p>
                                <a href="{{ route('admin.skpd.create') }}" class="mt-6 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Tambah SKPD Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(count($skpds) > 0)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Menampilkan {{ count($skpds) }} SKPD</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                        <span>≥100%</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 bg-yellow-500 rounded-full"></span>
                        <span>50-99%</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                        <span>&lt;50%</span>
                    </div>
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
</script>
@endpush
