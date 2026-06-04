@extends('layouts.app')

@section('title', 'Manajemen Kategori')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-tags me-2"></i>Manajemen Kategori
                    </h1>
                    <p class="page-subtitle">Kelola kategori ticket untuk sistem ticketing</p>
                </div>
                <div>
                    <a href="{{ route('categories.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Kategori Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Categories Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Daftar Kategori
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary bg-opacity-10">
                        <tr>
                            <th class="ps-3 py-3">#</th>
                            <th class="py-3">Nama Kategori</th>
                            <th class="py-3">Warna</th>
                            <th class="py-3">Deskripsi</th>
                            <th class="py-3">Jumlah Ticket</th>
                            <th class="py-3">Dibuat</th>
                            <th class="text-center py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $category->name }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle" style="width: 24px; height: 24px; background-color: {{ $category->color ?? '#6c757d' }}"></div>
                                    <span class="badge" style="background-color: {{ $category->color ?? '#6c757d' }}; color: white;">
                                        {{ $category->color ?? '#6c757d' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted" style="max-width: 250px;">
                                    {{ \Illuminate\Support\Str::limit($category->description ?? '-', 60) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                    <i class="fas fa-ticket-alt me-1"></i>
                                    {{ $category->tickets_count }} ticket
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <div class="small">{{ $category->created_at->format('d M Y') }}</div>
                                <div class="small text-muted">{{ $category->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('categories.show', $category) }}"
                                       class="btn btn-sm btn-outline-primary rounded-circle"
                                       style="width: 32px; height: 32px;"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-warning rounded-circle"
                                       style="width: 32px; height: 32px;"
                                       title="Edit Kategori">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($category->tickets_count == 0)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger rounded-circle delete-btn"
                                            style="width: 32px; height: 32px;"
                                            data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}"
                                            title="Hapus Kategori">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-circle"
                                            style="width: 32px; height: 32px;"
                                            disabled
                                            title="Tidak dapat dihapus karena memiliki ticket">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-tags fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted mb-3">Belum ada kategori</p>
                                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i>
                                        Tambah Kategori Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus kategori <strong id="categoryName"></strong>?</p>
                    <p class="text-danger mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Tindakan ini tidak dapat dibatalkan!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .empty-state {
        text-align: center;
        padding: 3rem;
    }
    .empty-state i {
        opacity: 0.5;
    }
    .table td, .table th {
        padding: 1rem 0.75rem;
    }
    @media (max-width: 768px) {
        .table td, .table th {
            padding: 0.75rem 0.5rem;
        }
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const categoryNameSpan = document.getElementById('categoryName');

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            const categoryName = this.dataset.name;

            categoryNameSpan.textContent = categoryName;
            deleteForm.action = `/categories/${categoryId}`;
            deleteModal.show();
        });
    });
});
</script>
@endpush
