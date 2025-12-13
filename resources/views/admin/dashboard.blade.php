@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Dashboard Admin</h1>
    <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
    
    <!-- Statistics Cards -->
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
            <div class="card text-dark bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Konten Pending</h5>
                    <h2>{{ $stats['pending_content'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Konten Bulan Ini</h5>
                    <h2>{{ $stats['total_content_this_month'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">SKPD Belum Memenuhi Kuota</h5>
                    <h2>{{ $stats['non_compliant_skpd'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Konten Approved Bulan Ini</h5>
                    <h3 class="text-success">{{ $stats['approved_content_this_month'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Konten Rejected Bulan Ini</h5>
                    <h3 class="text-danger">{{ $stats['rejected_content_this_month'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Konten (Semua)</h5>
                    <h3 class="text-primary">{{ $stats['total_content_all_time'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <!-- SKPD Performance -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Performa SKPD Bulan Ini</h5>
                    <a href="{{ route('reports.skpd-performance') }}" class="btn btn-sm btn-primary">Lihat Detail</a>
                </div>
                <div class="card-body">
                    @if(isset($skpdPerformance) && $skpdPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>SKPD</th>
                                        <th>Approved</th>
                                        <th>Kuota</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($skpdPerformance->take(10) as $skpd)
                                    <tr>
                                        <td>{{ Str::limit($skpd->nama_skpd, 30) }}</td>
                                        <td>{{ $skpd->approved_count }}</td>
                                        <td>{{ $skpd->kuota_bulanan }}</td>
                                        <td>
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
                        <p class="text-muted text-center py-3">Tidak ada data SKPD</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Non-Compliant SKPDs -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">SKPD Belum Memenuhi Kuota</h5>
                </div>
                <div class="card-body">
                    @if(isset($nonCompliantSkpds) && $nonCompliantSkpds->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($nonCompliantSkpds->take(5) as $skpd)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ Str::limit($skpd->nama_skpd, 20) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $skpd->approved_count }}/{{ $skpd->kuota_bulanan }} konten</small>
                                </div>
                                <span class="badge bg-{{ $skpd->compliance_percentage >= 50 ? 'warning' : 'danger' }}">
                                    {{ $skpd->compliance_percentage }}%
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-success text-center py-3">
                            <i class="bi bi-check-circle"></i> Semua SKPD memenuhi kuota
                        </p>
                    @endif
                </div>
            </div>

            <!-- Notifications -->
            @if(isset($notifications) && $notifications->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notifikasi Terbaru</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($notifications->take(5) as $notification)
                        <li class="list-group-item">
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            <p class="mb-0">{{ $notification->message }}</p>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Quick Links -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Menu Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.skpd.index') }}" class="btn btn-outline-primary">Kelola SKPD</a>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-outline-primary">Kelola User</a>
                        <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-primary">Kelola Kategori</a>
                        <a href="{{ route('reports.content-history') }}" class="btn btn-outline-secondary">Riwayat Konten</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
