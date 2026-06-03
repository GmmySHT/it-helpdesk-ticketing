@extends('layouts.app')

@section('title', 'Tiket Saya - IT Staff')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header dengan Container Biru --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-tasks me-2"></i>Tiket Saya
                    </h1>
                    <p class="page-subtitle mb-0">
                        Daftar ticket yang ditugaskan kepada Anda (antrian dan sedang dikerjakan)
                    </p>
                </div>
                <div>
                    <a href="{{ route('tickets.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-2"></i>Buat Ticket Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Urutan Prioritas --}}
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Informasi Pengurutan:</strong> Ticket diurutkan berdasarkan <strong>Prioritas</strong> (Urgent → High → Medium → Low),
        kemudian berdasarkan <strong>SLA Deadline</strong> terdekat, lalu berdasarkan <strong>waktu dibuat</strong>.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    {{-- Filter Area --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('it.tickets.my') }}" class="row g-3 align-items-end" id="filterForm">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-search me-1"></i> Cari Ticket
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control border-start-0 ps-0"
                               placeholder="Cari ticket number atau judul...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-filter me-1"></i> Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="in_queue" {{ request('status') == 'in_queue' ? 'selected' : '' }}>Dalam Antrian</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Selesai</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-flag me-1"></i> Prioritas
                    </label>
                    <select name="priority" class="form-select">
                        <option value="">Semua Prioritas</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>🟠 High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>🟢 Low</option>
                    </select>
                </div>
                <input type="hidden" name="tab" id="tabInput" value="{{ request('tab', 'active') }}">
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-sliders-h me-1"></i> Filter
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('it.tickets.my') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs untuk Memisahkan Ticket --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <ul class="nav nav-tabs card-header-tabs" id="ticketTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab', 'active') == 'active' ? 'active' : '' }}" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-tickets" type="button" role="tab" data-tab="active">
                        <i class="fas fa-tasks me-1"></i> Aktif / Dalam Proses
                        <span class="badge bg-primary ms-1">{{ $activeTicketsCount ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab', 'active') == 'resolved' ? 'active' : '' }}" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved-tickets" type="button" role="tab" data-tab="resolved">
                        <i class="fas fa-check-circle me-1"></i> Selesai / Ditutup
                        <span class="badge bg-success ms-1">{{ $resolvedTicketsCount ?? 0 }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                {{-- Tab 1: Aktif / Dalam Proses --}}
                <div class="tab-pane fade {{ request('tab', 'active') == 'active' ? 'show active' : '' }}" id="active-tickets" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th class="py-2">Ticket Number</th>
                                    <th class="py-2">Judul</th>
                                    <th class="py-2">Kategori</th>
                                    <th class="py-2">Prioritas</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Pembuat</th>
                                    <th class="py-2">Dibuat</th>
                                    <th class="py-2">SLA Deadline</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeTickets ?? [] as $index => $ticket)
                                @php
                                    $isOverdue = $ticket->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at));
                                    $priorityLabels = ['low' => '🟢 Low', 'medium' => '🟡 Medium', 'high' => '🟠 High', 'urgent' => '🔴 Urgent'];
                                    $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                                    $statusLabels = ['in_queue' => 'Dalam Antrian', 'in_progress' => 'Sedang Dikerjakan'];
                                    $statusColors = ['in_queue' => 'info', 'in_progress' => 'warning'];
                                    $shortTitle = \Illuminate\Support\Str::limit(strip_tags($ticket->title), 35);
                                    $shortUserName = \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 10);
                                @endphp
                                <tr @if($isOverdue) class="table-danger" @endif>
                                    <td class="ps-3">{{ ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('it.tickets.show', $ticket) }}" class="fw-bold text-decoration-none text-primary">
                                            {{ $ticket->ticket_number }}
                                        </a>
                                        @if($ticket->reopen_count > 0)
                                            <span class="badge bg-warning bg-opacity-10 text-warning ms-1" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                                                <i class="fas fa-undo-alt me-1"></i> {{ $ticket->reopen_count }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold" title="{{ strip_tags($ticket->title) }}">{{ $shortTitle }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                            <i class="fas fa-tag me-1"></i> {{ $ticket->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'primary' }} bg-opacity-10 text-{{ $priorityColors[$ticket->priority] ?? 'primary' }} px-2 py-1">
                                            {{ $priorityLabels[$ticket->priority] ?? ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusColors[$ticket->status] ?? 'secondary' }} px-2 py-1">
                                            <i class="fas {{ $ticket->status == 'in_queue' ? 'fa-clock' : 'fa-cogs' }} me-1"></i>
                                            {{ $statusLabels[$ticket->status] ?? ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="avatar-sm bg-secondary">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</div>
                                            <span class="small">{{ $shortUserName }}</span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="small">{{ $ticket->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($ticket->sla_due_at)
                                            <div class="small {{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M Y H:i') }}
                                            </div>
                                            @if($isOverdue)
                                                <span class="badge bg-danger mt-1">OVERDUE</span>
                                            @else
                                                @php $hoursLeft = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($ticket->sla_due_at), false); @endphp
                                                @if($hoursLeft <= 24 && $hoursLeft > 0)
                                                    <span class="badge bg-warning mt-1">H-{{ $hoursLeft }} jam</span>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('it.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 30px; height: 30px; line-height: 30px; padding: 0;" title="Lihat Detail">
                                                <i class="fas fa-eye fa-sm"></i>
                                            </a>

                                            @if($ticket->status == 'in_queue')
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle take-ticket-btn" style="width: 30px; height: 30px; line-height: 30px; padding: 0;" data-url="{{ route('it.tickets.take', $ticket) }}" data-ticket-number="{{ $ticket->ticket_number }}" title="Ambil Ticket">
                                                    <i class="fas fa-hand-paper fa-sm"></i>
                                                </button>
                                            @elseif($ticket->status == 'in_progress')
                                                <button type="button" class="btn btn-sm btn-outline-success rounded-circle resolve-btn" style="width: 30px; height: 30px; line-height: 30px; padding: 0;" data-ticket-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}" data-ticket-title="{{ addslashes($shortTitle) }}" title="Selesaikan Ticket">
                                                    <i class="fas fa-check-circle fa-sm"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-tasks fa-3x mb-2 text-muted"></i>
                                            <p class="text-muted mb-0">Tidak ada ticket aktif</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 2: Selesai / Ditutup --}}
                <div class="tab-pane fade {{ request('tab', 'active') == 'resolved' ? 'show active' : '' }}" id="resolved-tickets" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th class="py-2">Ticket Number</th>
                                    <th class="py-2">Judul</th>
                                    <th class="py-2">Kategori</th>
                                    <th class="py-2">Prioritas</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Pembuat</th>
                                    <th class="py-2">Dibuat</th>
                                    <th class="py-2">Tanggal Selesai</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resolvedTickets ?? [] as $index => $ticket)
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
                                    $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                                    $shortTitle = \Illuminate\Support\Str::limit(strip_tags($ticket->title), 35);
                                    $shortUserName = \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 10);
                                @endphp
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('it.tickets.show', $ticket) }}" class="fw-bold text-decoration-none text-primary">
                                            {{ $ticket->ticket_number }}
                                        </a>
                                        @if($ticket->reopen_count > 0)
                                            <span class="badge bg-warning bg-opacity-10 text-warning ms-1" title="Dibuka kembali {{ $ticket->reopen_count }} kali">
                                                <i class="fas fa-undo-alt me-1"></i> {{ $ticket->reopen_count }}x
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold" title="{{ strip_tags($ticket->title) }}">{{ $shortTitle }}</div>
                                        @if($duration)<div class="small text-success"><i class="fas fa-hourglass-half me-1"></i> {{ $duration }}</div>@endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                            <i class="fas fa-tag me-1"></i> {{ $ticket->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'primary' }} bg-opacity-10 text-{{ $priorityColors[$ticket->priority] ?? 'primary' }} px-2 py-1">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->status == 'resolved' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $ticket->status == 'resolved' ? 'success' : 'secondary' }} px-2 py-1">
                                            <i class="fas {{ $ticket->status == 'resolved' ? 'fa-check-circle' : 'fa-archive' }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="avatar-sm bg-secondary">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}</div>
                                            <span class="small">{{ $shortUserName }}</span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="small">{{ $ticket->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($ticket->resolved_at)
                                            <div class="small text-success">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y H:i') }}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('it.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 30px; height: 30px; line-height: 30px; padding: 0;" title="Lihat Detail">
                                                <i class="fas fa-eye fa-sm"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-circle open-reopen-modal" style="width: 30px; height: 30px; line-height: 30px; padding: 0;" data-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}" data-title="{{ addslashes($shortTitle) }}" data-resolution="{{ $ticket->resolution_notes ?? '' }}" title="Buka Kembali Ticket">
                                                <i class="fas fa-undo-alt fa-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-check-circle fa-3x mb-2 text-muted"></i>
                                            <p class="text-muted mb-0">Belum ada ticket yang diselesaikan</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if(isset($tickets) && $tickets->total() > 0)
        <div class="card-footer bg-white border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Menampilkan {{ $tickets->firstItem() ?? 0 }} - {{ $tickets->lastItem() ?? 0 }} dari {{ $tickets->total() }} tiket
                </div>
                <div>
                    {{ $tickets->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Resolve Modal --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="resolveForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" value="resolved">
            <input type="hidden" name="ticket_id" id="ticketId" />
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Selesaikan Ticket <span id="resolveTicketNumber" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info"><strong>Ticket:</strong> <span id="resolveTicketTitle"></span></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Solusi <span class="text-danger">*</span></label>
                        <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran (Opsional)</label>
                        <input type="file" name="resolution_attachments[]" class="form-control" multiple>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Setelah ticket diselesaikan, status tidak dapat diubah kembali.
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
                    <h5 class="modal-title">Buka Kembali Ticket <span id="reopenTicketNumber" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="reopenTicketId" />
                    <div class="alert alert-warning">Ticket akan masuk ke antrian kembali.</div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Dibuka Kembali <span class="text-danger">*</span></label>
                        <textarea name="reopen_reason" id="reopen_reason" class="form-control" rows="4" required></textarea>
                    </div>
                    <div id="previousResolutionContainer" style="display: none;">
                        <div class="alert alert-info"><strong>Solusi sebelumnya:</strong><br><span id="previousResolution"></span></div>
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

<style>
    .avatar-sm {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }
    .badge {
        font-weight: 500;
    }
    .table td, .table th {
        padding: 0.75rem 0.5rem;
        font-size: 0.85rem;
    }
    .btn-sm.rounded-circle {
        width: 30px !important;
        height: 30px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        padding: 0 !important;
    }
    @media (max-width: 768px) {
        .table td, .table th {
            padding: 0.5rem 0.25rem;
            font-size: 0.75rem;
        }
        .btn-sm.rounded-circle {
            width: 26px !important;
            height: 26px !important;
        }
        .avatar-sm {
            width: 24px;
            height: 24px;
            font-size: 0.6rem;
        }
    }
</style>
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
                confirmButtonColor: '#0d6efd',
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
                confirmButtonColor: '#28a745',
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
                confirmButtonColor: '#ffc107',
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
