@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Dashboard Operator</h1>
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

    <div class="row">
        <!-- Pending Contents -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Konten Menunggu Verifikasi</h5>
                    <a href="{{ route('operator.verification.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if($pendingContents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>SKPD</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingContents as $content)
                                    <tr>
                                        <td>{{ Str::limit($content->judul, 40) }}</td>
                                        <td>{{ $content->skpd->nama_skpd ?? '-' }}</td>
                                        <td>{{ $content->tanggal_publikasi?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('operator.verification.show', $content->id) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Tidak ada konten yang menunggu verifikasi</p>
                    @endif
                </div>
            </div>
        </div>


        <!-- Non-Compliant SKPDs -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">SKPD Belum Memenuhi Kuota</h5>
                    <a href="{{ route('operator.monitoring.index') }}" class="btn btn-sm btn-primary">Monitoring</a>
                </div>
                <div class="card-body">
                    @if(isset($nonCompliantSkpds) && $nonCompliantSkpds->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($nonCompliantSkpds as $skpd)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ Str::limit($skpd->nama_skpd, 25) }}</strong>
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
                        <p class="text-muted text-center py-3">Semua SKPD memenuhi kuota</p>
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
        </div>
    </div>
</div>
@endsection
