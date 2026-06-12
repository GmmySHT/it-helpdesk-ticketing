@extends('layouts.app')

@section('title', 'Tiket Saya - IT Staff')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tickets-index.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- ══════════════════════ PAGE HEADER ══════════════════════ --}}
    <div class="tk-page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="tk-page-title">
                    <i class="fas fa-tasks"></i>Tiket Saya
                </h1>
                <p class="tk-page-subtitle">
                    Daftar ticket yang ditugaskan kepada Anda (antrian dan sedang dikerjakan)
                </p>
            </div>
            <a href="{{ route('tickets.create') }}" class="tk-btn-header">
                <i class="fas fa-plus-circle"></i>Buat Ticket Baru
            </a>
        </div>
    </div>

    {{-- ══════════════════════ INFO PENGURUTAN ══════════════════════ --}}
    <div class="tk-alert alert alert-dismissible fade show" role="alert">
        <div class="tk-alert-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div>
            <strong>Informasi Pengurutan:</strong> Ticket diurutkan berdasarkan <strong>Prioritas</strong> (Urgent → High → Medium → Low),
            kemudian berdasarkan <strong>SLA Deadline</strong> terdekat, lalu berdasarkan <strong>waktu dibuat</strong>.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- ══════════════════════ FILTER AREA ══════════════════════ --}}
    <div class="tk-filter-card">
        <form method="GET" action="{{ route('it.tickets.my') }}" class="row g-3 align-items-end" id="filterForm">
            <div class="col-md-4">
                <label class="tk-filter-label">
                    <i class="fas fa-search"></i> Cari Ticket
                </label>
                <div class="tk-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="tk-input"
                           placeholder="Cari ticket number atau judul...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="tk-filter-label">
                    <i class="fas fa-filter"></i> Status
                </label>
                <select name="status" class="tk-select">
                    <option value="">Semua Status</option>
                    <option value="in_queue" {{ request('status') == 'in_queue' ? 'selected' : '' }}>Dalam Antrian</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Selesai</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="tk-filter-label">
                    <i class="fas fa-flag"></i> Prioritas
                </label>
                <select name="priority" class="tk-select">
                    <option value="">Semua Prioritas</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <input type="hidden" name="tab" id="tabInput" value="{{ request('tab', 'active') }}">
            <div class="col-auto">
                <button class="tk-btn-filter" type="submit">
                    <i class="fas fa-sliders-h"></i> Filter
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('it.tickets.my') }}" class="tk-btn-reset">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ══════════════════════ TABS + TABLE CARD ══════════════════════ --}}
    <div class="tk-table-card">

        {{-- Tabs --}}
        <div class="card-header" style="padding-bottom:0; border-bottom:none;">
            <ul class="tk-tabs" id="ticketTabs" role="tablist">
                <li class="tk-tab-item" role="presentation">
                    <button class="tk-tab-link {{ request('tab', 'active') == 'active' ? 'tk-tab-active' : '' }}" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-tickets" type="button" role="tab" data-tab="active">
                        <i class="fas fa-tasks"></i> Aktif / Dalam Proses
                        <span class="tk-tab-count">{{ $activeTicketsCount ?? 0 }}</span>
                    </button>
                </li>
                <li class="tk-tab-item" role="presentation">
                    <button class="tk-tab-link tk-tab-success {{ request('tab', 'active') == 'resolved' ? 'tk-tab-active' : '' }}" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved-tickets" type="button" role="tab" data-tab="resolved">
                        <i class="fas fa-check-circle"></i> Selesai / Ditutup
                        <span class="tk-tab-count">{{ $resolvedTicketsCount ?? 0 }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            {{-- ══ Tab 1: Aktif / Dalam Proses ══ --}}
            <div class="tab-pane fade {{ request('tab', 'active') == 'active' ? 'show active' : '' }}" id="active-tickets" role="tabpanel">
                @if(($activeTickets ?? collect())->count() > 0)
                <div class="tk-table-scroll">
                    <table class="tk-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ticket Number</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Pembuat</th>
                                <th>Dibuat</th>
                                <th>SLA Deadline</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeTickets as $index => $ticket)
                            @php
                                $isOverdue = $ticket->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at));
                                $priorityMap = ['low'=>'low','medium'=>'medium','high'=>'high','urgent'=>'urgent'];
                                $statusLabels = ['in_queue' => 'Dalam Antrian', 'in_progress' => 'Sedang Dikerjakan'];
                                $statusBadge = ['in_queue' => 'tk-badge-info-soft', 'in_progress' => 'tk-badge-warning-soft'];
                                $shortTitle = \Illuminate\Support\Str::limit(strip_tags($ticket->title), 35);
                                $shortUserName = \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 10);
                            @endphp
                            <tr @if($isOverdue) class="tk-row-overdue" @endif>
                                <td class="tk-id-cell">{{ ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('it.tickets.show', $ticket) }}" class="tk-ticket-number">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    @if($ticket->reopen_count > 0)
                                        <span class="tk-reopen-tag" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                                            <i class="fas fa-undo-alt"></i> {{ $ticket->reopen_count }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tk-ticket-title" style="max-width:200px" title="{{ strip_tags($ticket->title) }}">{{ $shortTitle }}</div>
                                </td>
                                <td>
                                    <span class="tk-badge tk-badge-category">
                                        <i class="fas fa-tag"></i> {{ $ticket->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="tk-badge tk-badge-priority-{{ $priorityMap[$ticket->priority] ?? 'medium' }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="tk-badge {{ $statusBadge[$ticket->status] ?? 'tk-badge-secondary-soft' }}">
                                        <i class="fas {{ $ticket->status == 'in_queue' ? 'fa-clock' : 'fa-cogs' }}"></i>
                                        {{ $statusLabels[$ticket->status] ?? ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="tk-person-cell">
                                        <div class="tk-avatar tk-avatar-muted">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</div>
                                        <span class="tk-person-name">{{ $shortUserName }}</span>
                                    </div>
                                </td>
                                <td class="tk-date-cell">
                                    {{ $ticket->created_at->format('d M Y') }}
                                </td>
                                <td class="tk-date-cell {{ $isOverdue ? 'tk-date-overdue' : '' }}">
                                    @if($ticket->sla_due_at)
                                        <div>
                                            <i class="fas fa-hourglass-half"></i>
                                            {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y H:i') }}
                                        </div>
                                        @if($isOverdue)
                                            <span class="tk-badge-overdue-tag">OVERDUE</span>
                                        @else
                                            @php $hoursLeft = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($ticket->sla_due_at), false); @endphp
                                            @if($hoursLeft <= 24 && $hoursLeft > 0)
                                                <span class="tk-badge-soon-tag">H-{{ $hoursLeft }} jam</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="tk-person-empty">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="tk-action-group">
                                        <a href="{{ route('it.tickets.show', $ticket) }}" class="tk-btn-icon tk-btn-view" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($ticket->status == 'in_queue')
                                            <button type="button" class="tk-btn-icon tk-btn-take take-ticket-btn" data-url="{{ route('it.tickets.take', $ticket) }}" data-ticket-number="{{ $ticket->ticket_number }}" title="Ambil Ticket">
                                                <i class="fas fa-hand-paper"></i>
                                            </button>
                                        @elseif($ticket->status == 'in_progress')
                                            <button type="button" class="tk-btn-icon tk-btn-resolve resolve-btn" data-ticket-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}" data-ticket-title="{{ addslashes($shortTitle) }}" title="Selesaikan Ticket">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="tk-empty-state">
                    <i class="fas fa-tasks"></i>
                    <p>Tidak ada ticket aktif</p>
                </div>
                @endif
            </div>

            {{-- ══ Tab 2: Selesai / Ditutup ══ --}}
            <div class="tab-pane fade {{ request('tab', 'active') == 'resolved' ? 'show active' : '' }}" id="resolved-tickets" role="tabpanel">
                @if(($resolvedTickets ?? collect())->count() > 0)
                <div class="tk-table-scroll">
                    <table class="tk-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ticket Number</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Pembuat</th>
                                <th>Dibuat</th>
                                <th>Tanggal Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resolvedTickets as $index => $ticket)
                            @php
                                $duration = '';
                                if ($ticket->created_at && $ticket->resolved_at) {
                                    $diffMinutes = $ticket->created_at->diffInMinutes($ticket->resolved_at);
                                    $hours = floor($diffMinutes / 60);
                                    $minutes = $diffMinutes % 60;
                                    if ($hours > 0 && $minutes > 0) $duration = $hours . ' jam ' . $minutes . ' menit';
                                    elseif ($hours > 0) $duration = $hours . ' jam';
                                    else $duration = $minutes . ' menit';
                                }
                                $priorityMap = ['low'=>'low','medium'=>'medium','high'=>'high','urgent'=>'urgent'];
                                $shortTitle = \Illuminate\Support\Str::limit(strip_tags($ticket->title), 35);
                                $shortUserName = \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 10);
                            @endphp
                            <tr>
                                <td class="tk-id-cell">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('it.tickets.show', $ticket) }}" class="tk-ticket-number">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    @if($ticket->reopen_count > 0)
                                        <span class="tk-reopen-tag" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                                            <i class="fas fa-undo-alt"></i> {{ $ticket->reopen_count }}x
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tk-ticket-title" style="max-width:200px" title="{{ strip_tags($ticket->title) }}">{{ $shortTitle }}</div>
                                    @if($duration)
                                        <div class="tk-duration-text"><i class="fas fa-hourglass-half"></i> {{ $duration }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="tk-badge tk-badge-category">
                                        <i class="fas fa-tag"></i> {{ $ticket->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="tk-badge tk-badge-priority-{{ $priorityMap[$ticket->priority] ?? 'medium' }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="tk-badge {{ $ticket->status == 'resolved' ? 'tk-badge-success-soft' : 'tk-badge-secondary-soft' }}">
                                        <i class="fas {{ $ticket->status == 'resolved' ? 'fa-check-circle' : 'fa-archive' }}"></i>
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="tk-person-cell">
                                        <div class="tk-avatar tk-avatar-muted">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</div>
                                        <span class="tk-person-name">{{ $shortUserName }}</span>
                                    </div>
                                </td>
                                <td class="tk-date-cell">
                                    {{ $ticket->created_at->format('d M Y') }}
                                </td>
                                <td class="tk-date-cell">
                                    @if($ticket->resolved_at)
                                        <span class="tk-resolved-date">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y H:i') }}</span>
                                    @else
                                        <span class="tk-person-empty">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="tk-action-group">
                                        <a href="{{ route('it.tickets.show', $ticket) }}" class="tk-btn-icon tk-btn-view" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="tk-btn-icon tk-btn-reopen open-reopen-modal" data-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}" data-title="{{ addslashes($shortTitle) }}" data-resolution="{{ $ticket->resolution_notes ?? '' }}" title="Buka Kembali Ticket">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="tk-empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Belum ada ticket yang diselesaikan</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ══ Pagination ══ --}}
        @if(isset($tickets) && $tickets->total() > 0)
        <div class="card-footer">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="tk-footer-info">
                    <i class="fas fa-info-circle"></i>
                    Menampilkan {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} dari {{ $tickets->total() }} tiket
                </div>
                <div>
                    {{ $tickets->appends(request()->except('page'))->links('vendor.pagination.tk-pagination') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════ RESOLVE MODAL ══════════════════════ --}}
<div class="modal fade tk-modal" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="resolveForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" value="resolved">
            <input type="hidden" name="ticket_id" id="ticketId" />
            <div class="modal-content">
                <div class="modal-header tk-modal-success">
                    <h5 class="modal-title">Selesaikan Ticket <span id="resolveTicketNumber"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="tk-modal-note tk-note-info">
                        <i class="fas fa-ticket-alt"></i>
                        <div><strong>Ticket:</strong> <span id="resolveTicketTitle"></span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Solusi <span class="text-danger">*</span></label>
                        <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran (Opsional)</label>
                        <input type="file" name="resolution_attachments[]" class="form-control" multiple>
                    </div>
                    <div class="tk-modal-note tk-note-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>Setelah ticket diselesaikan, status tidak dapat diubah kembali.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="tk-btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="tk-btn-modal-confirm tk-confirm-success">Selesaikan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════ REOPEN MODAL ══════════════════════ --}}
<div class="modal fade tk-modal" id="reopenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="reopenForm" method="POST" action="">
            @csrf
            <div class="modal-content">
                <div class="modal-header tk-modal-warning">
                    <h5 class="modal-title">Buka Kembali Ticket <span id="reopenTicketNumber"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="reopenTicketId" />
                    <div class="tk-modal-note tk-note-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>Ticket akan masuk ke antrian kembali.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Dibuka Kembali <span class="text-danger">*</span></label>
                        <textarea name="reopen_reason" id="reopen_reason" class="form-control" rows="4" required></textarea>
                    </div>
                    <div id="previousResolutionContainer" style="display: none;">
                        <div class="tk-modal-note tk-note-info">
                            <i class="fas fa-history"></i>
                            <div><strong>Solusi sebelumnya:</strong><br><span id="previousResolution"></span></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="tk-btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="tk-btn-modal-confirm tk-confirm-warning">Ya, Buka Kembali</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab persistence
    const tabs = document.querySelectorAll('#ticketTabs button');
    const tabInput = document.getElementById('tabInput');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            if (tabInput) tabInput.value = this.getAttribute('data-tab');
            document.getElementById('filterForm').submit();
        });
    });

    // Take Ticket
    document.querySelectorAll('.take-ticket-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Ambil Ticket?',
                html: `Ambil ticket <strong>${this.dataset.ticketNumber}</strong> dari antrian?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1d6fb8',
                confirmButtonText: 'Ya, Ambil',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    fetch(this.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                        .then(response => { if (response.redirected) window.location.href = response.url; else return response.json(); })
                        .then(() => { Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); })
                        .catch(() => Swal.fire({ icon: 'error', title: 'Gagal!' }));
                }
            });
        });
    });

    // Resolve Modal
    let resolveModal = new bootstrap.Modal(document.getElementById('resolveModal'));
    document.querySelectorAll('.resolve-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Selesaikan Ticket?',
                html: `Selesaikan ticket <strong>${this.dataset.ticketNumber}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('ticketId').value = this.dataset.ticketId;
                    document.getElementById('resolveTicketNumber').textContent = this.dataset.ticketNumber;
                    document.getElementById('resolveTicketTitle').textContent = this.dataset.ticketTitle;
                    document.getElementById('resolveForm').action = `/it/tickets/${this.dataset.ticketId}/status`;
                    resolveModal.show();
                }
            });
        });
    });

    // Reopen Modal
    let reopenModal = new bootstrap.Modal(document.getElementById('reopenModal'));
    document.querySelectorAll('.open-reopen-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            Swal.fire({
                title: 'Buka Kembali Ticket?',
                html: `Buka kembali ticket <strong>${this.dataset.ticketNumber}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Ya, Buka Kembali',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reopenTicketId').value = this.dataset.id;
                    document.getElementById('reopenTicketNumber').textContent = this.dataset.ticketNumber;
                    document.getElementById('reopen_reason').value = '';
                    let container = document.getElementById('previousResolutionContainer');
                    if (this.dataset.resolution && this.dataset.resolution.trim() !== '') {
                        document.getElementById('previousResolution').textContent = this.dataset.resolution;
                        container.style.display = 'block';
                    } else {
                        container.style.display = 'none';
                    }
                    document.getElementById('reopenForm').action = `/it/tickets/${this.dataset.id}/reopen`;
                    reopenModal.show();
                }
            });
        });
    });
});
</script>
@endpush
