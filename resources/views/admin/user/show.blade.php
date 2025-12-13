@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Detail User</h1>
        <div>
            <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi User</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nama</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>
                                <span class="badge bg-{{ $user->role == 'Admin' ? 'danger' : ($user->role == 'Operator' ? 'warning' : 'info') }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>SKPD</th>
                            <td>{{ $user->skpd?->nama_skpd ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diupdate</th>
                            <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik</h5>
                </div>
                <div class="card-body">
                    @if($user->isPublisher())
                        <div class="row text-center">
                            <div class="col-6 col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="mb-0">{{ $stats['total_contents'] }}</h3>
                                    <small class="text-muted">Total Konten</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="mb-0 text-success">{{ $stats['approved_contents'] }}</h3>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="mb-0 text-warning">{{ $stats['pending_contents'] }}</h3>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="mb-0 text-danger">{{ $stats['rejected_contents'] }}</h3>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                        </div>
                    @elseif($user->isOperator())
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h3 class="mb-0">{{ $stats['total_verifications'] }}</h3>
                                    <small class="text-muted">Total Verifikasi</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Tidak ada statistik untuk Admin</p>
                    @endif
                </div>
            </div>

            @if($user->skpd)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi SKPD</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="30%">Nama SKPD</th>
                                <td>{{ $user->skpd->nama_skpd }}</td>
                            </tr>
                            <tr>
                                <th>Website</th>
                                <td>
                                    <a href="{{ $user->skpd->website_url }}" target="_blank">
                                        {{ $user->skpd->website_url }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $user->skpd->email }}</td>
                            </tr>
                            <tr>
                                <th>Kuota Bulanan</th>
                                <td>{{ $user->skpd->kuota_bulanan }} konten</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
