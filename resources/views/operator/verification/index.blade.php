@extends('layouts.app')

@section('title', 'Verifikasi Konten')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Verifikasi Konten</h1>
            <p class="text-muted mb-0">Daftar konten yang menunggu verifikasi</p>
        </div>
        <span class="badge bg-warning text-dark fs-6">{{ $contents->count() }} Pending</span>
    </div>

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

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('operator.verification.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="skpd_id" class="form-label">SKPD</label>
                    <select name="skpd_id" id="skpd_id" class="form-select">
                        <option value="">Semua SKPD</option>
                        @foreach($skpds as $skpd)
                            <option value="{{ $skpd->id }}" {{ ($filters['skpd_id'] ?? '') == $skpd->id ? 'selected' : '' }}>
                                {{ $skpd->nama_skpd }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select name="kategori_id" id="kategori_id" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ ($filters['kategori_id'] ?? '') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Cari</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Cari judul atau deskripsi..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('operator.verification.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Content List -->
    <div class="card">
        <div class="card-body">
            @if($contents->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Tidak ada konten pending</h5>
                    <p class="text-muted">Semua konten sudah diverifikasi</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>SKPD</th>
                                <th>Kategori</th>
                                <th>Publisher</th>
                                <th>Tanggal Submit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contents as $content)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($content->judul, 50) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($content->deskripsi, 80) }}</small>
                                    </td>
                                    <td>{{ $content->skpd->nama_skpd ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $content->kategori->nama_kategori ?? '-' }}</span>
                                    </td>
                                    <td>{{ $content->publisher->name ?? '-' }}</td>
                                    <td>{{ $content->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('operator.verification.show', $content->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Review
                                        </a>
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
@endsection
