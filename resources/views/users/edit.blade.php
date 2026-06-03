@extends('layouts.app')

@section('content')
<div class="crud-container">
    <div class="form-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Edit User
            </h1>
            <div class="page-actions">
                <a href="{{ route('users.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- KOLOM KIRI -->
                        <div class="col-md-6">
                            <!-- Name Field -->
                            <div class="form-group">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}"
                                           required
                                           placeholder="Masukkan nama lengkap">
                                </div>
                                @error('name')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Username Field -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    Username <span class="text-danger">*</span>
                                    <small class="form-text text-muted">(Digunakan untuk login alternatif)</small>
                                </label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input type="text"
                                           name="username"
                                           id="username"
                                           class="form-control @error('username') is-invalid @enderror"
                                           value="{{ old('username', $user->username) }}"
                                           required
                                           placeholder="Masukkan username (contoh: johndoe)">
                                </div>
                                @error('username')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Email Field -->
                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}"
                                           required
                                           placeholder="Masukkan alamat email">
                                </div>
                                @error('email')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Phone Field -->
                            <div class="form-group">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text"
                                           name="phone"
                                           id="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="Masukkan nomor telepon (contoh: 08123456789)">
                                </div>
                                <small class="form-text text-muted">Format: 08123456789 atau +628123456789</small>
                                @error('phone')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="col-md-6">
                            <!-- Department Field -->
                            <div class="form-group">
                                <label for="department" class="form-label">Departemen</label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <select name="department"
                                            id="department"
                                            class="form-control @error('department') is-invalid @enderror">
                                        <option value="">Pilih Departemen</option>
                                        @foreach($departments as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('department', $user->department) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">Pilih departemen tempat user bekerja</small>
                                @error('department')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Role Field -->
                            <div class="form-group">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <select name="role"
                                            id="role"
                                            class="form-control @error('role') is-invalid @enderror"
                                            required>
                                        <option value="">Pilih Role</option>
                                        @foreach ($roles as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('role', $user->role) == $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">
                                    <strong>User:</strong> Hanya bisa membuat ticket<br>
                                    <strong>Tim IT:</strong> Bisa mengelola ticket yang ditugaskan<br>
                                    <strong>Admin:</strong> Akses penuh ke semua fitur
                                </small>
                                @error('role')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Email Verified Status -->
                            <div class="form-group">
                                <label class="form-label">Status Verifikasi Email</label>
                                <div class="input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <div class="form-control bg-light">
                                        @if($user->email_verified_at)
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                                Terverifikasi pada {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                            </span>
                                        @else
                                            <span class="text-warning">
                                                <i class="fas fa-clock"></i>
                                                Belum diverifikasi
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if(!$user->email_verified_at)
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-success" onclick="verifyEmail({{ $user->id }})">
                                            <i class="fas fa-envelope"></i> Verifikasi Email Sekarang
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-lock"></i> Keamanan Password</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="password" class="form-label">
                                                    Password
                                                    <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>
                                                </label>
                                                <div class="input-group-custom">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    <input type="password"
                                                           name="password"
                                                           id="password"
                                                           class="form-control @error('password') is-invalid @enderror"
                                                           placeholder="Masukkan password baru">
                                                    <button type="button" class="btn btn-outline-secondary toggle-password" onclick="togglePassword('password')">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </button>
                                                </div>
                                                <div class="password-strength mt-2" id="password-strength"></div>
                                                @error('password')
                                                    <div class="form-error">
                                                        <i class="fas fa-exclamation-circle"></i>
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                                <div class="input-group-custom">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    <input type="password"
                                                           name="password_confirmation"
                                                           id="password_confirmation"
                                                           class="form-control"
                                                           placeholder="Konfirmasi password baru">
                                                    <button type="button" class="btn btn-outline-secondary toggle-password" onclick="togglePassword('password_confirmation')">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-group mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('users.index') }}" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i>
                                Update User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form untuk email verification -->
<form id="verify-email-form" action="{{ route('users.verify-email', $user->id) }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Include Global CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/global-crud.css') }}">

<style>
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
    }

    .form-text {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .input-group-custom {
        display: flex;
        align-items: stretch;
    }

    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem 0 0 0.375rem;
        color: #6c757d;
    }

    .input-group-custom .form-control {
        border-radius: 0 0.375rem 0.375rem 0;
        flex: 1;
    }

    .toggle-password {
        border-radius: 0 0.375rem 0.375rem 0;
        cursor: pointer;
    }

    .form-error {
        color: #dc3545;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .form-error i {
        margin-right: 0.25rem;
    }

    .password-strength {
        font-size: 0.7rem;
    }

    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }
</style>

<script>
    // Toggle Password Visibility
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleButton = passwordInput.parentElement.querySelector('.toggle-password i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.classList.remove('fa-eye-slash');
            toggleButton.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleButton.classList.remove('fa-eye');
            toggleButton.classList.add('fa-eye-slash');
        }
    }

    // Validasi username realtime (hanya huruf kecil, angka, underscore)
    document.getElementById('username')?.addEventListener('input', function() {
        this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
    });

    // Validasi phone number (hanya angka dan +)
    document.getElementById('phone')?.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });

    // Password strength checker
    document.getElementById('password')?.addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('password-strength');

        if (password.length === 0) {
            strengthDiv.innerHTML = '';
            return;
        }

        let strength = '';
        let strengthClass = '';

        if (password.length < 6) {
            strength = '✗ Weak - Minimal 6 karakter';
            strengthClass = 'strength-weak';
        } else if (password.length >= 6 && password.length < 8) {
            strength = '⚠ Medium - Tambah karakter untuk lebih kuat';
            strengthClass = 'strength-medium';
        } else if (password.length >= 8) {
            if (/[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strength = '✓ Strong - Password kuat!';
                strengthClass = 'strength-strong';
            } else {
                strength = '⚠ Medium - Kombinasi huruf besar & angka lebih aman';
                strengthClass = 'strength-medium';
            }
        }

        strengthDiv.innerHTML = `<small class="${strengthClass}"><i class="fas fa-shield-alt"></i> ${strength}</small>`;
    });

    // Email verification function
    function verifyEmail(userId) {
        if (confirm('Verifikasi email user ini? Tindakan ini akan menandai email user sebagai terverifikasi.')) {
            document.getElementById('verify-email-form').submit();
        }
    }
</script>
@endsection
