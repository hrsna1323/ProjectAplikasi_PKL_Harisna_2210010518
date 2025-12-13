@extends('layouts.app')

@section('title', 'Detail SKPD')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Detail SKPD</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.skpd.edit', $skpd->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('admin.skpd.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- SKPD Info -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi SKPD</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 35%;">Nama SKPD</th>
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
                            <td>{{ $skpd->kuota_bulanan }} konten</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $skpd->status == 'Active' ? 'success' : 'secondary' }}">
                                    {{ $skpd->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Server</th>
                            <td>{{ $skpd->server->nama_server ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $skpd->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui</th>
                            <td>{{ $skpd->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Compliance Status -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Kepatuhan - {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</h5>
                </div>
                <div class="card-body">
                    @php
                        $statusClass = match($complianceStatus) {
                            'Memenuhi' => 'success',
                            'Sebagian' => 'warning',
                            'Belum Memenuhi' => 'danger',
                            default => 'secondary'
                        };
                        $percentage = $skpd->kuota_bulanan > 0 
                            ? min(($contentStats['approved'] / $skpd->kuota_bulanan) * 100, 100) 
                            : 0;
                    @endphp
                    
                    <div class="text-center mb-4">
                        <h2 class="display-4 text-{{ $statusClass }}">{{ $contentStats['approved'] }}/{{ $skpd->kuota_bulanan }}</h2>
                        <p class="text-muted">Konten Approved Bulan Ini</p>
                        <span class="badge bg-{{ $statusClass }} fs-6">{{ $complianceStatus }}</span>
                    </div>

                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-{{ $statusClass }}" role="progressbar" 
                             style="width: {{ $percentage }}%">
                            {{ number_format($percentage, 0) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Statistics -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Statistik Konten</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col">
                    <div class="border rounded p-3">
                        <h3 class="mb-0">{{ $contentStats['total'] }}</h3>
                        <small class="text-muted">Total Konten</small>
                    </div>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <h3 class="mb-0 text-warning">{{ $contentStats['pending'] }}</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <h3 class="mb-0 text-success">{{ $contentStats['approved'] }}</h3>
                        <small class="text-muted">Approved</small>
                    </div>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <h3 class="mb-0 text-danger">{{ $contentStats['rejected'] }}</h3>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <h3 class="mb-0 text-info">{{ $contentStats['published'] }}</h3>
                        <small class="text-muted">Published</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Publishers -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Publisher SKPD</h5>
        </div>
        <div class="card-body">
            @if($skpd->publishers->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skpd->publishers as $publisher)
                                <tr>
                                    <td>{{ $publisher->name }}</td>
                                    <td>{{ $publisher->username }}</td>
                                    <td>{{ $publisher->email ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $publisher->is_active ? 'success' : 'secondary' }}">
                                            {{ $publisher->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-3 mb-0">Belum ada publisher untuk SKPD ini</p>
            @endif
        </div>
    </div>
</div>
@endsection
