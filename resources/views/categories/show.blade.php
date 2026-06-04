@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-tag me-2"></i>Detail Kategori
                    </h1>
                    <p class="page-subtitle">Informasi lengkap kategori</p>
                </div>
                <div>
                    <a href="{{ route('categories.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            {{-- Info Card --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>Informasi Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Nama Kategori</th>
                            <td><strong>{{ $category->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Warna</th>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle" style="width: 24px; height: 24px; background-color: {{ $category->color ?? '#6c757d' }}"></div>
                                    <code>{{ $category->color ?? '#6c757d' }}</code>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $category->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah Ticket</th>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                    <i class="fas fa-ticket-alt me-1"></i>
                                    {{ $category->tickets_count }} ticket
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $category->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Update</th>
                            <td>{{ $category->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex gap-2">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Edit Kategori
                        </a>
                        @if($category->tickets_count == 0)
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus kategori ini?')">
                                <i class="fas fa-trash me-1"></i> Hapus Kategori
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            {{-- Tickets in this category --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ticket-alt text-primary me-2"></i>Ticket dalam Kategori Ini
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($category->tickets_count > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-primary bg-opacity-10">
                                    <tr>
                                        <th class="ps-3">Ticket #</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->tickets()->latest()->limit(10)->get() as $ticket)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="fw-bold text-primary">
                                                {{ $ticket->ticket_number }}
                                            </a>
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($ticket->title, 40) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $ticket->getStatusBadgeAttribute() }} bg-opacity-10 text-{{ $ticket->getStatusBadgeAttribute() }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 30px; height: 30px;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($category->tickets_count > 10)
                        <div class="text-center p-3">
                            <a href="{{ route('tickets.index', ['category_id' => $category->id]) }}" class="btn btn-sm btn-outline-primary">
                                Lihat semua {{ $category->tickets_count }} ticket
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada ticket dalam kategori ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        background: white !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 1rem 1.5rem;
    }
    .table th {
        font-weight: 600;
        color: #4b5563;
    }
</style>
@endsection
