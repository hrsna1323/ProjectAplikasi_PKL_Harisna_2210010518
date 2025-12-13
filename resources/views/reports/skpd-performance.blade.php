@extends('layouts.app')

@section('title', 'Performa SKPD')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Laporan Performa SKPD</h1>
            <p class="text-muted mb-0">{{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
        </div>
        <a href="{{ route('reports.export.skpd', ['month' => $month, 'year' => $year]) }}" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.skpd-performance') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        @for($y = now()->year - 2; $y <= now()->year; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total SKPD</h5>
                    <h2>{{ $summary['total_skpd'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Memenuhi Kuota</h5>
                    <h2>{{ $summary['compliant_skpd'] }}</h2>
                    <small>{{ $summary['compliance_rate'] }}% dari total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Belum Memenuhi</h5>
                    <h2>{{ $summary['non_compliant_skpd'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata Kepatuhan</h5>
                    <h2>{{ $summary['average_compliance'] }}%</h2>
                </div>
            </div>
        </div>
    </div>


    <!-- Content Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Total Approved</h5>
                    <h3 class="text-success">{{ $summary['total_approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Total Pending</h5>
                    <h3 class="text-warning">{{ $summary['total_pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Total Rejected</h5>
                    <h3 class="text-danger">{{ $summary['total_rejected'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Performa per SKPD</h5>
        </div>
        <div class="card-body">
            @if($performance->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>SKPD</th>
                                <th>Website</th>
                                <th>Kuota</th>
                                <th>Approved</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($performance as $index => $skpd)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $skpd->nama_skpd }}</strong></td>
                                <td>
                                    @if($skpd->website_url)
                                        <a href="{{ $skpd->website_url }}" target="_blank" class="text-muted">
                                            {{ Str::limit($skpd->website_url, 25) }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $skpd->kuota_bulanan }}</td>
                                <td><span class="badge bg-success">{{ $skpd->approved_count }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $skpd->pending_count }}</span></td>
                                <td><span class="badge bg-danger">{{ $skpd->rejected_count }}</span></td>
                                <td style="min-width: 150px;">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $skpd->is_compliant ? 'success' : ($skpd->compliance_percentage >= 50 ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ min($skpd->compliance_percentage, 100) }}%">
                                            {{ $skpd->compliance_percentage }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $skpd->is_compliant ? 'success' : ($skpd->compliance_percentage >= 50 ? 'warning' : 'danger') }}">
                                        {{ $skpd->compliance_status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">Tidak ada data SKPD</p>
            @endif
        </div>
    </div>
</div>
@endsection
