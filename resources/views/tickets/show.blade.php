@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header dengan Container Biru -->
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h1 class="page-title mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>
                            Ticket #{{ $ticket->ticket_number }}
                        </h1>
                        <div>
                            <span class="badge bg-{{ $ticket->getPriorityBadgeAttribute() }} px-3 py-2">
                                <i class="fas {{ $ticket->priority == 'low' ? 'fa-arrow-down' : ($ticket->priority == 'medium' ? 'fa-minus' : ($ticket->priority == 'high' ? 'fa-arrow-up' : 'fa-exclamation-triangle')) }} me-1"></i>
                                {{ ucfirst($ticket->priority) }}
                            </span>
                            <span class="badge bg-{{ $ticket->getStatusBadgeAttribute() }} px-3 py-2 ms-2">
                                <i class="fas {{ $ticket->status == 'open' ? 'fa-envelope' : ($ticket->status == 'in_progress' ? 'fa-cogs' : ($ticket->status == 'resolved' ? 'fa-check-circle' : 'fa-archive')) }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            @if($ticket->reopen_count > 0)
                            <span class="badge bg-warning px-3 py-2 ms-2" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                                <i class="fas fa-undo-alt me-1"></i>
                                Reopen x{{ $ticket->reopen_count }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <p class="page-subtitle mb-0">{{ strip_tags($ticket->title) }}</p>
                </div>
                <div>
                    @php
                        $backRoute = auth()->user()->role === 'admin'
                            ? route('tickets.index')
                            : (request()->routeIs('it.tickets.show') ? route('it.tickets.my') : route('tickets.index'));
                    @endphp
                    <a href="{{ $backRoute }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Description Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle me-3">
                            <i class="fas fa-file-alt text-primary"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Deskripsi Ticket</h5>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Dibuat oleh: {{ $ticket->user->name }}
                                <i class="fas fa-calendar-alt ms-2 me-1"></i>
                                {{ $ticket->created_at->format('d M Y, H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="ticket-description p-3 bg-light rounded">
                        {!! $ticket->description !!}
                    </div>
                </div>
            </div>

            <!-- Resolution Section (jika ticket resolved) -->
            @if($ticket->status === 'resolved' && $ticket->resolution_notes)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success bg-opacity-10 border-success">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-success bg-opacity-20 rounded-circle me-3">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 text-success">Solusi Penyelesaian</h5>
                            <small class="text-muted">
                                <i class="fas fa-user-check me-1"></i>
                                Diselesaikan oleh: {{ $ticket->resolvedBy->name ?? $ticket->assignedTo->name ?? 'System' }}
                                <i class="fas fa-calendar-alt ms-2 me-1"></i>
                                {{ $ticket->resolved_at ? \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y, H:i') : '-' }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="resolution-notes p-3 bg-light rounded">
                        {!! $ticket->resolution_notes !!}
                    </div>

                    <!-- Lampiran Bukti -->
                    @if($ticket->resolution_attachments)
                        @php
                            $attachments = is_string($ticket->resolution_attachments)
                                ? json_decode($ticket->resolution_attachments, true)
                                : $ticket->resolution_attachments;
                        @endphp
                        @if(is_array($attachments) && count($attachments) > 0)
                            <hr>
                            <h6 class="mt-3">
                                <i class="fas fa-paperclip me-2 text-primary"></i>Lampiran Bukti
                            </h6>
                            <div class="row g-3 mt-1">
                                @foreach($attachments as $attachment)
                                <div class="col-md-4 col-sm-6">
                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="text-decoration-none">
                                        <div class="card attachment-card h-100">
                                            <div class="card-body text-center py-3">
                                                @if(str_contains($attachment['mime'], 'image'))
                                                    <i class="fas fa-image fa-3x text-primary mb-2"></i>
                                                @elseif(str_contains($attachment['mime'], 'pdf'))
                                                    <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                                @elseif(str_contains($attachment['mime'], 'word'))
                                                    <i class="fas fa-file-word fa-3x text-info mb-2"></i>
                                                @elseif(str_contains($attachment['mime'], 'excel'))
                                                    <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                                                @else
                                                    <i class="fas fa-file fa-3x text-secondary mb-2"></i>
                                                @endif
                                                <p class="mb-0 text-truncate small fw-semibold">{{ $attachment['name'] }}</p>
                                                <small class="text-muted">{{ round($attachment['size'] / 1024, 2) }} KB</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Sidebar -->
        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Ticket
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-tag me-2 text-muted"></i>Kategori
                            </div>
                            <div class="info-value">
                                <span class="badge" style="background-color: {{ $ticket->category->color ?? '#6c757d' }}">
                                    {{ $ticket->category->name ?? '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-barcode me-2 text-muted"></i>Ticket Number
                            </div>
                            <div class="info-value fw-bold">{{ $ticket->ticket_number }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-plus me-2 text-muted"></i>Tanggal Dibuat
                            </div>
                            <div class="info-value">{{ $ticket->created_at->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-check me-2 text-muted"></i>Terakhir Update
                            </div>
                            <div class="info-value">{{ $ticket->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-check me-2 text-muted"></i>Ditugaskan Ke
                            </div>
                            <div class="info-value">
                                @if($ticket->assignedTo)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary me-2">
                                            {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $ticket->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-user-slash me-1"></i>Belum ditugaskan
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($ticket->assigned_at)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-clock me-2 text-muted"></i>Ditugaskan Pada
                            </div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($ticket->assigned_at)->format('d M Y, H:i') }}</div>
                        </div>
                        @endif
                        @if($ticket->resolved_at)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-check-circle me-2 text-muted"></i>Diselesaikan
                            </div>
                            <div class="info-value text-success">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y, H:i') }}</div>
                        </div>
                        @endif
                        @if($ticket->sla_due_at)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-hourglass-half me-2 text-muted"></i>SLA Deadline
                            </div>
                            <div class="info-value @if(\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at)) && !in_array($ticket->status, ['resolved', 'closed'])) text-danger fw-bold @endif">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y, H:i') }}
                                @if(\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at)) && !in_array($ticket->status, ['resolved', 'closed']))
                                    <span class="badge bg-danger ms-2">OVERDUE!</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($ticket->reopen_count > 0)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-undo-alt me-2 text-muted"></i>Jumlah Reopen
                            </div>
                            <div class="info-value">
                                <span class="badge bg-warning">{{ $ticket->reopen_count }} kali</span>
                                @if($ticket->reopened_at)
                                    <div class="small text-muted mt-1">
                                        Terakhir: {{ \Carbon\Carbon::parse($ticket->reopened_at)->format('d M Y, H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('updateStatus', $ticket)
                            @if(!in_array($ticket->status, ['resolved', 'closed']))
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal">
                                    <i class="fas fa-check-circle me-2"></i>Selesaikan Ticket
                                </button>
                            @endif
                        @endcan

                        @can('update', $ticket)
                            @php
                                $editRoute = auth()->user()->role === 'admin'
                                    ? route('tickets.edit', $ticket)
                                    : '#';
                            @endphp
                            @if($editRoute !== '#')
                                <a href="{{ $editRoute }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Ticket
                                </a>
                            @endif
                        @endcan

                        @can('reopen', $ticket)
                            @if(in_array($ticket->status, ['resolved', 'closed']))
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#reopenModal">
                                    <i class="fas fa-undo-alt me-2"></i>Buka Kembali Ticket
                                </button>
                            @endif
                        @endcan

                        @can('take', $ticket)
                            @if(is_null($ticket->assigned_to) && in_array($ticket->status, ['open']))
                                @php
                                    $takeRoute = auth()->user()->role === 'admin'
                                        ? route('tickets.take', $ticket)
                                        : route('it.tickets.take', $ticket);
                                @endphp
                                <form action="{{ $takeRoute }}" method="POST" class="d-grid">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-hand-paper me-2"></i>Ambil Ticket Ini
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('delete', $ticket)
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-2"></i>Hapus Ticket
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Activity Timeline Card -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-history me-2 text-info"></i>
                        <h5 class="card-title mb-0">Riwayat Aktivitas</h5>
                    </div>
                    <small class="text-muted">Semua perubahan pada ticket ini</small>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="timeline">
                        @forelse($ticket->histories()->with('user')->latest()->get() as $history)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{
                                $history->action === 'created' ? 'primary' :
                                ($history->action === 'updated' ? 'info' :
                                ($history->action === 'assigned' ? 'warning' :
                                ($history->action === 'taken' ? 'success' :
                                ($history->action === 'status_changed' ? 'secondary' :
                                ($history->action === 'resolved' ? 'success' :
                                ($history->action === 'reopened' ? 'warning' : 'dark'))))))
                            }}">
                                @switch($history->action)
                                    @case('created') <i class="fas fa-plus"></i> @break
                                    @case('updated') <i class="fas fa-edit"></i> @break
                                    @case('assigned') <i class="fas fa-user-plus"></i> @break
                                    @case('taken') <i class="fas fa-hand-paper"></i> @break
                                    @case('status_changed') <i class="fas fa-sync-alt"></i> @break
                                    @case('resolved') <i class="fas fa-check-circle"></i> @break
                                    @case('reopened') <i class="fas fa-undo-alt"></i> @break
                                    @default <i class="fas fa-circle"></i>
                                @endswitch
                            </div>
                            <div class="timeline-content">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                            <div>
                                                <span class="fw-semibold">{{ $history->user->name ?? 'System' }}</span>
                                                <span class="text-muted mx-1">•</span>
                                                <span class="small text-muted">{{ $history->created_at->diffForHumans() }}</span>
                                            </div>
                                            <span class="badge bg-{{
                                                $history->action === 'created' ? 'primary' :
                                                ($history->action === 'updated' ? 'info' :
                                                ($history->action === 'assigned' ? 'warning' :
                                                ($history->action === 'taken' ? 'success' :
                                                ($history->action === 'status_changed' ? 'secondary' :
                                                ($history->action === 'resolved' ? 'success' :
                                                ($history->action === 'reopened' ? 'warning' : 'dark'))))))
                                            }} bg-opacity-10 text-{{
                                                $history->action === 'created' ? 'primary' :
                                                ($history->action === 'updated' ? 'info' :
                                                ($history->action === 'assigned' ? 'warning' :
                                                ($history->action === 'taken' ? 'success' :
                                                ($history->action === 'status_changed' ? 'secondary' :
                                                ($history->action === 'resolved' ? 'success' :
                                                ($history->action === 'reopened' ? 'warning' : 'dark'))))))
                                            }} px-2 py-1">
                                                {{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                            </span>
                                        </div>
                                        <p class="mb-0 mt-2 small">{{ $history->notes }}</p>
                                        @if($history->action === 'reopened' && $history->meta)
                                            @php $meta = json_decode($history->meta, true); @endphp
                                            @if(isset($meta['reason']))
                                                <div class="alert alert-warning alert-sm mt-2 mb-0 py-1 px-2 small">
                                                    <i class="fas fa-comment me-1"></i>
                                                    <strong>Alasan reopen:</strong> {{ $meta['reason'] }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">Belum ada riwayat aktivitas</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($ticket->histories->count() > 5)
                <div class="card-footer bg-white">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Menampilkan {{ $ticket->histories->count() }} aktivitas
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL RESOLVE ==================== --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Selesaikan Ticket #{{ $ticket->ticket_number }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            @php
                $statusRoute = auth()->user()->role === 'admin'
                    ? route('tickets.status', $ticket)
                    : route('it.tickets.status', $ticket);
            @endphp
            <form action="{{ $statusRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" value="resolved">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Ticket:</strong> {{ $ticket->ticket_number }} - {{ strip_tags($ticket->title) }}
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment-dots me-1 text-danger"></i>
                            Deskripsi Penyelesaian <span class="text-danger">*</span>
                        </label>
                        <textarea name="resolution_notes" class="form-control" rows="5" required
                                  placeholder="Jelaskan langkah-langkah penyelesaian, solusi yang diterapkan, dan hasil akhir..."></textarea>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1 text-warning"></i>
                            Berikan penjelasan yang detail agar user memahami solusi yang diberikan
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-paperclip me-1"></i>
                            Lampiran Bukti (Opsional)
                        </label>
                        <input type="file" name="resolution_attachments[]" class="form-control" multiple
                               accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Upload gambar, PDF, atau dokumen sebagai bukti penyelesaian (maks 5MB per file)
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Setelah ticket diselesaikan, status tidak dapat diubah kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i>Konfirmasi Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODAL REOPEN ==================== --}}
<div class="modal fade" id="reopenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        @php
            $reopenRoute = auth()->user()->role === 'admin'
                ? route('tickets.reopen', $ticket)
                : route('it.tickets.reopen', $ticket);
        @endphp
        <form action="{{ $reopenRoute }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-undo-alt me-2"></i>Buka Kembali Ticket #{{ $ticket->ticket_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Anda akan membuka kembali ticket yang sudah selesai. Ticket akan berstatus <strong>OPEN</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-comment me-1"></i>Alasan Dibuka Kembali <span class="text-danger">*</span>
                        </label>
                        <textarea name="reopen_reason"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="Jelaskan mengapa ticket ini perlu dibuka kembali..."></textarea>
                        <div class="form-text">
                            Alasan ini akan dicatat dalam history ticket.
                        </div>
                    </div>

                    @if($ticket->resolution_notes)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Solusi sebelumnya:</strong><br>
                        {{ \Illuminate\Support\Str::limit(strip_tags($ticket->resolution_notes), 200) }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo-alt me-1"></i> Ya, Buka Kembali
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL DELETE ==================== --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

@endsection

@push('styles')
<style>
    /* Avatar Styles */
    .avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
    }

    .avatar-lg {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    /* Info List Styles */
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.875rem;
        color: #1f2937;
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 35px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 17px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #e5e7eb, #d1d5db, #e5e7eb);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1rem;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 1;
        font-size: 0.75rem;
    }

    .timeline-content {
        margin-left: 15px;
    }

    /* Attachment Card */
    .attachment-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .attachment-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: #0d6efd;
    }

    /* Description Styles */
    .ticket-description {
        line-height: 1.6;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .resolution-notes {
        line-height: 1.6;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* Card Header */
    .card-header {
        background: white !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 1rem 1.25rem;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .timeline {
            padding-left: 30px;
        }

        .timeline-marker {
            width: 28px;
            height: 28px;
            left: -30px;
            font-size: 0.7rem;
        }

        .timeline-content {
            margin-left: 10px;
        }

        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }

    /* Scrollbar untuk timeline */
    .card-body::-webkit-scrollbar {
        width: 4px;
    }

    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .card-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .card-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush
