@extends('layouts.app')

@section('title', 'Detail Konten')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('publisher.content.index') }}">Daftar Konten</a></li>
                    <li class="breadcrumb-item active">Detail Konten</li>
                </ol>
            </nav>
        </div>
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

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Konten</h5>
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
                </div>
                <div class="card-body">
                    <h4>{{ $content->judul }}</h4>
                    
                    <div class="mb-3">
                        <span class="badge bg-light text-dark">{{ $content->kategori->nama_kategori ?? '-' }}</span>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Deskripsi</h6>
                        <p>{{ $content->deskripsi }}</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">URL Publikasi</h6>
                            <a href="{{ $content->url_publikasi }}" target="_blank" class="text-break">
                                {{ $content->url_publikasi }} <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Tanggal Publikasi</h6>
                            <p>{{ $content->tanggal_publikasi->format('d F Y') }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Dibuat oleh</h6>
                            <p>{{ $content->publisher->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Tanggal Input</h6>
                            <p>{{ $content->created_at->format('d F Y H:i') }}</p>
                        </div>
                    </div>

                    @if($content->canBeEdited())
                        <div class="mt-4">
                            <a href="{{ route('publisher.content.edit', $content->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit Konten
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Timeline Verifikasi</h5>
                </div>
                <div class="card-body">
                    @if($verificationHistory->isEmpty())
                        <p class="text-muted mb-0">Belum ada riwayat verifikasi</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($verificationHistory as $verification)
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <strong>
                                            @if($verification->status === 'Approved')
                                                <span class="text-success"><i class="bi bi-check-circle"></i> Disetujui</span>
                                            @else
                                                <span class="text-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                            @endif
                                        </strong>
                                        <small class="text-muted">{{ $verification->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">Oleh: {{ $verification->verifikator->name ?? '-' }}</small>
                                    </div>
                                    @if($verification->alasan)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $verification->alasan }}</small>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi SKPD</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $content->skpd->nama_skpd ?? '-' }}</strong></p>
                    @if($content->skpd)
                        <p class="mb-0 text-muted small">{{ $content->skpd->website_url }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
