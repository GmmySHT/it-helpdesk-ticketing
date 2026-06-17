@extends('layouts.app')

@section('title', 'Semua Ticket - IT Helpdesk')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tickets-my.css') }}">
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
            <div class="col-md-4">
                <label class="tk-filter-label">
                    <i class="fas fa-search"></i> Cari
                </label>
                <div class="tk-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="tk-input"
                           placeholder="Cari ticket number / judul / nama">
                </div>
            </div>
            <div class="col-md-3">
                <label class="tk-filter-label">
                    <i class="fas fa-filter"></i> Status
                </label>
                <select name="status" class="tk-select">
                    <option value="">Semua Status</option>
                    @foreach(['open' => 'Open', 'in_queue' => 'Dalam Antrian', 'in_progress' => 'Sedang Dikerjakan', 'resolved' => 'Selesai', 'closed' => 'Ditutup'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="tk-filter-label">
                    <i class="fas fa-undo-alt"></i> Reopen
                </label>
                <select name="has_reopen" class="tk-select">
                    <option value="">Semua</option>
                    <option value="yes" {{ request('has_reopen') == 'yes' ? 'selected' : '' }}>Pernah Reopen</option>
                    <option value="no"  {{ request('has_reopen') == 'no'  ? 'selected' : '' }}>Belum Pernah Reopen</option>
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

        <div class="card-header-title">
            <i class="fas fa-list"></i> Daftar Semua Ticket
            <span class="tk-tab-count ms-1">{{ $tickets->total() }}</span>
        </div>

        @if($tickets->count() > 0)
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
                        <th>Ditugaskan Ke</th>
                        <th>Pembuat</th>
                        <th>Dibuat</th>
                        <th>SLA Deadline</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    @php
                        $isOverdue = $ticket->sla_due_at
                            && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at))
                            && !in_array($ticket->status, ['resolved', 'closed']);
                        $priorityMap = ['low' => 'low', 'medium' => 'medium', 'high' => 'high', 'urgent' => 'urgent'];
                        $shortTitle  = \Illuminate\Support\Str::limit(strip_tags($ticket->title), 45);
                    @endphp
                    <tr @if($isOverdue) class="tk-row-overdue" @endif>

                        {{-- # --}}
                        <td class="tk-id-cell">{{ ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration }}</td>

                        {{-- Ticket Number --}}
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="tk-ticket-number">
                                {{ $ticket->ticket_number }}
                            </a>
                            @if($ticket->reopen_count > 0)
                                <span class="tk-reopen-tag" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                                    <i class="fas fa-undo-alt"></i> {{ $ticket->reopen_count }}x
                                </span>
                            @endif
                        </td>

                        {{-- Judul --}}
                        <td>
                            <div class="tk-ticket-title" style="max-width:200px" title="{{ strip_tags($ticket->title) }}">
                                {{ $shortTitle }}
                            </div>
                        </td>

                        {{-- Kategori --}}
                        <td>
                            <span class="tk-badge tk-badge-category">
                                <i class="fas fa-tag"></i> {{ $ticket->category->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Prioritas --}}
                        <td>
                            <span class="tk-badge tk-badge-priority-{{ $priorityMap[$ticket->priority] ?? 'medium' }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="tk-badge tk-badge-status-{{ $ticket->status }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>

                        {{-- Ditugaskan Ke --}}
                        <td>
                            @if($ticket->assignedTo)
                                <div class="tk-person-cell">
                                    <div class="tk-avatar">{{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}</div>
                                    <span class="tk-person-name" title="{{ $ticket->assignedTo->name }}">
                                        {{ \Illuminate\Support\Str::limit($ticket->assignedTo->name, 12) }}
                                    </span>
                                </div>
                            @else
                                <span class="tk-person-empty">—</span>
                            @endif
                        </td>

                        {{-- Pembuat --}}
                        <td>
                            <div class="tk-person-cell">
                                <div class="tk-avatar tk-avatar-muted">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</div>
                                <span class="tk-person-name" title="{{ $ticket->user->name ?? '-' }}">
                                    {{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 12) }}
                                </span>
                            </div>
                        </td>

                        {{-- Dibuat --}}
                        <td class="tk-date-cell">
                            {{ $ticket->created_at->format('d M Y') }}
                            <div class="tk-duration-text">{{ $ticket->created_at->diffForHumans() }}</div>
                        </td>

                        {{-- SLA Deadline --}}
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

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="tk-action-group">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="tk-btn-icon tk-btn-view"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
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

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="tk-footer-info">
                    <i class="fas fa-info-circle"></i>
                    Menampilkan {{ $tickets->firstItem() ?? 0 }} – {{ $tickets->lastItem() ?? 0 }}
                    dari {{ $tickets->total() }} tiket
                </div>
                <div>
                    {{ $tickets->appends(request()->except('page'))->links('vendor.pagination.tk-pagination') }}
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
