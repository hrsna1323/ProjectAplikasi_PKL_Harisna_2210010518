@extends('layouts.app')

@section('title', 'Edit Konten')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('publisher.content.index') }}">Daftar Konten</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('publisher.content.show', $content->id) }}">Detail</a></li>
                    <li class="breadcrumb-item active">Edit Konten</li>
                </ol>
            </nav>
            <h1 class="h3">Edit Konten</h1>
            <p class="text-muted">Perbarui informasi konten publikasi</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('publisher.content.update', $content->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Konten <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" name="judul" value="{{ old('judul', $content->judul) }}" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="4" required>{{ old('deskripsi', $content->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('kategori_id') is-invalid @enderror" 
                                    id="kategori_id" name="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('kategori_id', $content->kategori_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="url_publikasi" class="form-label">URL Publikasi <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('url_publikasi') is-invalid @enderror" 
                                   id="url_publikasi" name="url_publikasi" 
                                   value="{{ old('url_publikasi', $content->url_publikasi) }}" 
                                   placeholder="https://example.com/artikel" required>
                            @error('url_publikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Masukkan URL lengkap konten yang sudah dipublikasikan</div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_publikasi" class="form-label">Tanggal Publikasi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_publikasi') is-invalid @enderror" 
                                   id="tanggal_publikasi" name="tanggal_publikasi" 
                                   value="{{ old('tanggal_publikasi', $content->tanggal_publikasi->format('Y-m-d')) }}" required>
                            @error('tanggal_publikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('publisher.content.show', $content->id) }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Konten</h5>
                </div>
                <div class="card-body">
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
                    <p class="mb-2">Status saat ini: <span class="badge {{ $badgeClass }}">{{ $content->status }}</span></p>
                    
                    @if($content->status === 'Rejected')
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle"></i> Konten ini ditolak. Setelah diedit, status akan berubah menjadi "Pending" untuk diverifikasi ulang.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Panduan</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Pastikan konten sudah dipublikasikan di website</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> URL harus dapat diakses publik</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Pilih kategori yang sesuai</li>
                        <li class="mb-2"><i class="bi bi-info-circle text-info"></i> Konten akan diverifikasi ulang oleh Operator</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
