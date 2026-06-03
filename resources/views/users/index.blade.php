@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="glassmorph-wrapper">
    <div class="container-fluid px-4">
        <!-- Page Header dengan Container Biru -->
        <div class="page-header-wrapper mb-4">
            <div class="page-header-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-users me-2"></i>Manajemen User
                        </h1>
                        <p class="page-subtitle">Kelola user, atur role, dan izin akses sistem</p>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('users.create') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i>
                            Tambah User
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>Daftar User
                    </h5>
                    <div class="search-box mt-2 mt-sm-0">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0 search-input"
                                   placeholder="Cari nama, username, email, atau departemen...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary bg-opacity-10">
                            <tr>
                                <th class="ps-3 py-3">Nama & Username</th>
                                <th class="py-3">Email & Telepon</th>
                                <th class="py-3">Departemen</th>
                                <th class="py-3">Role</th>
                                <th class="py-3">Status</th>
                                <th class="text-center py-3" width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary me-3">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                            <div class="small text-muted">
                                                <i class="fas fa-user-tag me-1"></i>
                                                @username: {{ $user->username ?? 'belum diatur' }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-id-card me-1"></i>
                                                ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-envelope me-2 text-muted" style="width: 16px;"></i>
                                            <div>
                                                <span>{{ $user->email }}</span>
                                                @if($user->email_verified_at)
                                                    <small class="text-success ms-1">
                                                        <i class="fas fa-check-circle"></i>
                                                    </small>
                                                @else
                                                    <small class="text-warning ms-1">
                                                        <i class="fas fa-clock"></i>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        @if($user->phone)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-phone me-2 text-muted" style="width: 16px;"></i>
                                            <span class="small">{{ $user->phone }}</span>
                                        </div>
                                        @else
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-phone me-2 text-muted" style="width: 16px;"></i>
                                            <span class="small text-muted">- belum diisi -</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($user->department)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building me-2 text-primary"></i>
                                            <span>{{ $user->department }}</span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building me-2 text-muted"></i>
                                            <span class="text-muted">- belum diisi -</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $roleConfig = [
                                            'admin' => ['class' => 'danger', 'icon' => 'fa-shield-alt', 'label' => 'Administrator'],
                                            'it_staff' => ['class' => 'warning', 'icon' => 'fa-user-cog', 'label' => 'Tim IT'],
                                            'user' => ['class' => 'info', 'icon' => 'fa-user', 'label' => 'User']
                                        ];
                                        $config = $roleConfig[$user->role] ?? ['class' => 'secondary', 'icon' => 'fa-user', 'label' => ucfirst($user->role)];
                                    @endphp
                                    <span class="badge bg-{{ $config['class'] }} bg-opacity-10 text-{{ $config['class'] }} px-3 py-2">
                                        <i class="fas {{ $config['icon'] }} me-1"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            <i class="fas fa-user-check me-1"></i>
                                            Aktif (Anda)
                                        </span>
                                    @else
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                Active
                                            </span>
                                            @if(!$user->email_verified_at)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success verify-email-btn"
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}">
                                                    <i class="fas fa-envelope"></i> Verifikasi
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('users.show', $user->id) }}"
                                           class="btn btn-sm btn-outline-info rounded-circle"
                                           style="width: 32px; height: 32px;"
                                           title="Detail User">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('users.edit', $user->id) }}"
                                           class="btn btn-sm btn-outline-warning rounded-circle"
                                           style="width: 32px; height: 32px;"
                                           title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user->id) }}"
                                                  method="POST"
                                                  class="d-inline delete-form"
                                                  data-user-name="{{ $user->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle delete-btn"
                                                        style="width: 32px; height: 32px;"
                                                        title="Hapus User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                                    style="width: 32px; height: 32px;"
                                                    disabled
                                                    title="Tidak dapat menghapus akun sendiri">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                        <p class="text-muted mb-3">Tidak ada user ditemukan</p>
                                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Tambah User Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($users->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-sm-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} user
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Hidden form untuk verifikasi email -->
<form id="verify-email-form" action="" method="POST" style="display: none;">
    @csrf
</form>

<style>
    /* Additional styles specific to user management */
    .avatar-sm {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Search box styling */
    .search-box .input-group-text {
        border-radius: 8px 0 0 8px;
    }

    .search-box .form-control {
        border-radius: 0 8px 8px 0;
    }

    .search-box .form-control:focus {
        border-color: var(--tosca-primary);
        box-shadow: none;
    }

    /* Alert styling */
    .alert {
        border: none;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    /* Button styling */
    .btn-outline-info:hover,
    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    .btn-outline-info:hover i,
    .btn-outline-warning:hover i,
    .btn-outline-danger:hover i {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    .verify-email-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        border-radius: 6px;
    }

    .verify-email-btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    /* Card header styling */
    .card-header {
        background: white !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 1rem 1.5rem;
    }

    /* Table cell styling */
    .table td, .table th {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badge styling */
    .badge {
        font-weight: 500;
        border-radius: 8px;
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            gap: 1rem;
        }

        .search-box {
            width: 100%;
        }

        .search-box .input-group {
            width: 100%;
        }

        .table td, .table th {
            padding: 0.75rem 0.5rem;
        }

        .btn-sm.rounded-circle {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.7rem;
        }

        .avatar-sm {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }

        /* Sembunyikan kolom tertentu di mobile */
        .table th:nth-child(3),
        .table td:nth-child(3) {
            display: none;
        }
    }

    @media (max-width: 576px) {
        /* Sembunyikan kolom telepon di mobile */
        .table th:nth-child(2) .d-flex:last-child,
        .table td:nth-child(2) .d-flex:last-child {
            display: none;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality (cari nama, username, email, atau departemen)
        const searchInput = document.querySelector('.search-input');
        const tableRows = document.querySelectorAll('tbody tr');

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();

                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // SweetAlert confirmation for delete
        const deleteForms = document.querySelectorAll('.delete-form');

        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const userName = this.dataset.userName || 'user ini';

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    html: `Anda akan menghapus user <strong>${userName}</strong><br>Data yang dihapus tidak dapat dikembalikan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });

        // Email verification
        const verifyButtons = document.querySelectorAll('.verify-email-btn');
        const verifyForm = document.getElementById('verify-email-form');

        verifyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const userName = this.dataset.userName;

                Swal.fire({
                    title: 'Verifikasi Email',
                    html: `Apakah Anda yakin ingin memverifikasi email user <strong>${userName}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, verifikasi!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        verifyForm.action = `/users/${userId}/verify-email`;
                        verifyForm.submit();
                    }
                });
            });
        });

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                if (bsAlert) {
                    bsAlert.close();
                }
            }, 5000);
        });
    });
</script>
@endsection
