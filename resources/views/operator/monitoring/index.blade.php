@extends('layouts.app')

@section('title', 'Monitoring SKPD')
@section('page-title', 'Monitoring SKPD')
@section('page-subtitle', 'Pantau progress publikasi setiap SKPD')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Monitoring SKPD</h1>
            <p class="text-gray-500 mt-1">Pantau progress publikasi setiap SKPD</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('operator.monitoring.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total SKPD</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $stats['total_skpd'] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="building-2" class="w-7 h-7 text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Memenuhi Kuota</p>
                    <p class="text-3xl font-bold mt-2 text-green-600">{{ $stats['compliant'] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-7 h-7 text-green-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Sebagian</p>
                    <p class="text-3xl font-bold mt-2 text-yellow-600">{{ $stats['partial'] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-yellow-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-7 h-7 text-yellow-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Belum Memenuhi</p>
                    <p class="text-3xl font-bold mt-2 text-red-600">{{ $stats['non_compliant'] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-7 h-7 text-red-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- SKPD List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Daftar SKPD</h2>
                    <p class="text-sm text-gray-500">{{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            @if($skpds->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKPD</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Kuota</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($skpds as $skpd)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="building" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $skpd->nama_skpd }}</p>
                                    @if($skpd->website_url)
                                        <a href="{{ $skpd->website_url }}" target="_blank" class="text-xs text-blue-600 hover:underline">{{ Str::limit($skpd->website_url, 30) }}</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">
                                {{ $skpd->approved_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-semibold">
                                {{ $skpd->pending_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-sm font-semibold">
                                {{ $skpd->rejected_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium text-gray-800">{{ $skpd->kuota_bulanan }}</td>
                        <td class="px-6 py-4" style="min-width: 150px;">
                            @php
                                $percentage = min($skpd->compliance_percentage, 100);
                                $bgClass = $skpd->is_compliant ? 'bg-green-500' : ($skpd->compliance_percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="{{ $bgClass }} h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-600 w-10">{{ $skpd->compliance_percentage }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusConfig = match(true) {
                                    $skpd->is_compliant => ['class' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
                                    $skpd->compliance_percentage >= 50 => ['class' => 'bg-yellow-100 text-yellow-700', 'icon' => 'alert-triangle'],
                                    default => ['class' => 'bg-red-100 text-red-700', 'icon' => 'x-circle']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusConfig['class'] }}">
                                <i data-lucide="{{ $statusConfig['icon'] }}" class="w-3.5 h-3.5"></i>
                                {{ $skpd->compliance_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('operator.monitoring.show', $skpd->id) }}" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-medium text-sm">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="building-2" class="w-8 h-8 text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium">Tidak ada data SKPD</p>
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
