@extends('layouts.app')

@section('title', 'Review Konten')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Review Konten</h1>
            <p class="text-muted mb-0">Detail konten untuk verifikasi</p>
        </div>
        <a href="{{ route('operator.verification.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Content Detail -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Konten</h5>
                    <span class="badge bg-warning text-dark">{{ $content->status }}</span>
                </div>
                <div class="card-body">
                    <h4>{{ $content->judul }}</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">SKPD</small>
                            <p class="mb-0">{{ $content->skpd->nama_skpd ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Kategori</small>
                            <p class="mb-0">
                                <span class="badge bg-secondary">{{ $content->kategori->nama_kategori ?? '-' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Publisher</small>
                            <p class="mb-0">{{ $content->publisher->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Tanggal Publikasi</small>
                            <p class="mb-0">{{ $content->tanggal_publikasi?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Deskripsi</small>
                        <p class="mb-0">{{ $content->deskripsi }}</p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">URL Publikasi</small>
                        <p class="mb-0">
                            <a href="{{ $content->url_publikasi }}" target="_blank" class="text-primary">
                                {{ $content->url_publikasi }}
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Tanggal Submit</small>
                            <p class="mb-0">{{ $content->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Terakhir Update</small>
                            <p class="mb-0">{{ $content->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification History -->
            @if($verificationHistory->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Verifikasi</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($verificationHistory as $verification)
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    @if($verification->isApproved())
                                        <span class="badge bg-success rounded-circle p-2">
                                            <i class="bi bi-check-lg"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-danger rounded-circle p-2">
                                            <i class="bi bi-x-lg"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $verification->status }}</strong>
                                        <small class="text-muted">{{ $verification->verified_at?->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <p class="mb-0 text-muted">oleh {{ $verification->verifikator->name ?? '-' }}</p>
                                    @if($verification->alasan)
                                        <p class="mb-0 mt-1"><em>"{{ $verification->alasan }}"</em></p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Verification Actions -->
        <div class="col-lg-4">
            @if($content->isPending())
            <!-- Approve Form -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Setujui Konten</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('operator.verification.approve', $content->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="alasan_approve" class="form-label">Alasan (Opsional)</label>
                            <textarea name="alasan" id="alasan_approve" class="form-control" rows="3"
                                      placeholder="Masukkan alasan persetujuan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg me-1"></i> Setujui
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reject Form -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Tolak Konten</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('operator.verification.reject', $content->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="alasan_reject" class="form-label">Alasan <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="alasan_reject" class="form-control @error('alasan') is-invalid @enderror" 
                                      rows="3" placeholder="Masukkan alasan penolakan..." required>{{ old('alasan') }}</textarea>
                            @error('alasan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 10 karakter</small>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-lg me-1"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-info-circle text-info" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Konten Sudah Diverifikasi</h5>
                    <p class="text-muted">Status: <strong>{{ $content->status }}</strong></p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
