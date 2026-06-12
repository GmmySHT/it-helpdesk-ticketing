@extends('layouts.app')

@section('title', 'Semua Ticket - IT Helpdesk')

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
                    <i class="fas fa-ticket-alt"></i>Semua Ticket
                </h1>
                <p class="tk-page-subtitle">
                    <span class="badge-readonly"><i class="fas fa-eye"></i> Read-Only</span>
                    Daftar semua ticket yang pernah dibuat
                </p>
            </div>
            <a href="{{ route('it.tickets.my') }}" class="tk-btn-header">
                <i class="fas fa-tasks"></i>Ticket Saya
            </a>
        </div>
    </div>

    {{-- ══════════════════════ ALERT READ-ONLY ══════════════════════ --}}
    <div class="tk-alert alert alert-dismissible fade show" role="alert">
        <div class="tk-alert-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div>
            <strong>Mode Baca Saja:</strong> Anda dapat melihat semua ticket, tetapi tidak dapat mengubah status atau melakukan tindakan lainnya.
            Gunakan menu <strong>"Ticket Saya"</strong> untuk mengelola ticket yang ditugaskan kepada Anda.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- ══════════════════════ FILTER AREA ══════════════════════ --}}
    <div class="tk-filter-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="tk-filter-label">
                    <i class="fas fa-search"></i> Cari
                </label>
                <div class="tk-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="tk-input"
                           placeholder="Cari ticket number / judul">
                </div>
            </div>
            <div class="col-md-2">
                <label class="tk-filter-label">
                    <i class="fas fa-filter"></i> Status
                </label>
                <select name="status" class="tk-select">
                    <option value="">Semua Status</option>
                    @foreach(['open','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" {{ request('status')==$s ? 'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="tk-filter-label">
                    <i class="fas fa-flag"></i> Prioritas
                </label>
                <select name="priority" class="tk-select">
                    <option value="">Semua Prioritas</option>
                    <option value="low" {{ request('priority')=='low'?'selected':'' }}>Low</option>
                    <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
                    <option value="high" {{ request('priority')=='high'?'selected':'' }}>High</option>
                    <option value="urgent" {{ request('priority')=='urgent'?'selected':'' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="tk-filter-label">
                    <i class="fas fa-undo-alt"></i> Reopen
                </label>
                <select name="has_reopen" class="tk-select">
                    <option value="">Semua</option>
                    <option value="yes" {{ request('has_reopen') == 'yes' ? 'selected' : '' }}>Pernah Reopen</option>
                    <option value="no" {{ request('has_reopen') == 'no' ? 'selected' : '' }}>Belum Pernah Reopen</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="tk-btn-filter" type="submit">
                    <i class="fas fa-sliders-h"></i> Filter
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('it.tickets.all') }}" class="tk-btn-reset">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ══════════════════════ TABLE ══════════════════════ --}}
    <div class="tk-table-card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-list"></i>Daftar Semua Ticket
            </h5>
        </div>

        @if($tickets->count() > 0)
        <div class="tk-table-scroll">
            <table class="tk-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Ticket / Judul</th>
                        <th>Kategori</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Ditugaskan Ke</th>
                        <th>Pembuat</th>
                        <th>Tanggal Dibuat</th>
                        <th>Deadline SLA</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    @php
                        $isOverdue = $ticket->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at)) && !in_array($ticket->status, ['resolved', 'closed']);

                        $priorityMap = ['low'=>'low','medium'=>'medium','high'=>'high','urgent'=>'urgent'];
                        $statusMap   = ['open'=>'open','in_progress'=>'in_progress','resolved'=>'resolved','closed'=>'closed'];
                    @endphp
                    <tr @if($isOverdue) class="tk-row-overdue" @endif>
                        <td class="tk-id-cell">#{{ $ticket->id }}</td>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="tk-ticket-number">
                                {{ $ticket->ticket_number }}
                            </a>
                            <div class="tk-ticket-title" title="{{ $ticket->title }}">
                                {{ \Illuminate\Support\Str::limit($ticket->title, 50) }}
                            </div>
                        </td>
                        <td>
                            <span class="tk-badge tk-badge-category">
                                {{ $ticket->category->name ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="tk-badge tk-badge-priority-{{ $priorityMap[$ticket->priority] ?? 'medium' }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="tk-badge tk-badge-status-{{ $statusMap[$ticket->status] ?? 'open' }}">
                                {{ ucfirst(str_replace('_',' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td>
                            @if($ticket->assignedTo)
                                <div class="tk-person-cell">
                                    <div class="tk-avatar">{{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}</div>
                                    <span class="tk-person-name">{{ $ticket->assignedTo->name }}</span>
                                </div>
                            @else
                                <span class="tk-person-empty">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="tk-person-cell">
                                <div class="tk-avatar tk-avatar-muted">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</div>
                                <span class="tk-person-name">{{ $ticket->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="tk-date-cell">
                            {{ $ticket->created_at->format('d M Y') }}
                        </td>
                        <td class="tk-date-cell {{ $isOverdue ? 'tk-date-overdue' : '' }}">
                            @if($ticket->sla_due_at)
                                {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y H:i') }}
                            @else
                                <span class="tk-person-empty">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('tickets.show', $ticket) }}"
                               class="tk-btn-view"
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="tk-empty-state">
            <i class="fas fa-ticket-alt"></i>
            <p>Tidak ada ticket ditemukan</p>
        </div>
        @endif

        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="tk-footer-info">
                    Menampilkan {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} dari {{ $tickets->total() }} tiket
                </div>
                <div>
                    {{ $tickets->appends(request()->except('page'))->links('vendor.pagination.tk-pagination') }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
