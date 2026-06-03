@extends('layouts.app')

@section('title', 'Edit Ticket #' . $ticket->ticket_number)

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Ticket #{{ $ticket->ticket_number }}
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Ticket *</label>
                            <input type="text" name="title" value="{{ old('title', $ticket->title) }}"
                                   class="form-control @error('title') is-invalid @enderror" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori *</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (old('category_id', $ticket->category_id) == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi *</label>
                    <textarea name="description" id="description" style="display:none;">
                        {!! old('description', $ticket->description) !!}
                    </textarea>
                    <div id="description-editor" style="height:300px; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                        {!! old('description', $ticket->description) !!}
                    </div>
                    @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @if(auth()->user()->role === 'admin')
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Tidak ada --</option>
                                @foreach($itStaff as $it)
                                    <option value="{{ $it->id }}"
                                        {{ (old('assigned_to', $ticket->assigned_to) == $it->id) ? 'selected' : '' }}>
                                        {{ $it->name }} ({{ ucfirst($it->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>
                                    ⚪ Rendah
                                </option>
                                <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>
                                    🟡 Sedang
                                </option>
                                <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>
                                    🔴 Tinggi
                                </option>
                                <option value="urgent" {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>
                                    ⚫ Darurat
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>
                                    🔵 Open
                                </option>
                                <option value="in_progress" {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>
                                    🟠 In Progress
                                </option>
                                <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>
                                    🟢 Resolved
                                </option>
                                <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>
                                    ⚫ Closed
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">SLA Deadline</label>
                            <input type="datetime-local" name="sla_due_at"
                                   value="{{ old('sla_due_at', $ticket->sla_due_at ? \Carbon\Carbon::parse($ticket->sla_due_at)->format('Y-m-d\TH:i') : '') }}"
                                   class="form-control">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Tentukan deadline penyelesaian ticket
                            </div>
                        </div>
                    </div>
                </div>

                @if($ticket->status === 'resolved' && $ticket->resolved_at)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Resolved</label>
                            <input type="datetime-local" name="resolved_at"
                                   value="{{ old('resolved_at', $ticket->resolved_at->format('Y-m-d\TH:i')) }}"
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Info Reopen</label>
                            <div class="form-control bg-light">
                                @if($ticket->reopen_count > 0)
                                    <i class="fas fa-undo-alt text-warning me-1"></i>
                                    Dibuka kembali {{ $ticket->reopen_count }} kali
                                    @if($ticket->reopened_at)
                                        <br>
                                        <small class="text-muted">
                                            Terakhir: {{ \Carbon\Carbon::parse($ticket->reopened_at)->format('d M Y H:i') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Belum pernah dibuka kembali
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($ticket->resolution_notes)
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Solusi Sebelumnya</label>
                    <div class="alert alert-info">
                        <i class="fas fa-sticky-note me-2"></i>
                        {{ \Illuminate\Support\Str::limit($ticket->resolution_notes, 300) }}
                        @if(strlen($ticket->resolution_notes) > 300)
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#fullResolution">
                                Lihat selengkapnya
                            </button>
                            <div id="fullResolution" class="collapse mt-2">
                                {{ $ticket->resolution_notes }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif
                @endif

                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Ticket
                        </button>
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </a>
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <button type="button"
                                class="btn btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i>Hapus Ticket
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
@if(auth()->user()->role === 'admin')
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus ticket <strong>#{{ $ticket->ticket_number }}</strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Tindakan ini tidak dapat dibatalkan!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        border: 1px solid #dee2e6;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }
    .ql-container.ql-snow {
        border: 1px solid #dee2e6;
        border-top: none;
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    .ql-editor {
        min-height: 200px;
        font-size: 1rem;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Inisialisasi Quill Editor untuk deskripsi
    const quill = new Quill('#description-editor', {
        theme: 'snow',
        placeholder: 'Tulis deskripsi ticket di sini...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Sinkronisasi konten Quill ke textarea
    const textarea = document.getElementById('description');
    quill.on('text-change', function() {
        textarea.value = quill.root.innerHTML;
    });

    // Pastikan konten tersinkron saat submit
    document.querySelector('form').addEventListener('submit', function() {
        textarea.value = quill.root.innerHTML;
    });
</script>
@endpush
