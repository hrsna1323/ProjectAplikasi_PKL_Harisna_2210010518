@extends('layouts.app')

@section('title', 'Riwayat Konten')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Riwayat Konten</h1>
            <p class="text-muted mb-0">Lihat semua konten dengan filter</p>
        </div>
        <a href="{{ route('reports.export.content', request()->query()) }}" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.content-history') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">SKPD</label>
                    <select name="skpd_id" class="form-select">
                        <option value="">Semua SKPD</option>
                        @foreach($skpds as $skpd)
                            <option value="{{ $skpd->id }}" {{ ($filters['skpd_id'] ?? '') == $skpd->id ? 'selected' : '' }}>
                                {{ $skpd->nama_skpd }}
                            </option>
                        @endforeach
                    </select>
                </div>
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
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Results -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Hasil: {{ $contents->count() }} konten ditemukan</h5>
        </div>
        <div class="card-body">
            @if($contents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>SKPD</th>
                                <th>Kategori</th>
                                <th>Publisher</th>
                                <th>Tanggal Publikasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contents as $index => $content)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ Str::limit($content->judul, 40) }}</strong>
                                    @if($content->url_publikasi)
                                        <br>
                                        <small>
                                            <a href="{{ $content->url_publikasi }}" target="_blank" class="text-muted">
                                                {{ Str::limit($content->url_publikasi, 30) }}
                                            </a>
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $content->skpd->nama_skpd ?? '-' }}</td>
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
                                <td>
                                    @if(auth()->user()->role === 'Operator')
                                        <a href="{{ route('operator.verification.show', $content->id) }}" class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">Tidak ada konten yang sesuai dengan filter</p>
            @endif
        </div>
    </div>
</div>
@endsection
