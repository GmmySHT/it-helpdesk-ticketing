@extends('layouts.app')

@section('title', 'Semua Ticket')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tickets-index.css') }}">
@endpush

@php
    $activeTab = request('tab', 'active');
    $currentFilter = request('filter', 'my');
@endphp

@section('content')
<div class="container-fluid px-4">

    {{-- Header --}}
    <div class="ti-page-header mb-4">
        <div class="ti-page-header-inner">
            <div>
                <h1 class="ti-page-title">
                    <i class="fas fa-ticket-alt" aria-hidden="true"></i>Semua Ticket
                </h1>
                <p class="ti-page-subtitle">Kelola dan pantau semua permintaan bantuan IT</p>
            </div>
            <a href="{{ route('tickets.create') }}" class="ti-btn-header">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>Buat Ticket Baru
            </a>
        </div>
    </div>

    {{-- Info Alert untuk IT Staff --}}
    @if(in_array(auth()->user()->role, ['it_staff', 'it', 'admin']))
    <div class="ti-alert alert alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle" aria-hidden="true"></i>
        <div>
            <strong>Info pengurutan:</strong> Ticket diurutkan berdasarkan
            <strong>Prioritas (Urgent → Low) → Deadline SLA → Tanggal dibuat (terlama ke terbaru)</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
    @endif

    {{-- Filter Bar untuk IT Staff --}}
    @if(in_array(auth()->user()->role, ['it_staff', 'it']))
    <div class="ti-filter-card mb-3">
        <div class="btn-group w-100" role="group">
            <a href="{{ route('tickets.index', array_merge(request()->except('filter'), ['filter' => 'my'])) }}"
               class="btn {{ $currentFilter == 'my' || !$currentFilter ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-tasks"></i> Ticket Saya (Aktif)
            </a>
            <a href="{{ route('tickets.index', array_merge(request()->except('filter'), ['filter' => 'resolved'])) }}"
               class="btn {{ $currentFilter == 'resolved' ? 'btn-success' : 'btn-outline-success' }}">
                <i class="fas fa-check-circle"></i> Ticket Selesai
            </a>
            <a href="{{ route('tickets.index', array_merge(request()->except('filter'), ['filter' => 'all'])) }}"
               class="btn {{ $currentFilter == 'all' ? 'btn-info' : 'btn-outline-info' }}">
                <i class="fas fa-list"></i> Semua Ticket
            </a>
        </div>
    </div>
    @endif

    {{-- Filter Form --}}
    <div class="ti-filter-card">
        <form method="GET" id="filterForm">
            <div class="ti-filter-grid">
                <div>
                    <div class="ti-filter-label"><i class="fas fa-search"></i>Cari</div>
                    <div class="ti-input-wrap">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="ti-input" placeholder="Cari ticket / user / judul…">
                    </div>
                </div>
                <div>
                    <div class="ti-filter-label"><i class="fas fa-filter"></i>Status</div>
                    <select name="status" class="ti-select">
                        <option value="">Semua status</option>
                        @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->role === 'admin')
                <div>
                    <div class="ti-filter-label"><i class="fas fa-user-check"></i>Staff IT</div>
                    <select name="assigned_to" class="ti-select">
                        <option value="">Semua staff</option>
                        @foreach(\App\Models\User::whereIn('role', ['admin','it_staff','it'])->orderBy('name')->get() as $st)
                            <option value="{{ $st->id }}" {{ request('assigned_to') == $st->id ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <div class="ti-filter-label"><i class="fas fa-undo-alt"></i>Reopen</div>
                    <select name="has_reopen" class="ti-select">
                        <option value="">Semua</option>
                        <option value="yes" {{ request('has_reopen') == 'yes' ? 'selected' : '' }}>Pernah reopen</option>
                        <option value="no"  {{ request('has_reopen') == 'no'  ? 'selected' : '' }}>Belum reopen</option>
                    </select>
                </div>
                {{-- Simpan filter dan tab state --}}
                <input type="hidden" name="filter" value="{{ request('filter', 'my') }}">
                <input type="hidden" name="tab" id="tabInput" value="{{ $activeTab }}">
                <div style="padding-top:20px">
                    <button type="submit" class="ti-btn-filter">
                        <i class="fas fa-sliders-h" aria-hidden="true"></i>Filter
                    </button>
                </div>
                <div style="padding-top:20px">
                    <a href="{{ route('tickets.index', ['filter' => $currentFilter]) }}" class="ti-btn-reset">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Card --}}
    <div class="ti-card">

        {{-- Tabs --}}
        <div class="ti-tabs" role="tablist">
            <button class="ti-tab-btn {{ $activeTab == 'active' ? 'active' : '' }}"
                    role="tab" aria-selected="{{ $activeTab == 'active' ? 'true' : 'false' }}"
                    aria-controls="pane-active" data-tab="active" id="tab-active">
                <i class="fas fa-tasks" aria-hidden="true"></i>
                Aktif / Dalam Proses
                <span class="ti-tab-count">{{ $activeTicketsCount ?? 0 }}</span>
            </button>
            <button class="ti-tab-btn success {{ $activeTab == 'resolved' ? 'active' : '' }}"
                    role="tab" aria-selected="{{ $activeTab == 'resolved' ? 'true' : 'false' }}"
                    aria-controls="pane-resolved" data-tab="resolved" id="tab-resolved">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                Selesai / Ditutup
                <span class="ti-tab-count">{{ $resolvedTicketsCount ?? 0 }}</span>
            </button>
        </div>

        {{-- Sort info strip --}}
        <div class="ti-sort-strip">
            <i class="fas fa-sort-amount-up" aria-hidden="true"></i>
            Diurutkan:
            <strong>Prioritas (Urgent → Low) → Deadline SLA → Tanggal dibuat (terlama ke terbaru)</strong>
            <span class="ti-sort-count">
                {{ $tickets->total() }} tiket ditemukan
            </span>
        </div>

        {{-- TAB: AKTIF --}}
        <div id="pane-active" role="tabpanel" aria-labelledby="tab-active"
             style="{{ $activeTab !== 'active' ? 'display:none' : '' }}">
            <div class="ti-table-wrap">
                <table class="ti-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode ticket</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Ditugaskan ke</th>
                            <th>Pembuat</th>
                            <th>Tanggal dibuat</th>
                            <th>Deadline SLA</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeTickets as $ticket)
                        @php
                            $isOverdue = false;
                            $slaClass = 'ti-sla-none';
                            $slaTagHtml = '';

                            if ($ticket->sla_due_at) {
                                $due = \Carbon\Carbon::parse($ticket->sla_due_at);
                                $now = \Carbon\Carbon::now();
                                $isOverdue = $now->gt($due);

                                if ($isOverdue) {
                                    $slaClass = 'ti-sla-overdue';
                                    $slaTagHtml = '<div class="ti-sla-overdue-tag"><i class="fas fa-exclamation-triangle"></i>OVERDUE</div>';
                                } elseif ($now->diffInHours($due, false) <= 24) {
                                    $slaClass = 'ti-sla-soon';
                                    $slaTagHtml = '<div class="ti-sla-soon-tag"><i class="fas fa-clock"></i>Segera!</div>';
                                } else {
                                    $slaClass = 'ti-sla-ok';
                                }
                            }

                            $priorityBadge = match($ticket->priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'success',
                                default => 'secondary'
                            };
                        @endphp
                        <tr class="{{ $isOverdue ? 'ti-row-overdue' : '' }}">
                            <td><span class="ti-seq">{{ $loop->iteration }}</span></td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ti-ticket-link">
                                    {{ $ticket->ticket_number }}
                                </a>
                                @if($ticket->reopen_count > 0)
                                <div style="margin-top:3px">
                                    <span class="ti-badge ti-badge-reopen" style="font-size:.62rem">
                                        <i class="fas fa-undo-alt" style="font-size:9px" aria-hidden="true"></i>
                                        Reopen ×{{ $ticket->reopen_count }}
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td><div class="ti-title">{{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 45) }}</div></td>
                            <td><div class="ti-desc">{{ \Illuminate\Support\Str::words(strip_tags($ticket->description ?? '-'), 12, '…') }}</div></td>
                            <td><span class="ti-badge ti-badge-category">{{ $ticket->category->name ?? 'Umum' }}</span></td>
                            <td>
                                <span class="badge bg-{{ $priorityBadge }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="ti-badge ti-badge-{{ str_replace(' ','_',$ticket->status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->assignedTo)
                                    <div class="ti-person-cell">
                                        <div class="ti-avatar" aria-hidden="true">{{ strtoupper(substr($ticket->assignedTo->name, 0, 2)) }}</div>
                                        <span class="ti-person-name">{{ \Illuminate\Support\Str::limit($ticket->assignedTo->name, 12) }}</span>
                                    </div>
                                @else
                                    <span class="ti-person-empty"><i class="fas fa-user-slash" aria-hidden="true"></i> Belum</span>
                                @endif
                            </td>
                            <td>
                                <div class="ti-person-cell">
                                    <div class="ti-avatar ti-av-gray" aria-hidden="true">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 2)) }}</div>
                                    <span class="ti-person-name">{{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 12) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="ti-date">{{ $ticket->created_at->translatedFormat('d M Y') }}</div>
                                <div class="ti-date-ago">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($ticket->sla_due_at)
                                    <div class="{{ $slaClass }}">
                                        {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y H:i') }}
                                    </div>
                                    {!! $slaTagHtml !!}
                                @else
                                    <span class="ti-sla-none">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="ti-action-group">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="ti-btn-action ti-btn-view" title="Lihat detail">
                                        <i class="fas fa-eye fa-sm" aria-hidden="true"></i>
                                    </a>

                                    @can('assign', $ticket)
                                        <div class="ti-action-divider"></div>
                                        <button type="button" class="ti-btn-action ti-btn-assign open-assign-modal"
                                                data-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}"
                                                data-assigned-to="{{ $ticket->assigned_to ?? '' }}" data-sla-due="{{ $ticket->sla_due_at ?? '' }}"
                                                title="Assign staff">
                                            <i class="fas fa-user-plus fa-sm" aria-hidden="true"></i>
                                        </button>
                                    @endcan

                                    @can('updateStatus', $ticket)
                                        <div class="ti-action-divider"></div>
                                        <div class="dropdown">
                                            <button type="button" class="ti-btn-action ti-btn-status dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Ubah status">
                                                <i class="fas fa-exchange-alt fa-sm" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @foreach(['open','in_progress','closed'] as $s)
                                                    @if($ticket->status != $s)
                                                    <li>
                                                        <form action="{{ route('tickets.status', $ticket) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="{{ $s }}">
                                                            <button type="submit" class="dropdown-item small">
                                                                <i class="fas {{ $s=='open' ? 'fa-envelope' : ($s=='in_progress' ? 'fa-cogs' : 'fa-archive') }} me-2 text-muted" aria-hidden="true"></i>
                                                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @endif
                                                @endforeach
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item small open-resolve-modal"
                                                            data-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}"
                                                            data-ticket-title="{{ addslashes(strip_tags($ticket->title)) }}">
                                                        <i class="fas fa-check-circle text-success me-2" aria-hidden="true"></i>Resolved
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="ti-empty">
                                <i class="fas fa-tasks" aria-hidden="true"></i>
                                <p>Tidak ada ticket aktif</p>
                                @if(in_array(auth()->user()->role, ['it_staff', 'it']) && $currentFilter == 'my')
                                    <small class="text-muted">Gunakan filter "Semua Ticket" untuk melihat semua tiket</small>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB: SELESAI --}}
        <div id="pane-resolved" role="tabpanel" aria-labelledby="tab-resolved"
             style="{{ $activeTab !== 'resolved' ? 'display:none' : '' }}">
            <div class="ti-table-wrap">
                <table class="ti-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode ticket</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Ditugaskan ke</th>
                            <th>Pembuat</th>
                            <th>Tanggal dibuat</th>
                            <th>Tanggal selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resolvedTickets as $ticket)
                        @php
                            $priorityBadge = match($ticket->priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'success',
                                default => 'secondary'
                            };
                        @endphp
                        <tr>
                            <td><span class="ti-seq">{{ $loop->iteration }}</span></td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ti-ticket-link">
                                    {{ $ticket->ticket_number }}
                                </a>
                                @if($ticket->reopen_count > 0)
                                <div style="margin-top:3px">
                                    <span class="ti-badge ti-badge-reopen" style="font-size:.62rem">
                                        <i class="fas fa-undo-alt" style="font-size:9px" aria-hidden="true"></i>
                                        Reopen ×{{ $ticket->reopen_count }}
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td><div class="ti-title">{{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 38) }}</div></td>
                            <td><div class="ti-desc">{{ \Illuminate\Support\Str::words(strip_tags($ticket->description ?? '-'), 10, '…') }}</div></td>
                            <td><span class="ti-badge ti-badge-category">{{ $ticket->category->name ?? 'Umum' }}</span></td>
                            <td><span class="badge bg-{{ $priorityBadge }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td>
                                <span class="ti-badge ti-badge-{{ $ticket->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->assignedTo)
                                    <div class="ti-person-cell">
                                        <div class="ti-avatar" aria-hidden="true">{{ strtoupper(substr($ticket->assignedTo->name, 0, 2)) }}</div>
                                        <span class="ti-person-name">{{ \Illuminate\Support\Str::limit($ticket->assignedTo->name, 12) }}</span>
                                    </div>
                                @else
                                    <span class="ti-person-empty">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="ti-person-cell">
                                    <div class="ti-avatar ti-av-gray" aria-hidden="true">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 2)) }}</div>
                                    <span class="ti-person-name">{{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 12) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="ti-date">{{ $ticket->created_at->translatedFormat('d M Y') }}</div>
                                <div class="ti-date-ago">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($ticket->resolved_at)
                                    <div class="ti-date" style="color:#059669;font-weight:500">
                                        {{ \Carbon\Carbon::parse($ticket->resolved_at)->translatedFormat('d M Y H:i') }}
                                    </div>
                                @else
                                    <span class="ti-sla-none">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="ti-action-group">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="ti-btn-action ti-btn-view" title="Lihat detail">
                                        <i class="fas fa-eye fa-sm" aria-hidden="true"></i>
                                    </a>
                                    @can('reopen', $ticket)
                                        <div class="ti-action-divider"></div>
                                        <button type="button" class="ti-btn-action ti-btn-reopen open-reopen-modal"
                                                data-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}"
                                                data-title="{{ addslashes(strip_tags($ticket->title)) }}"
                                                data-resolution="{{ $ticket->resolution_notes ?? '' }}"
                                                title="Buka kembali">
                                            <i class="fas fa-undo-alt fa-sm" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="ti-empty">
                                <i class="fas fa-check-circle" aria-hidden="true"></i>
                                <p>Belum ada ticket yang selesai</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="ti-pagination-bar">
            <div class="ti-pagination-info">
                <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
                Menampilkan {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }}
                dari {{ $tickets->total() }} tiket
            </div>
            {{ $tickets->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
        </div>
        @endif

    </div>
</div>

{{-- ==================== MODALS ==================== --}}

{{-- Assign Modal --}}
<div class="modal fade ti-modal" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="assignForm" method="POST" action="">
            @csrf
            <div class="modal-content">
                <div class="modal-header ti-modal-primary">
                    <h5 class="modal-title text-white" id="assignModalLabel">
                        <i class="fas fa-user-plus me-2" aria-hidden="true"></i>
                        Assign Ticket <span id="modalTicketNumber" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="modalTicketId">
                    <div class="mb-3">
                        <label class="form-label" for="modalAssignedTo">Pilih Staff IT</label>
                        <select name="assigned_to" id="modalAssignedTo" class="form-select" required>
                            <option value="">— pilih staff —</option>
                            @foreach(\App\Models\User::whereIn('role', ['admin','it_staff','it'])->orderBy('name')->get() as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="modalPriority">Prioritas</label>
                        <select name="priority" id="modalPriority" class="form-select">
                            <option value="">Gunakan prioritas default</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="slaDueAt">SLA Deadline</label>
                        <input type="datetime-local" name="sla_due_at" id="slaDueAt" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="notify" id="modalNotify" value="1" checked>
                        <label class="form-check-label fw-normal" for="modalNotify">Kirim notifikasi ke assignee</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1" aria-hidden="true"></i>Assign
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Resolve Modal --}}
<div class="modal fade ti-modal" id="resolveModal" tabindex="-1" aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="resolveForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header ti-modal-success">
                    <h5 class="modal-title text-white" id="resolveModalLabel">
                        <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                        Selesaikan Ticket <span id="resolveTicketNumber" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="resolved">
                    <input type="hidden" name="ticket_id" id="resolveTicketId">
                    <div class="alert alert-info">
                        <i class="fas fa-ticket-alt me-2" aria-hidden="true"></i>
                        <strong>Ticket:</strong> <span id="resolveTicketTitle"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="resolution_notes">
                            Catatan solusi <span class="text-danger">*</span>
                        </label>
                        <textarea id="resolution_notes" name="resolution_notes"
                                  class="form-control" rows="5" required
                                  placeholder="Jelaskan langkah-langkah penyelesaian…"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="resolution_attachments">
                            Lampiran bukti (opsional)
                        </label>
                        <input id="resolution_attachments" type="file"
                               name="resolution_attachments[]" class="form-control"
                               multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Selesaikan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reopen Modal --}}
<div class="modal fade ti-modal" id="reopenModal" tabindex="-1" aria-labelledby="reopenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="reopenForm" method="POST" action="">
            @csrf
            <div class="modal-content">
                <div class="modal-header ti-modal-warning">
                    <h5 class="modal-title" id="reopenModalLabel">
                        <i class="fas fa-undo-alt me-2" aria-hidden="true"></i>
                        Buka Kembali Ticket <span id="reopenTicketNumber" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="reopenTicketId">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                        Ticket akan kembali berstatus <strong>OPEN</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reopen_reason">
                            Alasan dibuka kembali <span class="text-danger">*</span>
                        </label>
                        <textarea id="reopen_reason" name="reopen_reason"
                                  class="form-control" rows="4" required
                                  placeholder="Jelaskan mengapa ticket ini perlu dibuka kembali…"></textarea>
                    </div>
                    <div id="prevResContainer" style="display:none">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                            <strong>Solusi sebelumnya:</strong><br>
                            <span id="prevResText"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo-alt me-1" aria-hidden="true"></i>Ya, buka kembali
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    var tabInput = document.getElementById('tabInput');
    if (tabInput) {
        document.querySelectorAll('.ti-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = this.dataset.tab;
                document.querySelectorAll('.ti-tab-btn').forEach(function (b) {
                    b.classList.remove('active');
                    b.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                document.querySelectorAll('[id^="pane-"]').forEach(function (p) {
                    p.style.display = 'none';
                });
                var pane = document.getElementById('pane-' + tab);
                if (pane) pane.style.display = '';
                if (tabInput) tabInput.value = tab;

                // Submit form to refresh with new tab
                document.getElementById('filterForm')?.submit();
            });
        });
    }

    // Assign Modal
    var assignModalEl = document.getElementById('assignModal');
    if (assignModalEl) {
        var assignModal = new bootstrap.Modal(assignModalEl);
        document.querySelectorAll('.open-assign-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('modalTicketId').value = this.dataset.id;
                document.getElementById('modalTicketNumber').textContent = this.dataset.ticketNumber;
                var sel = document.getElementById('modalAssignedTo');
                if (sel) sel.value = this.dataset.assignedTo || '';
                var sla = document.getElementById('slaDueAt');
                if (sla) sla.value = this.dataset.slaDue || '';
                document.getElementById('assignForm').action = '/tickets/' + this.dataset.id + '/assign';
                assignModal.show();
            });
        });
    }

    // Resolve Modal
    var resolveModalEl = document.getElementById('resolveModal');
    if (resolveModalEl) {
        var resolveModal = new bootstrap.Modal(resolveModalEl);
        document.querySelectorAll('.open-resolve-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('resolveTicketId').value = this.dataset.id;
                document.getElementById('resolveTicketNumber').textContent = this.dataset.ticketNumber;
                document.getElementById('resolveTicketTitle').textContent = this.dataset.ticketTitle || '';
                document.getElementById('resolution_notes').value = '';
                document.getElementById('resolveForm').action = '/tickets/' + this.dataset.id + '/status';
                resolveModal.show();
            });
        });
    }

    // Reopen Modal
    var reopenModalEl = document.getElementById('reopenModal');
    if (reopenModalEl) {
        var reopenModal = new bootstrap.Modal(reopenModalEl);
        document.querySelectorAll('.open-reopen-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('reopenTicketId').value = this.dataset.id;
                document.getElementById('reopenTicketNumber').textContent = this.dataset.ticketNumber;
                document.getElementById('reopen_reason').value = '';
                document.getElementById('reopenForm').action = '/tickets/' + this.dataset.id + '/reopen';

                var resolution = (this.dataset.resolution || '').trim();
                var container = document.getElementById('prevResContainer');
                var text = document.getElementById('prevResText');
                if (resolution && container && text) {
                    text.textContent = resolution.substring(0, 250) + (resolution.length > 250 ? '…' : '');
                    container.style.display = 'block';
                } else if (container) {
                    container.style.display = 'none';
                }
                reopenModal.show();
            });
        });
    }
});
</script>
@endpush
