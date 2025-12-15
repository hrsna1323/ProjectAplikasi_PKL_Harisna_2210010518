@extends('layouts.app')

@section('title', 'Edit SKPD')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit SKPD</h1>
        <a href="{{ route('admin.skpd.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Edit SKPD: {{ $skpd->nama_skpd }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.skpd.update', $skpd->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="nama_skpd" class="form-label">Nama SKPD <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_skpd') is-invalid @enderror" 
                           id="nama_skpd" name="nama_skpd" value="{{ old('nama_skpd', $skpd->nama_skpd) }}" required>
                    @error('nama_skpd')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="website_url" class="form-label">Website URL</label>
                    <input type="url" class="form-control @error('website_url') is-invalid @enderror" 
                           id="website_url" name="website_url" value="{{ old('website_url', $skpd->website_url) }}" 
                           placeholder="https://example.go.id">
                    @error('website_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Kontak</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $skpd->email) }}" 
                           placeholder="email@example.go.id">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kuota_bulanan" class="form-label">Kuota Bulanan</label>
                            <input type="number" class="form-control @error('kuota_bulanan') is-invalid @enderror" 
                                   id="kuota_bulanan" name="kuota_bulanan" 
                                   value="{{ old('kuota_bulanan', $skpd->kuota_bulanan) }}" 
                                   min="1" max="100">
                            <div class="form-text">Jumlah minimum konten yang harus dipublikasikan per bulan</div>
                            @error('kuota_bulanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="Active" {{ old('status', $skpd->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status', $skpd->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update
                    </button>
                    <a href="{{ route('admin.skpd.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
