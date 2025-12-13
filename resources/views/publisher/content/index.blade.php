@extends('layouts.app')

@section('title', 'Daftar Konten')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">Daftar Konten</h1>
            <p class="text-muted">Kelola konten publikasi SKPD Anda</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('publisher.content.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Konten
            </a>
        </div>
    </div>

    {{-- Quota Progress Card --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Progress Kuota Bulan Ini</h5>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar {{ $quotaProgress['is_fulfilled'] ? 'bg-success' : 'bg-warning' }}" 
                             role="progressbar" 
                             style="width: {{ min($quotaProgress['percentage'], 100) }}%"
                             aria-valuenow="{{ $quotaProgress['approved'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $quotaProgress['quota'] }}">
                            {{ $quotaProgress['approved'] }} / {{ $quotaProgress['quota'] }}
                        </div>
                    </div>
                    <small class="text-muted">
                        Disetujui: {{ $quotaProgress['approved'] }} | 
                        Pending: {{ $quotaProgress['pending'] }} | 
                        Sisa: {{ $quotaProgress['remaining'] }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('publisher.content.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_id" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($filters['kategori_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Judul..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2">Filter</button>
                    <a href="{{ route('publisher.content.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Content Table --}}
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal Publikasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contents as $content)
                            <tr>
                                <td>
                                    <a href="{{ route('publisher.content.show', $content->id) }}">
                                        {{ Str::limit($content->judul, 50) }}
                                    </a>
                                </td>
                                <td>{{ $content->kategori->nama_kategori ?? '-' }}</td>
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
                                <td>
                                    <a href="{{ route('publisher.content.show', $content->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($content->canBeEdited())
                                        <a href="{{ route('publisher.content.edit', $content->id) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada konten. <a href="{{ route('publisher.content.create') }}">Tambah konten baru</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
