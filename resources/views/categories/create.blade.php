@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Kategori
                    </h1>
                    <p class="page-subtitle">Buat kategori baru untuk ticket</p>
                </div>
                <div>
                    <a href="{{ route('categories.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>Form Kategori Baru
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        {{-- Nama Kategori --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-tag me-1"></i>Nama Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Hardware, Software, Jaringan, dll"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        {{-- Warna Kategori --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-palette me-1"></i>Warna Kategori
                            </label>
                            <div class="input-group">
                                <input type="color" name="color" id="colorPicker"
                                       class="form-control form-control-color @error('color') is-invalid @enderror"
                                       value="{{ old('color', '#0d6efd') }}"
                                       style="width: 60px; padding: 0.25rem;">
                                <input type="text" id="colorHex"
                                       class="form-control @error('color') is-invalid @enderror"
                                       value="{{ old('color', '#0d6efd') }}"
                                       placeholder="#0d6efd">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Pilih warna untuk badge kategori (opsional)
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-align-left me-1"></i>Deskripsi
                    </label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="4"
                              placeholder="Jelaskan tentang kategori ini...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Deskripsi opsional, dapat diisi nanti
                    </div>
                </div>

                {{-- Preview Warna --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-eye me-1"></i>Preview
                    </label>
                    <div>
                        <span id="previewBadge" class="badge px-3 py-2" style="background-color: #0d6efd; color: white;">
                            <i class="fas fa-tag me-1"></i> Contoh Kategori
                        </span>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Simpan Kategori
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                </div>
            </form>
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
        border-radius: 15px 15px 0 0 !important;
        padding: 1rem 1.5rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 0.6rem 1rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #18b5a0;
        box-shadow: 0 0 0 0.2rem rgba(24, 181, 160, 0.15);
    }
    .btn-primary {
        background: linear-gradient(135deg, #18b5a0, #0e8b7a);
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 181, 160, 0.3);
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('colorPicker');
    const colorHex = document.getElementById('colorHex');
    const previewBadge = document.getElementById('previewBadge');

    function updateColor(color) {
        previewBadge.style.backgroundColor = color;
        colorPicker.value = color;
        colorHex.value = color;
    }

    colorPicker.addEventListener('change', function() {
        updateColor(this.value);
    });

    colorHex.addEventListener('input', function() {
        let color = this.value;
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            updateColor(color);
        }
    });
});
</script>
@endpush
