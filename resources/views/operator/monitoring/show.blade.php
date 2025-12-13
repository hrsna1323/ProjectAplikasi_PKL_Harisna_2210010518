@extends('layouts.app')

@section('title', 'Detail Monitoring - ' . $skpd->nama_skpd)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $skpd->nama_skpd }}</h1>
            <p class="text-muted mb-0">Detail monitoring dan tren publikasi</p>
        </div>
        <a href="{{ route('operator.monitoring.index') }}" class="btn btn-outline-secondary">
            &larr; Kembali
        </a>
    </div>

    <!-- SKPD Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi SKPD</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nama SKPD</th>
                            <td>{{ $skpd->nama_skpd }}</td>
                        </tr>
                        <tr>
                            <th>Website</th>
                            <td>
                                @if($skpd->website_url)
                                    <a href="{{ $skpd->website_url }}" target="_blank">{{ $skpd->website_url }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $skpd->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kuota Bulanan</th>
                            <td><strong>{{ $skpd->kuota_bulanan }}</strong> konten/bulan</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge bg-{{ $skpd->status == 'Active' || $skpd->status == 'Aktif' ? 'success' : 'secondary' }}">{{ $skpd->status }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Performa Bulan Ini</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-success">{{ $currentPerformance['approved'] }}</h3>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning">{{ $currentPerformance['pending'] }}</h3>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-danger">{{ $currentPerformance['rejected'] }}</h3>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <strong>Progress Kuota:</strong> {{ $currentPerformance['approved'] }}/{{ $currentPerformance['quota'] }}
                    </div>
                    <div class="progress" style="height: 25px;">
                        @php
                            $pct = $currentPerformance['compliance_percentage'];
                            $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                        @endphp
                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ min($pct, 100) }}%">
                            {{ $pct }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- 6-Month Trend -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Tren Publikasi 6 Bulan Terakhir</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            @foreach($trend as $item)
                                <th>{{ $item['month'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($trend as $item)
                                <td>
                                    <h4 class="mb-0 {{ $item['approved_count'] >= $item['quota'] ? 'text-success' : 'text-danger' }}">
                                        {{ $item['approved_count'] }}
                                    </h4>
                                    <small class="text-muted">dari {{ $item['quota'] }}</small>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($trend as $item)
                                <td>
                                    <div class="progress" style="height: 15px;">
                                        @php
                                            $pct = $item['compliance_percentage'];
                                            $color = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ min($pct, 100) }}%">
                                        </div>
                                    </div>
                                    <small>{{ $pct }}%</small>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Contents -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Konten Terbaru</h5>
        </div>
        <div class="card-body">
            @if($recentContents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Publisher</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentContents as $content)
                            <tr>
                                <td>{{ Str::limit($content->judul, 50) }}</td>
                                <td>{{ $content->kategori->nama_kategori ?? '-' }}</td>
                                <td>{{ $content->publisher->name ?? '-' }}</td>
                                <td>{{ $content->tanggal_publikasi?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'Draft' => 'secondary',
                                            'Pending' => 'warning',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            'Published' => 'info',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$content->status] ?? 'secondary' }}">
                                        {{ $content->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-3">Belum ada konten dari SKPD ini</p>
            @endif
        </div>
    </div>
</div>
@endsection
