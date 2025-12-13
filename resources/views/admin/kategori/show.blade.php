@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Detail Kategori</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.kategori.edit', $kategori->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Kategori</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8">{{ $kategori->nama_kategori }}</dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $kategori->deskripsi ?? '-' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($kategori->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Total Konten</dt>
                        <dd class="col-sm-8">{{ $kategori->contents_count }}</dd>

                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8">{{ $kategori->created_at->format('d M Y H:i') }}</dd>

                        <dt class="col-sm-4">Diperbarui</dt>
                        <dd class="col-sm-8">{{ $kategori->updated_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Konten Terbaru dalam Kategori Ini</h5>
                </div>
                <div class="card-body">
                    @if($recentContents->isEmpty())
                        <p class="text-center text-muted py-4">Belum ada konten dalam kategori ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>SKPD</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentContents as $content)
                                        <tr>
                                            <td>{{ Str::limit($content->judul, 40) }}</td>
                                            <td>{{ $content->skpd->nama_skpd ?? '-' }}</td>
                                            <td>
                                                @switch($content->status)
                                                    @case('Draft')
                                                        <span class="badge bg-secondary">Draft</span>
                                                        @break
                                                    @case('Pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                        @break
                                                    @case('Approved')
                                                        <span class="badge bg-success">Approved</span>
                                                        @break
                                                    @case('Rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                        @break
                                                    @case('Published')
                                                        <span class="badge bg-info">Published</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>{{ $content->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
