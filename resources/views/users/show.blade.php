@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="glassmorph-wrapper">
    <div class="container-fluid px-4">
        <!-- Page Header dengan Container Biru -->
        <div class="page-header-wrapper mb-4">
            <div class="page-header-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-user-circle me-2"></i>Detail User
                        </h1>
                        <p class="page-subtitle">Informasi lengkap dan detail akun user</p>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i>
                            Edit User
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
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

        <!-- User Profile Card -->
        <div class="row">
            <!-- Left Column - Profile & Avatar -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <!-- Avatar Large -->
                        <div class="avatar-large mx-auto mb-4">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>

                        <h3 class="mb-1">{{ $user->name }}</h3>
                        <div class="mb-3">
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
                        </div>

                        <!-- Status Card -->
                        <div class="user-status-card mt-4">
                            <div class="status-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Member sejak</span>
                                <strong>{{ $user->created_at->format('d M Y') }}</strong>
                            </div>
                            <div class="status-item">
                                <i class="fas fa-clock"></i>
                                <span>Terakhir update</span>
                                <strong>{{ $user->updated_at->format('d M Y') }}</strong>
                            </div>
                            <div class="status-item">
                                <i class="fas fa-id-card"></i>
                                <span>ID User</span>
                                <strong>#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</strong>
                            </div>
                        </div>

                        <!-- Badge untuk user yang sedang login -->
                        @if($user->id === auth()->id())
                            <div class="mt-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                    <i class="fas fa-user-check me-1"></i>
                                    Akun Anda (Sedang Aktif)
                                </span>
                            </div>
                        @endif

                        <!-- Tombol Verifikasi Email (Admin Only) -->
                        @if(auth()->user()->isAdmin() && !$user->email_verified_at && $user->id !== auth()->id())
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-success" onclick="verifyEmail({{ $user->id }})">
                                    <i class="fas fa-envelope me-1"></i>
                                    Verifikasi Email User
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - User Details -->
            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Informasi Lengkap
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="user-details-grid">
                            <!-- Username Field -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-user-tag"></i>
                                    Username
                                </label>
                                <div class="detail-value">
                                    @if($user->username)
                                        <code class="username-code">@ {{ $user->username }}</code>
                                    @else
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Belum diatur
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Email Field -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <div class="detail-value">
                                    <div>{{ $user->email }}</div>
                                    @if($user->email_verified_at)
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Terverifikasi pada {{ $user->email_verified_at->format('d M Y H:i') }}
                                        </small>
                                    @else
                                        <small class="text-warning">
                                            <i class="fas fa-clock"></i> Belum diverifikasi
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <!-- Phone Field (BARU) -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-phone"></i>
                                    Nomor Telepon
                                </label>
                                <div class="detail-value">
                                    @if($user->phone)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-phone-alt text-success"></i>
                                            <span>{{ $user->phone }}</span>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-success ms-2"
                                               style="padding: 0.25rem 0.5rem;">
                                                <i class="fab fa-whatsapp"></i> Chat
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-minus-circle"></i>
                                            Belum diisi
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Department Field (BARU) -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-building"></i>
                                    Departemen
                                </label>
                                <div class="detail-value">
                                    @if($user->department)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-building text-primary"></i>
                                            <span>{{ $user->department }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-minus-circle"></i>
                                            Belum diisi
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Name Field -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-user"></i>
                                    Nama Lengkap
                                </label>
                                <div class="detail-value">
                                    {{ $user->name }}
                                </div>
                            </div>

                            <!-- Role Field -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-tag"></i>
                                    Role Akses
                                </label>
                                <div class="detail-value">
                                    <span class="role-badge role-{{ $user->role }}">
                                        <i class="fas {{ $config['icon'] }} me-1"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Created At -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-calendar-plus"></i>
                                    Tanggal Dibuat
                                </label>
                                <div class="detail-value">
                                    <i class="fas fa-calendar-day me-1 text-muted"></i>
                                    {{ $user->created_at->format('d F Y') }}
                                    <small class="text-muted">{{ $user->created_at->format('H:i') }} WIB</small>
                                </div>
                            </div>

                            <!-- Updated At -->
                            <div class="detail-item">
                                <label>
                                    <i class="fas fa-calendar-edit"></i>
                                    Terakhir Diupdate
                                </label>
                                <div class="detail-value">
                                    <i class="fas fa-clock me-1 text-muted"></i>
                                    {{ $user->updated_at->format('d F Y H:i') }} WIB
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info Card -->
                        <div class="additional-info mt-4">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Hak Akses Sistem</h6>
                                    <p>
                                        User ini memiliki akses sebagai
                                        <strong>{{ $config['label'] }}</strong>
                                        @if($user->isAdmin())
                                            dengan hak akses penuh ke seluruh sistem termasuk manajemen user, ticket, dan laporan.
                                        @elseif($user->isIT())
                                            dengan hak akses mengelola ticket, merespon permintaan, dan melihat laporan.
                                        @else
                                            dengan hak akses terbatas untuk membuat ticket dan melihat ticket sendiri.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info Card 2 - Department Info (BARU) -->
                        @if($user->department)
                        <div class="additional-info mt-3">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Informasi Departemen</h6>
                                    <p>
                                        User ini bekerja di departemen <strong>{{ $user->department }}</strong>.
                                        @if($user->phone)
                                            Dapat dihubungi melalui nomor telepon <strong>{{ $user->phone }}</strong>.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="action-buttons mt-4">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i>
                                    Edit User
                                </a>

                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user->id) }}"
                                          method="POST"
                                          class="d-inline delete-form"
                                          data-user-name="{{ $user->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash me-1"></i>
                                            Hapus User
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Kembali ke Daftar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form untuk email verification -->
<form id="verify-email-form" action="" method="POST" style="display: none;">
    @csrf
</form>

<style>
    /* Avatar Large */
    .avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 600;
        color: white;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .avatar-large:hover {
        transform: scale(1.05);
    }

    /* User Status Card */
    .user-status-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .status-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        text-align: center;
    }

    .status-item i {
        color: var(--primary);
        font-size: 1.2rem;
        margin-bottom: 0.25rem;
    }

    .status-item span {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-item strong {
        font-size: 0.85rem;
        color: #1f2937;
    }

    /* User Details Grid */
    .user-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .detail-item:hover {
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .detail-item label {
        font-weight: 600;
        color: #4b5563;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0;
    }

    .detail-item label i {
        margin-right: 0.25rem;
        font-size: 0.7rem;
        color: var(--primary);
    }

    .detail-value {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 500;
    }

    .detail-value small {
        font-size: 0.7rem;
        margin-left: 0.5rem;
    }

    .username-code {
        font-family: 'Courier New', monospace;
        font-size: 1rem;
        background: #e5e7eb;
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        display: inline-block;
        color: #1f2937;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .role-admin {
        background: #fee2e2;
        color: #dc2626;
    }

    .role-it_staff {
        background: #fed7aa;
        color: #ea580c;
    }

    .role-user {
        background: #dbeafe;
        color: #2563eb;
    }

    /* Additional Info Card */
    .additional-info {
        margin-top: 1.5rem;
    }

    .info-card {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-radius: 12px;
        border-left: 4px solid var(--primary);
    }

    .info-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .info-content h6 {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #1f2937;
    }

    .info-content p {
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 0;
        line-height: 1.4;
    }

    /* Alert Styling */
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

    /* Buttons */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .user-details-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .user-status-card {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .avatar-large {
            width: 90px;
            height: 90px;
            font-size: 2rem;
        }

        .info-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons .d-flex {
            justify-content: center !important;
        }
    }

    @media (max-width: 576px) {
        .page-header-blue .d-flex {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .detail-item {
            padding: 0.5rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

    // Email verification function
    function verifyEmail(userId) {
        Swal.fire({
            title: 'Verifikasi Email',
            text: 'Apakah Anda yakin ingin memverifikasi email user ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, verifikasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('verify-email-form');
                form.action = `/users/${userId}/verify-email`;
                form.submit();
            }
        });
    }
</script>
@endsection
