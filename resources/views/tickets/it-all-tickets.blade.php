@extends('layouts.app')

@section('title', 'Semua Ticket - IT Helpdesk')

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
                    <p class="page-subtitle">(Read-Only) - Daftar semua ticket yang pernah dibuat</p>
                </div>
                <div>
                    <a href="{{ route('it.tickets.my') }}" class="btn btn-light">
                        <i class="fas fa-tasks me-2"></i>Ticket Saya
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Read-Only --}}
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Mode Baca Saja:</strong> Anda dapat melihat semua ticket, tetapi tidak dapat mengubah status atau melakukan tindakan lainnya.
        Gunakan menu <strong>"Ticket Saya"</strong> untuk mengelola ticket yang ditugaskan kepada Anda.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    {{-- Filter Area --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
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
                               placeholder="Cari ticket number / judul">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-filter me-1"></i> Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['open','in_progress','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ request('status')==$s ? 'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-flag me-1"></i> Prioritas
                    </label>
                    <select name="priority" class="form-select">
                        <option value="">Semua Prioritas</option>
                        <option value="low" {{ request('priority')=='low'?'selected':'' }}>Low</option>
                        <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
                        <option value="high" {{ request('priority')=='high'?'selected':'' }}>High</option>
                        <option value="urgent" {{ request('priority')=='urgent'?'selected':'' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-undo-alt me-1"></i> Reopen
                    </label>
                    <select name="has_reopen" class="form-select">
                        <option value="">Semua</option>
                        <option value="yes" {{ request('has_reopen') == 'yes' ? 'selected' : '' }}>Pernah Reopen</option>
                        <option value="no" {{ request('has_reopen') == 'no' ? 'selected' : '' }}>Belum Pernah Reopen</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-sliders-h me-1"></i> Filter
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('it.tickets.all') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Daftar Semua Ticket
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary bg-opacity-10">
                        <tr>
                            <th class="ps-3 py-3">#ID</th>
                            <th class="py-3">Ticket / Judul</th>
                            <th class="py-3">Kategori</th>
                            <th class="py-3">Prioritas</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Ditugaskan Ke</th>
                            <th class="py-3">Pembuat</th>
                            <th class="py-3">Tanggal Dibuat</th>
                            <th class="py-3">Deadline SLA</th>
                            <th class="text-center py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        @php
                            $isOverdue = $ticket->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at)) && !in_array($ticket->status, ['resolved', 'closed']);

                            $priorityColors = [
                                'low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'
                            ];
                            $statusColors = [
                                'open' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'
                            ];
                        @endphp
                        <tr @if($isOverdue) class="table-danger" @endif>
                            <td class="ps-3 fw-bold text-primary">#{{ $ticket->id }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none fw-semibold text-dark">
                                    {{ $ticket->ticket_number }}
                                </a>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($ticket->title, 50) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                    {{ $ticket->category->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'primary' }} bg-opacity-10 text-{{ $priorityColors[$ticket->priority] ?? 'primary' }} px-3 py-2">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusColors[$ticket->status] ?? 'secondary' }} px-3 py-2">
                                    {{ ucfirst(str_replace('_',' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->assignedTo)
                                    {{ $ticket->assignedTo->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $ticket->user->name ?? '-' }}</td>
                            <td class="text-nowrap">
                                <small>{{ $ticket->created_at->format('d M Y') }}</small>
                            </td>
                            <td class="text-nowrap">
                                @if($ticket->sla_due_at)
                                    <small class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y H:i') }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- HANYA TOMBOL LIHAT DETAIL --}}
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="btn btn-sm btn-outline-primary rounded-circle"
                                   style="width: 32px; height: 32px;"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-ticket-alt fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted mb-3">Tidak ada ticket ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} dari {{ $tickets->total() }} tiket
                </div>
                <div>
                    {{ $tickets->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-danger {
        background-color: #fff5f5 !important;
        border-left: 3px solid #dc3545;
    }
    .btn-sm.rounded-circle {
        width: 32px;
        height: 32px;
    }
</style>
@endsection
