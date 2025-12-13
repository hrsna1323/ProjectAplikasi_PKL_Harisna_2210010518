@extends('layouts.app')

@section('title', 'Monitoring SKPD')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Monitoring SKPD</h1>
    <p class="text-muted">Pantau progress publikasi setiap SKPD</p>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('operator.monitoring.index') }}" class="row g-3">
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
                    <h2>{{ $stats['total_skpd'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Memenuhi Kuota</h5>
                    <h2>{{ $stats['compliant'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-dark bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Sebagian</h5>
                    <h2>{{ $stats['partial'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Belum Memenuhi</h5>
                    <h2>{{ $stats['non_compliant'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>


    <!-- SKPD List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar SKPD - {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</h5>
        </div>
        <div class="card-body">
            @if($skpds->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>SKPD</th>
                                <th>Approved</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Kuota</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skpds as $skpd)
                            <tr>
                                <td>
                                    <strong>{{ $skpd->nama_skpd }}</strong>
                                    @if($skpd->website_url)
                                        <br><small class="text-muted">{{ $skpd->website_url }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $skpd->approved_count }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $skpd->pending_count }}</span></td>
                                <td><span class="badge bg-danger">{{ $skpd->rejected_count }}</span></td>
                                <td>{{ $skpd->kuota_bulanan }}</td>
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
                                <td>
                                    <a href="{{ route('operator.monitoring.show', $skpd->id) }}" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
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
