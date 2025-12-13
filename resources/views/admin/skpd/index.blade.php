@extends('layouts.app')

@section('title', 'Daftar SKPD')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Daftar SKPD</h1>
        <a href="{{ route('admin.skpd.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah SKPD
        </a>
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

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.skpd.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SKPD Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Status Kepatuhan Kuota - {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama SKPD</th>
                            <th>Website</th>
                            <th>Email</th>
                            <th>Kuota</th>
                            <th>Approved</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($skpds as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['skpd']->nama_skpd }}</td>
                                <td>
                                    @if($item['skpd']->website_url)
                                        <a href="{{ $item['skpd']->website_url }}" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-link-45deg"></i> Link
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item['skpd']->email ?? '-' }}</td>
                                <td>{{ $item['quota'] }}</td>
                                <td>{{ $item['approved_count'] }}</td>
                                <td style="width: 150px;">
                                    <div class="progress" style="height: 20px;">
                                        @php
                                            $percentage = min($item['compliance_percentage'], 100);
                                            $bgClass = $percentage >= 100 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress-bar {{ $bgClass }}" role="progressbar" 
                                             style="width: {{ $percentage }}%">
                                            {{ number_format($item['compliance_percentage'], 0) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($item['compliance_status']) {
                                            'Memenuhi' => 'success',
                                            'Sebagian' => 'warning',
                                            'Belum Memenuhi' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $item['compliance_status'] }}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.skpd.show', $item['skpd']->id) }}" class="btn btn-outline-info" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.skpd.edit', $item['skpd']->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.skpd.destroy', $item['skpd']->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus SKPD ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data SKPD</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
