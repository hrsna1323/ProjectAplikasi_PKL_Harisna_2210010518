@extends('layouts.app')

@section('title', 'Riwayat Verifikasi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Riwayat Verifikasi</h1>
            <p class="text-muted mb-0">Timeline verifikasi untuk konten: {{ $content->judul }}</p>
        </div>
        <a href="{{ route('operator.verification.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Content Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Konten</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>{{ $content->judul }}</h5>
                            <p class="text-muted">{{ Str::limit($content->deskripsi, 200) }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $content->status === 'Approved' ? 'success' : ($content->status === 'Rejected' ? 'danger' : 'warning') }} fs-6">
                                {{ $content->status }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">SKPD</small>
                            <p class="mb-0">{{ $content->skpd->nama_skpd ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Publisher</small>
                            <p class="mb-0">{{ $content->publisher->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Tanggal Submit</small>
                            <p class="mb-0">{{ $content->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Timeline Verifikasi</h5>
                </div>
                <div class="card-body">
                    @if($verificationHistory->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Belum Ada Riwayat</h5>
                            <p class="text-muted">Konten ini belum pernah diverifikasi</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($verificationHistory as $verification)
                                <div class="card mb-3 border-{{ $verification->isApproved() ? 'success' : 'danger' }}">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0">
                                                @if($verification->isApproved())
                                                    <span class="badge bg-success p-2">
                                                        <i class="bi bi-check-lg"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger p-2">
                                                        <i class="bi bi-x-lg"></i>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">{{ $verification->status }}</h6>
                                                    <small class="text-muted">
                                                        {{ $verification->verified_at?->format('d/m/Y H:i') }}
                                                    </small>
                                                </div>
                                                <p class="mb-1 text-muted">
                                                    <i class="bi bi-person me-1"></i>
                                                    {{ $verification->verifikator->name ?? 'Unknown' }}
                                                </p>
                                                @if($verification->alasan)
                                                    <div class="mt-2 p-2 bg-light rounded">
                                                        <small class="text-muted">Alasan:</small>
                                                        <p class="mb-0">{{ $verification->alasan }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('operator.verification.show', $content->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-eye me-1"></i> Lihat Detail
                    </a>
                    <a href="{{ $content->url_publikasi }}" target="_blank" class="btn btn-outline-primary w-100">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka URL
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
