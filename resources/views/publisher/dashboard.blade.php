@extends('layouts.app')

@section('title', 'Dashboard Publisher')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3">Dashboard</h1>
            <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
    </div>

    {{-- Quota Progress --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Progress Kuota Bulan {{ now()->translatedFormat('F Y') }}</h5>
                    <div class="progress mb-2" style="height: 30px;">
                        <div class="progress-bar {{ $quotaProgress['is_fulfilled'] ? 'bg-success' : 'bg-warning' }}" 
                             role="progressbar" 
                             style="width: {{ min($quotaProgress['percentage'], 100) }}%">
                            {{ $quotaProgress['approved'] }} / {{ $quotaProgress['quota'] }} Konten
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col">
                            <h4 class="mb-0">{{ $quotaProgress['approved'] }}</h4>
                            <small class="text-muted">Disetujui</small>
                        </div>
                        <div class="col">
                            <h4 class="mb-0">{{ $quotaProgress['pending'] }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col">
                            <h4 class="mb-0">{{ $quotaProgress['remaining'] }}</h4>
                            <small class="text-muted">Sisa Kuota</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Konten</h5>
                    <h2>{{ $contentStats['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-dark bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2>{{ $contentStats['pending'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Disetujui</h5>
                    <h2>{{ $contentStats['approved'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Ditolak</h5>
                    <h2>{{ $contentStats['rejected'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Recent Contents --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Konten Terbaru</h5>
                    <a href="{{ route('publisher.content.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if($recentContents->isEmpty())
                        <p class="text-muted text-center py-4">Belum ada konten</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentContents as $content)
                                        <tr>
                                            <td>
                                                <a href="{{ route('publisher.content.show', $content->id) }}">
                                                    {{ Str::limit($content->judul, 40) }}
                                                </a>
                                            </td>
                                            <td>{{ $content->tanggal_publikasi->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match($content->status) {
                                                        'Draft' => 'bg-secondary',
                                                        'Pending' => 'bg-warning text-dark',
                                                        'Approved' => 'bg-success',
                                                        'Rejected' => 'bg-danger',
                                                        'Published' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $content->status }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notifikasi</h5>
                </div>
                <div class="card-body">
                    @if($notifications->isEmpty())
                        <p class="text-muted text-center py-4">Tidak ada notifikasi baru</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($notifications->take(5) as $notification)
                                <li class="mb-3 pb-3 border-bottom">
                                    <p class="mb-1">{{ $notification->message }}</p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body text-center">
                    <a href="{{ route('publisher.content.create') }}" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-plus-lg"></i> Tambah Konten Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
