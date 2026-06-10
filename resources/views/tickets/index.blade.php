@extends('layouts.app')

@section('title','Semua Ticket')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tickets-index.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-ticket-alt me-2"></i>Semua Ticket
                    </h1>
                    <p class="page-subtitle">Kelola dan pantau semua permintaan bantuan IT</p>
                </div>
                <div>
                    <a href="{{ route('tickets.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-2"></i>Buat Ticket Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Pengurutan (IT Staff) --}}
    @if(in_array(auth()->user()->role, ['it_staff', 'it']))
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Informasi Pengurutan:</strong> Ticket diurutkan berdasarkan <strong>Prioritas</strong>
        (Urgent → High → Medium → Low), kemudian <strong>SLA Deadline</strong> terdekat,
        lalu <strong>waktu dibuat</strong>.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filter --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body mt-4">
            <form method="GET" class="row g-3 align-items-end" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-search me-1"></i> Cari
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control border-start-0 ps-0"
                               placeholder="Cari ticket / user / title">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-filter me-1"></i> Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-user-check me-1"></i> Staff IT
                    </label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Semua Staff</option>
                        @foreach(\App\Models\User::whereIn('role', ['admin','it_staff','it'])->get() as $st)
                            <option value="{{ $st->id }}" {{ request('assigned_to') == $st->id ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-undo-alt me-1"></i> Reopen
                    </label>
                    <select name="has_reopen" class="form-select">
                        <option value="">Semua</option>
                        <option value="yes" {{ request('has_reopen') == 'yes' ? 'selected' : '' }}>Pernah Reopen</option>
                        <option value="no"  {{ request('has_reopen') == 'no'  ? 'selected' : '' }}>Belum Pernah Reopen</option>
                    </select>
                </div>
                <input type="hidden" name="tab" id="tabInput" value="{{ request('tab', 'active') }}">
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-sliders-h me-1"></i> Filter
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <ul class="nav nav-tabs card-header-tabs" id="ticketTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab', 'active') == 'active' ? 'active' : '' }}"
                            id="active-tab" data-bs-toggle="tab" data-bs-target="#active-tickets"
                            type="button" role="tab" data-tab="active">
                        <i class="fas fa-tasks me-1"></i> Aktif / Dalam Proses
                        <span class="badge bg-primary ms-1">
                            {{ $activeTicketsCount ?? $tickets->whereNotIn('status', ['resolved','closed'])->count() }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab', 'active') == 'resolved' ? 'active' : '' }}"
                            id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved-tickets"
                            type="button" role="tab" data-tab="resolved">
                        <i class="fas fa-check-circle me-1"></i> Selesai / Ditutup
                        <span class="badge bg-success ms-1">
                            {{ $resolvedTicketsCount ?? $tickets->whereIn('status', ['resolved','closed'])->count() }}
                        </span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content">

                {{-- ============================================================
                     TAB 1 — Aktif / Dalam Proses
                     ============================================================ --}}
                <div class="tab-pane fade {{ request('tab', 'active') == 'active' ? 'show active' : '' }}"
                     id="active-tickets" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3 py-2" width="50">#</th>
                                    <th class="py-2" width="140">Kode Ticket</th>
                                    <th class="py-2" width="220">Judul</th>
                                    <th class="py-2">Deskripsi</th>
                                    <th class="py-2" width="100">Kategori</th>
                                    <th class="py-2" width="100">Prioritas</th>
                                    <th class="py-2" width="100">Status</th>
                                    <th class="py-2" width="120">Ditugaskan Ke</th>
                                    <th class="py-2" width="120">Pembuat</th>
                                    <th class="py-2" width="100">Tanggal Dibuat</th>
                                    <th class="py-2" width="130">Deadline SLA</th>
                                    <th class="text-center py-2" width="110">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeTickets ?? $tickets->whereNotIn('status', ['resolved','closed']) as $ticket)
                                @php
                                    $isOverdue   = false;
                                    $deadlineClass = '';
                                    $deadlineText  = '';

                                    if ($ticket->sla_due_at && !in_array($ticket->status, ['resolved','closed'])) {
                                        $dueDate  = \Carbon\Carbon::parse($ticket->sla_due_at);
                                        $now      = \Carbon\Carbon::now();
                                        $isOverdue = $now->gt($dueDate);

                                        if ($isOverdue) {
                                            $deadlineClass = 'text-danger fw-bold';
                                            $deadlineText  = 'OVERDUE!';
                                        } elseif ($now->diffInHours($dueDate, false) <= 24) {
                                            $deadlineClass = 'text-warning';
                                            $deadlineText  = 'Segera!';
                                        }
                                    }

                                    $priorityLabels = [
                                        'low'    => '🟢 Low',
                                        'medium' => '🟡 Medium',
                                        'high'   => '🟠 High',
                                        'urgent' => '🔴 Urgent',
                                    ];
                                    $priorityColors = [
                                        'low'    => 'success',
                                        'medium' => 'warning',
                                        'high'   => 'danger',
                                        'urgent' => 'danger',
                                    ];
                                    $statusColors = [
                                        'open'        => 'primary',
                                        'in_progress' => 'warning',
                                        'resolved'    => 'success',
                                        'closed'      => 'secondary',
                                    ];
                                    $pColor = $priorityColors[$ticket->priority] ?? 'primary';
                                    $sColor = $statusColors[$ticket->status]    ?? 'primary';
                                @endphp
                                <tr @if($isOverdue) class="table-danger" @endif>
                                    <td class="ps-3 fw-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                        class="text-decoration-none fw-semibold text-primary">
                                            {{ $ticket->ticket_number }}
                                        </a>

                                        @if($ticket->reopen_count > 0)
                                            <div class="mt-1">
                                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                                    <i class="fas fa-undo-alt me-1"></i>
                                                    Reopen x{{ $ticket->reopen_count }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            {{ \Illuminate\Support\Str::words(strip_tags($ticket->description ?? '-'), 15, '...') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                            <i class="fas fa-tag me-1"></i>{{ $ticket->category->name ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $pColor }} bg-opacity-10 text-{{ $pColor }} px-2 py-1">
                                            {{ $priorityLabels[$ticket->priority] ?? ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sColor }} bg-opacity-10 text-{{ $sColor }} px-2 py-1">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->assignedTo)
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="avatar-sm bg-primary">
                                                    {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                                                </div>
                                                <span class="small">
                                                    {{ \Illuminate\Support\Str::limit($ticket->assignedTo->name, 12) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fas fa-user-slash me-1"></i>Belum
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="avatar-sm bg-secondary">
                                                {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="small">
                                                {{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 12) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="small">{{ $ticket->created_at->format('d M Y') }}</div>
                                        <div class="small text-muted">{{ $ticket->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($ticket->sla_due_at)
                                            <div class="small {{ $deadlineClass }}">
                                                {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y H:i') }}
                                            </div>
                                            @if($deadlineText)
                                                <div class="small {{ $deadlineClass }}">{{ $deadlineText }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>

                                    {{-- ── ACTION BUTTONS ── --}}
                                    <td>
                                        <div class="action-group">
                                            {{-- Lihat Detail --}}
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                               class="btn-action btn-action-view"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye fa-sm"></i>
                                            </a>

                                            @can('assign', $ticket)
                                                <div class="action-divider"></div>
                                                {{-- Assign Staff --}}
                                                <button type="button"
                                                        class="btn-action btn-action-assign open-assign-modal"
                                                        data-id="{{ $ticket->id }}"
                                                        data-ticket-number="{{ $ticket->ticket_number }}"
                                                        data-assigned-to="{{ $ticket->assigned_to ?? '' }}"
                                                        data-sla-due="{{ $ticket->sla_due_at ?? '' }}"
                                                        title="Assign Staff">
                                                    <i class="fas fa-user-plus fa-sm"></i>
                                                </button>
                                            @endcan

                                            @can('updateStatus', $ticket)
                                                <div class="action-divider"></div>
                                                {{-- Ubah Status (dropdown) --}}
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn-action btn-action-status dropdown-toggle"
                                                            data-bs-toggle="dropdown"
                                                            aria-expanded="false"
                                                            title="Ubah Status">
                                                        <i class="fas fa-exchange-alt fa-sm"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        @foreach(['open','in_progress','closed'] as $s)
                                                            @if($ticket->status != $s)
                                                            <li>
                                                                <form action="{{ route('tickets.status', $ticket) }}"
                                                                      method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="status" value="{{ $s }}">
                                                                    <button type="submit" class="dropdown-item py-1 small">
                                                                        <i class="fas
                                                                            {{ $s == 'open'        ? 'fa-envelope'  :
                                                                               ($s == 'in_progress' ? 'fa-cogs'      :
                                                                                                      'fa-archive') }}
                                                                            me-2 text-muted"></i>
                                                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                        @endforeach
                                                        <li><hr class="dropdown-divider my-1"></li>
                                                        <li>
                                                            {{-- Resolve (buka modal) --}}
                                                            <button type="button"
                                                                    class="dropdown-item py-1 small open-resolve-modal"
                                                                    data-id="{{ $ticket->id }}"
                                                                    data-ticket-number="{{ $ticket->ticket_number }}"
                                                                    data-ticket-title="{{ addslashes(strip_tags($ticket->title)) }}">
                                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                                Resolved
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
                                    <td colspan="10" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-tasks fa-3x mb-3 text-muted d-block"></i>
                                            <p class="text-muted mb-0">Tidak ada ticket aktif</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============================================================
                     TAB 2 — Selesai / Ditutup
                     ============================================================ --}}
                <div class="tab-pane fade {{ request('tab', 'active') == 'resolved' ? 'show active' : '' }}"
                     id="resolved-tickets" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3 py-2" width="50">#</th>
                                    <th class="py-2" width="140">Kode Ticket</th>
                                    <th class="py-2" width="150">Judul</th>
                                    <th class="py-2">Deskripsi</th>
                                    <th class="py-2" width="100">Kategori</th>
                                    <th class="py-2" width="100">Prioritas</th>
                                    <th class="py-2" width="100">Status</th>
                                    <th class="py-2" width="120">Ditugaskan Ke</th>
                                    <th class="py-2" width="120">Pembuat</th>
                                    <th class="py-2" width="100">Tanggal Dibuat</th>
                                    <th class="py-2" width="130">Tanggal Selesai</th>
                                    <th class="text-center py-2" width="80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resolvedTickets ?? $tickets->whereIn('status', ['resolved','closed']) as $ticket)
                                @php
                                    $priorityColors = [
                                        'low'    => 'success',
                                        'medium' => 'warning',
                                        'high'   => 'danger',
                                        'urgent' => 'danger',
                                    ];
                                    $statusColors = [
                                        'resolved' => 'success',
                                        'closed'   => 'secondary',
                                    ];
                                    $pColor = $priorityColors[$ticket->priority] ?? 'primary';
                                    $sColor = $statusColors[$ticket->status]    ?? 'success';
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $loop->iteration }}</td>
                                    {{-- KODE TICKET --}}
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                        class="text-decoration-none fw-semibold text-primary">
                                            {{ $ticket->ticket_number }}
                                        </a>

                                        @if($ticket->reopen_count > 0)
                                            <div class="mt-1">
                                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                                    <i class="fas fa-undo-alt me-1"></i>
                                                    Reopen x{{ $ticket->reopen_count }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- JUDUL --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 25) }}
                                        </div>
                                    </td>

                                    {{-- DESKRIPSI --}}
                                    <td>
                                        <div class="small text-muted">
                                            {{ \Illuminate\Support\Str::words(strip_tags($ticket->description ?? '-'), 15, '...') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                            <i class="fas fa-tag me-1"></i>{{ $ticket->category->name ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $pColor }} bg-opacity-10 text-{{ $pColor }} px-2 py-1">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sColor }} bg-opacity-10 text-{{ $sColor }} px-2 py-1">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->assignedTo)
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="avatar-sm bg-primary">
                                                    {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                                                </div>
                                                <span class="small">
                                                    {{ \Illuminate\Support\Str::limit($ticket->assignedTo->name, 12) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="avatar-sm bg-secondary">
                                                {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="small">
                                                {{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 12) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="small">{{ $ticket->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($ticket->resolved_at)
                                            <div class="small text-success">
                                                {{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>

                                    {{-- ── ACTION BUTTONS ── --}}
                                    <td>
                                        <div class="action-group">
                                            {{-- Lihat Detail --}}
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                               class="btn-action btn-action-view"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye fa-sm"></i>
                                            </a>

                                            @can('reopen', $ticket)
                                                <div class="action-divider"></div>
                                                {{-- Reopen --}}
                                                <button type="button"
                                                        class="btn-action btn-action-reopen open-reopen-modal"
                                                        data-id="{{ $ticket->id }}"
                                                        data-ticket-number="{{ $ticket->ticket_number }}"
                                                        data-title="{{ addslashes(strip_tags($ticket->title)) }}"
                                                        data-resolution="{{ $ticket->resolution_notes ?? '' }}"
                                                        title="Buka Kembali Ticket">
                                                    <i class="fas fa-undo-alt fa-sm"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-check-circle fa-3x mb-3 text-muted d-block"></i>
                                            <p class="text-muted mb-0">Belum ada ticket yang selesai</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- /tab-content --}}
        </div>{{-- /card-body --}}

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="card-footer bg-white border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Menampilkan {{ $tickets->firstItem() ?? 0 }} – {{ $tickets->lastItem() ?? 0 }}
                    dari {{ $tickets->total() }} tiket
                </div>
                <div>
                    {{ $tickets->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /card --}}
</div>{{-- /container-fluid --}}

{{-- ================================================================
     MODALS
     ================================================================ --}}

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="assignForm" method="POST" action="">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            Assign Ticket <span id="modalTicketNumber" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="ticket_id" id="modalTicketId">
          <div class="mb-3">
            <label class="form-label">Pilih Staff IT</label>
            <select name="assigned_to" id="modalAssignedTo" class="form-select" required>
              <option value="">-- pilih staff --</option>
              @foreach(\App\Models\User::whereIn('role', ['admin','it_staff','it'])->get() as $staff)
                <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Prioritas</label>
            <select name="priority" class="form-select">
              <option value="">Gunakan prioritas default</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">SLA Deadline</label>
            <input type="datetime-local" name="sla_due_at" id="slaDueAt" class="form-control">
          </div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" name="notify" id="modalNotify" value="1" checked>
            <label class="form-check-label">Kirim notifikasi ke assignee</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-primary" type="submit">Assign</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Resolve Modal --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="resolveForm" method="POST" action="" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            Selesaikan Ticket <span id="resolveTicketNumber" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="status" value="resolved">
          <input type="hidden" name="ticket_id" id="resolveTicketId">
          <div class="alert alert-info">
            <strong>Ticket:</strong> <span id="resolveTicketTitle"></span>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">
              Catatan Solusi <span class="text-danger">*</span>
            </label>
            <textarea name="resolution_notes" id="resolution_notes"
                      class="form-control" rows="5" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Lampiran Solusi (Opsional)</label>
            <input type="file" name="resolution_attachments[]" class="form-control"
                   multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-success" type="submit">Selesaikan</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Reopen Modal --}}
<div class="modal fade" id="reopenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="reopenForm" method="POST" action="">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">
            Buka Kembali Ticket <span id="reopenTicketNumber" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="ticket_id" id="reopenTicketId">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Anda akan membuka kembali ticket yang sudah selesai.
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">
              Alasan Dibuka Kembali <span class="text-danger">*</span>
            </label>
            <textarea name="reopen_reason" id="reopen_reason"
                      class="form-control" rows="4" required></textarea>
          </div>
          <div id="previousResolutionContainer" style="display: none;">
            <div class="alert alert-info">
              <strong>Solusi sebelumnya:</strong><br>
              <span id="previousResolution"></span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-warning" type="submit">Ya, Buka Kembali</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Tab persistence ── */
    const tabInput = document.getElementById('tabInput');
    document.querySelectorAll('#ticketTabs button').forEach(tab => {
        tab.addEventListener('click', function () {
            if (tabInput) tabInput.value = this.dataset.tab;
            document.getElementById('filterForm').submit();
        });
    });

    /* ── Assign Modal ── */
    const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
    document.querySelectorAll('.open-assign-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTicketId').value     = this.dataset.id;
            document.getElementById('modalTicketNumber').textContent = this.dataset.ticketNumber;
            document.getElementById('modalAssignedTo').value  = this.dataset.assignedTo || '';
            document.getElementById('slaDueAt').value         = this.dataset.slaDue || '';
            document.getElementById('assignForm').action      = '/tickets/' + this.dataset.id + '/assign';
            assignModal.show();
        });
    });

    /* ── Resolve Modal ── */
    const resolveModal = new bootstrap.Modal(document.getElementById('resolveModal'));
    document.querySelectorAll('.open-resolve-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('resolveTicketId').value           = this.dataset.id;
            document.getElementById('resolveTicketNumber').textContent = this.dataset.ticketNumber;
            document.getElementById('resolveTicketTitle').textContent  = this.dataset.ticketTitle || '';
            document.getElementById('resolveForm').action              = '/tickets/' + this.dataset.id + '/status';
            document.getElementById('resolution_notes').value         = '';
            resolveModal.show();
        });
    });

    /* ── Reopen Modal ── */
    const reopenModal = new bootstrap.Modal(document.getElementById('reopenModal'));
    document.querySelectorAll('.open-reopen-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('reopenTicketId').value            = this.dataset.id;
            document.getElementById('reopenTicketNumber').textContent  = this.dataset.ticketNumber;
            document.getElementById('reopen_reason').value            = '';
            document.getElementById('reopenForm').action              = '/tickets/' + this.dataset.id + '/reopen';

            const resolution  = this.dataset.resolution || '';
            const container   = document.getElementById('previousResolutionContainer');
            if (resolution.trim()) {
                document.getElementById('previousResolution').textContent = resolution;
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
            reopenModal.show();
        });
    });

});
</script>
@endpush
