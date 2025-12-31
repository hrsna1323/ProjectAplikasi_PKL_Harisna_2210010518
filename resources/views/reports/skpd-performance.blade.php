@extends('layouts.app')

@section('title', 'Performa SKPD')
@section('page-title', 'Laporan Performa SKPD')
@section('page-subtitle', 'Analisis kepatuhan kuota konten per SKPD')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Performa SKPD</h1>
            <p class="text-gray-500 mt-1">Periode {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
        </div>
        <div class="relative">
            <button onclick="toggleExportDropdown('skpdExport')" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-lg shadow-green-500/30">
                <i data-lucide="download" class="w-5 h-5"></i>
                Export
                <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </button>
            <div id="skpdExport" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                <a href="{{ route('reports.export.skpd', ['month' => $month, 'year' => $year, 'format' => 'pdf']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
                    <span class="font-medium">Export PDF</span>
                </a>
                <a href="{{ route('reports.export.skpd', ['month' => $month, 'year' => $year, 'format' => 'csv']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                    <i data-lucide="table" class="w-4 h-4 text-green-500"></i>
                    <span class="font-medium">Export CSV</span>
                </a>
                <a href="{{ route('reports.export.skpd', ['month' => $month, 'year' => $year, 'format' => 'word']) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-gray-700">
                    <i data-lucide="file-type" class="w-4 h-4 text-blue-500"></i>
                    <span class="font-medium">Export Word</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.skpd-performance') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl font-medium transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="building-2" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total_skpd'] }}</p>
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
                    <p class="text-2xl font-bold text-green-600">{{ $summary['compliant_skpd'] }}</p>
                    <p class="text-sm text-gray-500">Memenuhi Kuota</p>
                    <p class="text-xs text-green-600 font-medium">{{ $summary['compliance_rate'] }}% dari total</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['non_compliant_skpd'] }}</p>
                    <p class="text-sm text-gray-500">Belum Memenuhi</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="percent" class="w-6 h-6 text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-indigo-600">{{ $summary['average_compliance'] }}%</p>
                    <p class="text-sm text-gray-500">Rata-rata Kepatuhan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Approved</p>
                    <p class="text-3xl font-bold text-green-600">{{ $summary['total_approved'] }}</p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
                    <i data-lucide="check" class="w-7 h-7 text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Pending</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $summary['total_pending'] }}</p>
                </div>
                <div class="w-14 h-14 bg-yellow-100 rounded-2xl flex items-center justify-center">
                    <i data-lucide="clock" class="w-7 h-7 text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Rejected</p>
                    <p class="text-3xl font-bold text-red-600">{{ $summary['total_rejected'] }}</p>
                </div>
                <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center">
                    <i data-lucide="x" class="w-7 h-7 text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Detail Performa per SKPD</h2>
                    <p class="text-sm text-gray-500">Periode {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            @if($performance->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKPD</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Kuota</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($performance as $index => $skpd)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ strtoupper(substr($skpd->nama_skpd, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 truncate max-w-[200px]">{{ $skpd->nama_skpd }}</p>
                                    @if($skpd->website_url)
                                        <a href="{{ $skpd->website_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 text-xs mt-0.5">
                                            <i data-lucide="external-link" class="w-3 h-3"></i>
                                            Website
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 rounded-xl text-sm font-bold text-gray-700">
                                {{ $skpd->kuota_bulanan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                {{ $skpd->approved_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-semibold">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                {{ $skpd->pending_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm font-semibold">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                {{ $skpd->rejected_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4" style="min-width: 180px;">
                            @php
                                $percentage = min($skpd->compliance_percentage, 100);
                                $bgClass = $skpd->is_compliant ? 'bg-green-500' : ($skpd->compliance_percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">{{ $skpd->approved_count }}/{{ $skpd->kuota_bulanan }} konten</span>
                                    <span class="font-semibold {{ $skpd->is_compliant ? 'text-green-600' : ($skpd->compliance_percentage >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $skpd->compliance_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $bgClass }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusConfig = match(true) {
                                    $skpd->is_compliant => ['class' => 'bg-green-100 text-green-700 border-green-200', 'icon' => 'check-circle'],
                                    $skpd->compliance_percentage >= 50 => ['class' => 'bg-yellow-100 text-yellow-700 border-yellow-200', 'icon' => 'alert-triangle'],
                                    default => ['class' => 'bg-red-100 text-red-700 border-red-200', 'icon' => 'x-circle']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $statusConfig['class'] }}">
                                <i data-lucide="{{ $statusConfig['icon'] }}" class="w-3.5 h-3.5"></i>
                                {{ $skpd->compliance_status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="px-6 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="bar-chart-3" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 font-medium text-lg">Tidak ada data SKPD</p>
                    <p class="text-gray-400 text-sm mt-1">Belum ada data performa untuk periode ini</p>
                </div>
            </div>
            @endif
        </div>
        
        @if($performance->count() > 0)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Menampilkan {{ $performance->count() }} SKPD</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                        <span>Memenuhi</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 bg-yellow-500 rounded-full"></span>
                        <span>Sebagian</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                        <span>Belum</span>
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
