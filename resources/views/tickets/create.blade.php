@extends('layouts.app')

@section('title', 'Buat Ticket Baru')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header dengan Container Biru --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-plus-circle me-2"></i>Buat Ticket Baru
                    </h1>
                    <p class="page-subtitle">Buat permintaan bantuan atau laporan masalah baru</p>
                </div>
                <div>
                    <a href="{{ route('tickets.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>Form Ticket Baru
            </h5>
            <p class="text-muted small mt-2 mb-0">Isi semua informasi yang diperlukan dengan lengkap</p>
        </div>
        <div class="card-body">
            <form action="{{ route('tickets.store') }}" method="POST" id="ticketForm">
                @csrf

                <div class="row">
                    <div class="col-md-12">
                        {{-- Judul --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-heading me-1"></i>Judul Ticket <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Masukkan judul ticket..." required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        {{-- Kategori --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-tag me-1"></i>Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin')
                    <div class="col-md-6">
                        {{-- Prioritas --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-flag me-1"></i>Prioritas
                            </label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ old('priority')=='low'?'selected':'' }}>🟢 Low (Rendah)</option>
                                <option value="medium" {{ old('priority')=='medium'?'selected':'' }}>🟡 Medium (Sedang)</option>
                                <option value="high" {{ old('priority')=='high'?'selected':'' }}>🟠 High (Tinggi)</option>
                                <option value="urgent" {{ old('priority')=='urgent'?'selected':'' }}>🔴 Urgent (Darurat)</option>
                            </select>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="row">
                    @if(auth()->user()->role === 'admin')
                    <div class="col-md-6">
                        {{-- Assigned To --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-user-check me-1"></i>Assign ke Staff IT
                            </label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Tidak ada (Unassigned) --</option>
                                @foreach($itStaff as $it)
                                    <option value="{{ $it->id }}" {{ old('assigned_to') == $it->id ? 'selected':'' }}>
                                        {{ $it->name }} ({{ $it->email }}) - {{ ucfirst($it->role) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Ticket akan langsung di-assign ke staff yang dipilih
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        {{-- Status --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-chart-line me-1"></i>Status Awal
                            </label>
                            <select name="status" class="form-select">
                                <option value="open" {{ old('status')=='open'?'selected':'' }}>📋 Open</option>
                                <option value="in_progress" {{ old('status')=='in_progress'?'selected':'' }}>⚙️ In Progress</option>
                                <option value="resolved" {{ old('status')=='resolved'?'selected':'' }}>✅ Resolved</option>
                                <option value="closed" {{ old('status')=='closed'?'selected':'' }}>📦 Closed</option>
                            </select>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Status awal ticket (default: Open)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        {{-- SLA Deadline --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-hourglass-half me-1"></i>SLA Deadline (Opsional)
                            </label>
                            <input type="datetime-local" name="sla_due_at"
                                   class="form-control @error('sla_due_at') is-invalid @enderror"
                                   value="{{ old('sla_due_at') }}">
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Tentukan batas waktu penyelesaian ticket. Ticket akan ditandai <span class="text-danger">OVERDUE</span> jika melewati deadline.
                            </div>
                            @error('sla_due_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        {{-- Resolved At (opsional) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-primary">
                                <i class="fas fa-calendar-check me-1"></i>Tanggal Resolved (Opsional)
                            </label>
                            <input type="datetime-local" name="resolved_at"
                                   class="form-control @error('resolved_at') is-invalid @enderror"
                                   value="{{ old('resolved_at') }}">
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Jika status dipilih "Resolved", isi tanggal penyelesaian
                            </div>
                            @error('resolved_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif

                {{-- Deskripsi --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-align-left me-1"></i>Deskripsi <span class="text-danger">*</span>
                    </label>
                    <textarea name="description" id="description" style="display:none;">{!! old('description') !!}</textarea>
                    <div id="description-editor" style="height:300px;">{!! old('description') !!}</div>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-muted mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Anda dapat menggunakan formatting teks (bold, italic, link, image) untuk menjelaskan masalah dengan lebih baik
                    </div>
                </div>

                {{-- Informasi Tambahan untuk User Biasa --}}
                @if(auth()->user()->role !== 'admin')
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Ticket akan diproses oleh tim IT. Prioritas dan status akan ditentukan oleh admin.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                {{-- Untuk user biasa, tambahkan estimasi SLA default --}}
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-hourglass-half me-2"></i>
                    <strong>Estimasi Waktu Respons:</strong>
                    <ul class="mb-0 mt-2">
                        <li>🟢 Low: 3 x 24 jam</li>
                        <li>🟡 Medium: 2 x 24 jam</li>
                        <li>🟠 High: 1 x 24 jam</li>
                        <li>🔴 Urgent: 4 jam</li>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Tombol Aksi --}}
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Simpan Ticket
                    </button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Card styling */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        background: white !important;
    }

    .card-header {
        background: white !important;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
    }

    /* Form styling */
    .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--tosca-primary, #18b5a0);
        box-shadow: 0 0 0 0.2rem rgba(24, 181, 160, 0.15);
    }

    /* Alert styling */
    .alert-info {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border: none;
        color: #0c4a6e;
        border-radius: 12px;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: none;
        color: #92400e;
        border-radius: 12px;
    }

    .alert-warning ul {
        padding-left: 1.25rem;
    }

    .alert-warning li {
        font-size: 0.875rem;
    }

    /* Button styling */
    .btn-primary {
        background: linear-gradient(135deg, var(--tosca-primary, #18b5a0), var(--tosca-dark, #0e8b7a));
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: var(--tosca-shadow, 0 4px 12px rgba(24, 181, 160, 0.3));
    }

    .btn-outline-secondary {
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-1px);
    }

    /* Quill editor customization */
    .ql-container {
        border-radius: 0 0 8px 8px !important;
        font-size: 0.875rem;
    }

    .ql-toolbar {
        border-radius: 8px 8px 0 0 !important;
        background: #f8f9fa;
    }

    .form-text {
        font-size: 0.75rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .btn-primary, .btn-outline-secondary {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .card-header {
            padding: 1rem;
        }

        .form-control, .form-select {
            padding: 0.5rem 0.75rem;
        }

        .ql-editor {
            font-size: 0.875rem;
        }
    }
</style>

@endsection

@push('scripts')
<!-- Quill CSS & JS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill editor
        const quill = new Quill('#description-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        const textarea = document.getElementById('description');
        const submitBtn = document.getElementById('submitBtn');

        // Set initial content
        if (textarea.value) {
            quill.root.innerHTML = textarea.value;
        }

        // Update textarea on change
        quill.on('text-change', function() {
            textarea.value = quill.root.innerHTML;
        });

        // Update textarea before submit with validation
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            textarea.value = quill.root.innerHTML;

            // Validasi deskripsi tidak boleh kosong
            const descriptionText = quill.getText().trim();
            if (descriptionText.length === 0) {
                e.preventDefault();
                alert('Deskripsi ticket tidak boleh kosong!');
                return false;
            }

            // Disable button untuk mencegah double submit
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            return true;
        });

        // Auto-dismiss alert after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        });

        // Set default SLA suggestion based on priority (untuk admin)
        const prioritySelect = document.querySelector('select[name="priority"]');
        const slaDueInput = document.querySelector('input[name="sla_due_at"]');

        if (prioritySelect && slaDueInput) {
            prioritySelect.addEventListener('change', function() {
                const priority = this.value;
                let hours = 0;

                switch(priority) {
                    case 'low':
                        hours = 72; // 3 hari
                        break;
                    case 'medium':
                        hours = 48; // 2 hari
                        break;
                    case 'high':
                        hours = 24; // 1 hari
                        break;
                    case 'urgent':
                        hours = 4; // 4 jam
                        break;
                    default:
                        hours = 0;
                }

                if (hours > 0 && !slaDueInput.value) {
                    const dueDate = new Date();
                    dueDate.setHours(dueDate.getHours() + hours);
                    slaDueInput.value = dueDate.toISOString().slice(0, 16);
                }
            });
        }
    });
</script>
@endpush
