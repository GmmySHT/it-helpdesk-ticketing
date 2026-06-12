@extends('layouts.app')

@section('title', 'Dashboard Tim IT')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}">
@endpush

@section('content')
<div class="dash-wrapper">

    {{-- ── HEADER ── --}}
    <div class="dash-header">
        <div class="dash-header-inner">
            <div>
                <h1 class="dash-header-title">
                    <i class="fas fa-desktop"></i>
                    Dashboard Tim IT
                </h1>
                <p class="dash-header-subtitle">Monitor dan kelola antrian ticket helpdesk</p>
            </div>
            <div class="dash-header-widgets">
                <div class="dash-clock">
                    <div class="dash-clock-time" id="dashClock">00:00:00</div>
                    <div class="dash-clock-date" id="dashDate"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="stat-grid">

        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Antrian / Inbox</div>
                <div class="stat-card-value" style="color:var(--accent-indigo)">{{ $inQueueCount ?? 0 }}</div>
                <div class="stat-card-trend neutral">
                    <i class="fas fa-clock me-1"></i>Tiket belum di-assign
                </div>
                @if(($inQueueChange['value'] ?? 0) != 0)
                    <div class="stat-card-trend {{ ($inQueueChange['trend'] ?? '') == 'up' ? 'up-bad' : 'down-good' }} mt-1">
                        <i class="fas fa-arrow-{{ ($inQueueChange['trend'] ?? '') == 'up' ? 'up' : 'down' }}"></i>
                        {{ $inQueueChange['value'] ?? 0 }}% dari bulan lalu
                    </div>
                @endif
            </div>
            <div class="stat-card-icon indigo">
                <i class="fas fa-inbox"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Tiket Aktif Saya</div>
                <div class="stat-card-value warn">{{ $myActiveTicketsCount ?? 0 }}</div>
                <div class="stat-card-trend neutral">
                    <i class="fas fa-spinner me-1"></i>Sedang dikerjakan
                </div>
            </div>
            <div class="stat-card-icon amber">
                <i class="fas fa-tasks"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Tiket Selesai</div>
                <div class="stat-card-value" style="color:var(--accent-emerald)">{{ $myResolvedTicketsCount ?? 0 }}</div>
                <div class="stat-card-trend up-good">
                    <i class="fas fa-check-circle me-1"></i>Total diselesaikan
                </div>
            </div>
            <div class="stat-card-icon emerald">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Rata-rata Penyelesaian</div>
                @php
                    $avgHours    = $avgResolutionHours ?? 0;
                    $totalMins   = round($avgHours * 60);
                    if ($totalMins < 60) {
                        $durDisplay = $totalMins . ' menit';
                    } else {
                        $h = floor($totalMins / 60); $m = $totalMins % 60;
                        $durDisplay = $h . ' jam' . ($m > 0 ? ' ' . $m . ' mnt' : '');
                    }
                @endphp
                <div class="stat-card-value" style="font-size:1.4rem; color:var(--accent-primary)">{{ $durDisplay }}</div>
                <div class="stat-card-trend neutral">
                    <i class="fas fa-hourglass-half me-1"></i>Per tiket
                </div>
            </div>
            <div class="stat-card-icon primary">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

    </div>

    {{-- ── CHARTS ROW ── --}}
    <div class="charts-row">

        {{-- Trend Line Chart --}}
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title">
                    <i class="fas fa-chart-line"></i> Trend Tiket Masuk (14 Hari Terakhir)
                </h5>
            </div>
            <div class="glass-card-body">
                <div class="chart-wrap chart-wrap--tall">
                    <canvas id="ticketsTrend"></canvas>
                </div>
            </div>
        </div>

        {{-- Tiket Per Bulan --}}
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title">
                    <i class="fas fa-chart-bar"></i> Tiket Per Bulan
                </h5>
                <span style="font-size:.7rem; color:var(--text-muted);">6 bulan terakhir</span>
            </div>
            <div class="glass-card-body">
                <div style="position:relative; height:200px;">
                    <canvas id="monthlyTicketsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Ringkasan Kinerja --}}
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title">
                    <i class="fas fa-chart-pie"></i> Ringkasan Kinerja
                </h5>
            </div>
            <div class="glass-card-body">

                <div style="margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; font-size:.8rem; margin-bottom:5px;">
                        <span style="color:var(--text-muted)">Tiket Selesai Bulan Ini</span>
                        <span style="font-weight:700; color:var(--accent-emerald)">{{ $resolvedThisMonth ?? 0 }}</span>
                    </div>
                    <div class="sla-progress-track">
                        <div class="sla-progress-fill good" style="width:{{ min(100, ($resolvedThisMonth ?? 0) * 10) }}%"></div>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; font-size:.8rem; margin-bottom:5px;">
                        <span style="color:var(--text-muted)">Tiket Dalam Proses</span>
                        <span style="font-weight:700; color:var(--accent-amber)">{{ $inProgressCount ?? 0 }}</span>
                    </div>
                    <div class="sla-progress-track">
                        <div class="sla-progress-fill medium" style="width:{{ min(100, ($inProgressCount ?? 0) * 10) }}%"></div>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; font-size:.8rem; margin-bottom:5px;">
                        <span style="color:var(--text-muted)">Rata-rata Respon</span>
                        <span style="font-weight:700; color:var(--accent-primary)">{{ $avgResponseTime ?? 'N/A' }}</span>
                    </div>
                    <div class="sla-progress-track">
                        <div class="sla-progress-fill good" style="width:65%"></div>
                    </div>
                </div>

                <div style="border-top:1px solid var(--border); padding-top:.75rem; text-align:center;">
                    <span style="font-size:.72rem; color:var(--text-muted);">
                        <i class="fas fa-sync-alt me-1"></i>
                        Update: {{ now()->format('d M Y H:i') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── ANTRIAN INBOX ── --}}
    <div class="glass-card">
        <div class="glass-card-header">
            <h5 class="glass-card-title">
                <i class="fas fa-inbox"></i> Antrian Ticket
                <span class="badge-pill" style="background:#ede9fe;color:#4338ca;margin-left:6px;">{{ count($ticketsInbox ?? []) }}</span>
            </h5>
            <a href="{{ route('tickets.index', ['filter' => 'inbox']) }}" class="btn-glass">
                <i class="fas fa-arrow-right me-1"></i>Lihat Semua
            </a>
        </div>
        <div class="table-scroll">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ticket</th>
                        <th>Judul</th>
                        <th>Prioritas</th>
                        <th>Kategori</th>
                        <th>Dibuat</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ticketsInbox ?? [] as $t)
                    @php
                        $pb = [
                            'low'    => ['badge-priority-low',    'fas fa-arrow-down'],
                            'medium' => ['badge-priority-medium', 'fas fa-minus'],
                            'high'   => ['badge-priority-high',   'fas fa-arrow-up'],
                            'urgent' => ['badge-priority-urgent', 'fas fa-exclamation-triangle'],
                        ][$t->priority] ?? ['badge-priority-medium','fas fa-flag'];
                    @endphp
                    <tr>
                        <td style="font-weight:700;color:var(--accent-primary)">{{ $loop->iteration }}</td>
                        <td><a href="{{ route('tickets.show', $t) }}" class="ticket-link">{{ $t->ticket_number }}</a></td>
                        <td><span class="ticket-title-text">{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 50) }}</span></td>
                        <td>
                            <span class="badge-pill {{ $pb[0] }}">
                                <i class="{{ $pb[1] }}" style="font-size:.65rem"></i>
                                {{ ucfirst($t->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-pill" style="background:#f1f5f9;color:#475569;">
                                <i class="fas fa-tag" style="font-size:.65rem"></i>
                                {{ $t->category->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td>
                            <div class="text-small">{{ $t->created_at->format('d M Y H:i') }}</div>
                            <div style="font-size:.7rem;color:var(--text-muted)">{{ $t->created_at->diffForHumans() }}</div>
                        </td>
                        <td style="text-align:center">
                            <form action="{{ route('tickets.take', $t) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn-take"
                                        onclick="return confirm('Ambil ticket ini?')">
                                    <i class="fas fa-hand-paper me-1"></i>Take
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Tidak ada antrian ticket</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── MY TICKETS TABS ── --}}
    <div class="glass-card">
        <div class="glass-card-header" style="padding-bottom:0; border-bottom:none;">
            <div class="it-tabs" id="myTicketsTab">
                <button class="it-tab active" data-target="#tab-active">
                    <i class="fas fa-tasks me-1"></i>Tiket Aktif
                    <span class="it-tab-badge">{{ $myActiveTicketsCount ?? 0 }}</span>
                </button>
                <button class="it-tab" data-target="#tab-resolved">
                    <i class="fas fa-check-circle me-1"></i>Tiket Selesai
                    <span class="it-tab-badge" style="background:#d1fae5;color:#065f46;">{{ $myResolvedTicketsCount ?? 0 }}</span>
                </button>
                <button class="it-tab" data-target="#tab-all">
                    <i class="fas fa-list me-1"></i>Semua Tiket Saya
                    <span class="it-tab-badge" style="background:#f1f5f9;color:#475569;">{{ $myAssignedCount ?? 0 }}</span>
                </button>
            </div>
        </div>

        {{-- Tab: Tiket Aktif --}}
        <div id="tab-active" class="it-tab-pane active">
            <div class="table-scroll">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Ticket</th><th>Judul</th><th>Status</th>
                            <th>Prioritas</th><th>SLA Deadline</th><th>Dibuat</th><th style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myActiveTickets ?? [] as $t)
                        @php
                            $isOverdue = $t->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($t->sla_due_at));
                            $pb = [
                                'low'    => 'badge-priority-low',
                                'medium' => 'badge-priority-medium',
                                'high'   => 'badge-priority-high',
                                'urgent' => 'badge-priority-urgent',
                            ][$t->priority] ?? 'badge-priority-medium';
                            $sb = [
                                'open'        => 'badge-status-open',
                                'in_queue'    => 'badge-status-open',
                                'in_progress' => 'badge-status-in_progress',
                                'resolved'    => 'badge-status-resolved',
                                'closed'      => 'badge-status-closed',
                            ][$t->status] ?? 'badge-status-open';
                        @endphp
                        <tr @if($isOverdue) style="background:#fff5f5;" @endif>
                            <td style="font-weight:700;color:var(--accent-primary)">{{ $loop->iteration }}</td>
                            <td><a href="{{ route('tickets.show', $t) }}" class="ticket-link">{{ $t->ticket_number }}</a></td>
                            <td><span class="ticket-title-text">{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 45) }}</span></td>
                            <td><span class="badge-pill {{ $sb }}">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span></td>
                            <td><span class="badge-pill {{ $pb }}">{{ ucfirst($t->priority) }}</span></td>
                            <td>
                                @if($t->sla_due_at)
                                    <span style="font-size:.75rem; color:{{ $isOverdue ? 'var(--accent-rose)' : 'var(--text-secondary)' }}; font-weight:{{ $isOverdue ? '700' : '400' }}">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        {{ \Carbon\Carbon::parse($t->sla_due_at)->format('d M Y H:i') }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="badge-pill badge-sla-overdue ms-1">OVERDUE</span>
                                    @else
                                        <span class="badge-pill badge-sla-ok ms-1">ON TIME</span>
                                    @endif
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-small">{{ $t->created_at->format('d M Y') }}</div>
                            </td>
                            <td style="text-align:center">
                                <div style="display:flex;gap:4px;justify-content:center;">
                                    <a href="{{ route('tickets.show', $t) }}" class="btn-action" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($t->status != 'resolved')
                                        <button type="button"
                                                class="btn-action btn-action--success resolve-btn"
                                                title="Resolve"
                                                data-id="{{ $t->id }}"
                                                data-number="{{ $t->ticket_number }}"
                                                data-title="{{ addslashes(strip_tags($t->title)) }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Tidak ada tiket aktif yang sedang dikerjakan</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Tiket Selesai --}}
        <div id="tab-resolved" class="it-tab-pane" style="display:none;">
            <div class="table-scroll">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Ticket</th><th>Judul</th><th>Status</th>
                            <th>Diselesaikan</th><th>Durasi</th><th>Reopen</th><th style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myResolvedTickets ?? [] as $t)
                        @php
                            $diffMins   = $t->created_at && $t->resolved_at ? $t->created_at->diffInMinutes($t->resolved_at) : 0;
                            $dh = floor($diffMins / 60); $dm = $diffMins % 60;
                            $durStr = $dh > 0 ? $dh.' jam'.($dm > 0 ? ' '.$dm.' mnt' : '') : ($dm > 0 ? $dm.' mnt' : '—');
                        @endphp
                        <tr>
                            <td style="font-weight:700;color:var(--accent-primary)">{{ $loop->iteration }}</td>
                            <td><a href="{{ route('tickets.show', $t) }}" class="ticket-link">{{ $t->ticket_number }}</a></td>
                            <td><span class="ticket-title-text">{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 45) }}</span></td>
                            <td><span class="badge-pill badge-status-resolved">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span></td>
                            <td>
                                @if($t->resolved_at)
                                    <div class="text-small">{{ \Carbon\Carbon::parse($t->resolved_at)->format('d M Y H:i') }}</div>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-pill" style="background:#dbeafe;color:#1e40af;">{{ $durStr }}</span>
                            </td>
                            <td>
                                @if(($t->reopen_count ?? 0) > 0)
                                    <span class="badge-pill badge-priority-medium">
                                        <i class="fas fa-undo-alt" style="font-size:.65rem"></i> {{ $t->reopen_count }}x
                                    </span>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                <a href="{{ route('tickets.show', $t) }}" class="btn-action" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>Belum ada tiket yang diselesaikan</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Semua Tiket --}}
        <div id="tab-all" class="it-tab-pane" style="display:none;">
            <div class="table-scroll">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Ticket</th><th>Judul</th><th>Status</th>
                            <th>Prioritas</th><th>Dibuat</th><th style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ticketsMy ?? [] as $t)
                        @php
                            $pb = [
                                'low'=>'badge-priority-low','medium'=>'badge-priority-medium',
                                'high'=>'badge-priority-high','urgent'=>'badge-priority-urgent',
                            ][$t->priority] ?? 'badge-priority-medium';
                            $sb = [
                                'open'=>'badge-status-open','in_queue'=>'badge-status-open',
                                'in_progress'=>'badge-status-in_progress',
                                'resolved'=>'badge-status-resolved','closed'=>'badge-status-closed',
                            ][$t->status] ?? 'badge-status-open';
                        @endphp
                        <tr>
                            <td style="font-weight:700;color:var(--accent-primary)">{{ $loop->iteration }}</td>
                            <td><a href="{{ route('tickets.show', $t) }}" class="ticket-link">{{ $t->ticket_number }}</a></td>
                            <td><span class="ticket-title-text">{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 45) }}</span></td>
                            <td><span class="badge-pill {{ $sb }}">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span></td>
                            <td><span class="badge-pill {{ $pb }}">{{ ucfirst($t->priority) }}</span></td>
                            <td><div class="text-small">{{ $t->created_at->format('d M Y') }}</div></td>
                            <td style="text-align:center">
                                <div style="display:flex;gap:4px;justify-content:center;">
                                    <a href="{{ route('tickets.show', $t) }}" class="btn-action" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!in_array($t->status, ['resolved','closed']))
                                        <button type="button"
                                                class="btn-action btn-action--success resolve-btn"
                                                title="Resolve"
                                                data-id="{{ $t->id }}"
                                                data-number="{{ $t->ticket_number }}"
                                                data-title="{{ addslashes(strip_tags($t->title)) }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-ticket-alt"></i>
                                <p>Belum ada tiket yang ditugaskan</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /glass-card tabs --}}

</div>{{-- /dash-wrapper --}}


{{-- ══ RESOLVE MODAL ══ --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="resolveForm" method="POST" action="" enctype="multipart/form-data">
      @csrf
      <div class="modal-content" style="border:none;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
        <div class="modal-header" style="background:linear-gradient(135deg,#059669,#0d9488);border:none;">
          <h5 class="modal-title" style="color:#fff;font-weight:600;font-size:.95rem;">
            <i class="fas fa-check-circle me-2"></i>
            Selesaikan Ticket <span id="resolveTicketNumber" style="font-family:'Courier New',monospace;"></span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding:1.5rem;">
          <input type="hidden" name="status" value="resolved">
          <input type="hidden" name="ticket_id" id="resolveTicketId">

          <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius-md);padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#065f46;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Ticket:</strong> <span id="resolveTicketTitle"></span>
          </div>

          <div style="margin-bottom:1.25rem;">
            <label style="font-size:.82rem;font-weight:600;color:#059669;display:block;margin-bottom:6px;">
              <i class="fas fa-sticky-note me-1"></i>Catatan Solusi <span style="color:var(--accent-rose)">*</span>
            </label>
            <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="5" required
                      placeholder="Jelaskan solusi yang diberikan untuk ticket ini..."></textarea>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">
              <i class="fas fa-info-circle me-1"></i>
              Catatan ini akan terlihat oleh pembuat ticket sebagai history.
            </div>
          </div>

          <div style="margin-bottom:1.25rem;">
            <label style="font-size:.82rem;font-weight:600;color:#059669;display:block;margin-bottom:6px;">
              <i class="fas fa-paperclip me-1"></i>Lampiran Solusi <span style="color:var(--text-muted);font-weight:400">(Opsional)</span>
            </label>
            <input type="file" name="resolution_attachments[]" class="form-control" multiple
                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">
              <i class="fas fa-info-circle me-1"></i>Multiple file. Maks 5MB per file.
            </div>
          </div>

          <div style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="notify_user" id="notifyUser" value="1" checked
                   style="width:16px;height:16px;accent-color:var(--accent-emerald);">
            <label for="notifyUser" style="font-size:.82rem;color:var(--text-secondary);cursor:pointer;margin:0;">
              <i class="fas fa-envelope me-1" style="color:var(--accent-emerald)"></i>
              Kirim notifikasi email ke pembuat ticket
            </label>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:.875rem 1.5rem;">
          <button type="button" class="btn-glass" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Batal
          </button>
          <button type="submit" id="submitResolveBtn"
                  style="display:inline-flex;align-items:center;gap:6px;padding:.55rem 1.25rem;border-radius:var(--radius-full);font-size:.82rem;font-weight:600;color:#fff;background:linear-gradient(135deg,#059669,#0d9488);border:none;cursor:pointer;">
            <i class="fas fa-check-circle"></i> Selesaikan Ticket
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- ── Page-specific CSS ── --}}
<style>
    .dash-wrapper { min-height: unset; }

    /* Chart wrap height */
    .chart-wrap--tall { height: 240px; }

    /* Take button */
    .btn-take {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 14px;
        border-radius: var(--radius-full);
        font-size: .75rem; font-weight: 600;
        color: #fff;
        background: var(--accent-primary);
        border: none; cursor: pointer;
        transition: background .15s;
    }
    .btn-take:hover { background: var(--accent-secondary); }

    /* Action buttons */
    .btn-action {
        width: 30px; height: 30px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: var(--bg-surface);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .75rem; color: var(--accent-primary);
        text-decoration: none; cursor: pointer;
        transition: background .15s;
    }
    .btn-action:hover { background: #dbeafe; }
    .btn-action--success { color: var(--accent-emerald); }
    .btn-action--success:hover { background: #d1fae5; }

    /* Header clock widget */
    .btn-header {
        display: inline-flex; align-items: center; gap: 6px;
        padding: .55rem 1.1rem;
        border-radius: var(--radius-full);
        font-size: .78rem; font-weight: 600;
        color: #fff;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.28);
        text-decoration: none;
        transition: background .15s;
    }

    /* Custom tab system (menghindari Bootstrap tab conflict) */
    .it-tabs {
        display: flex;
        gap: 2px;
        padding: .875rem 1.25rem 0;
        border-bottom: 1px solid var(--border);
    }
    .it-tab {
        display: inline-flex; align-items: center; gap: 6px;
        padding: .6rem 1rem;
        font-size: .82rem; font-weight: 600;
        color: var(--text-muted);
        background: none; border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        margin-bottom: -1px;
        transition: color .15s, border-color .15s;
    }
    .it-tab:hover { color: var(--text-primary); }
    .it-tab.active {
        color: var(--accent-primary);
        border-bottom-color: var(--accent-primary);
    }
    .it-tab-badge {
        background: #ede9fe; color: var(--accent-indigo);
        border-radius: var(--radius-full);
        font-size: .65rem; font-weight: 700;
        padding: 1px 7px;
    }
    .it-tab-pane { display: block; }

    /* Overdue row */
    .glass-table tbody tr[style*="background:#fff5f5"]:hover { background: #ffe8e8 !important; }

    /* Responsive */
    @media (max-width: 768px) {
        .main-grid { grid-template-columns: 1fr !important; }
        .charts-row { grid-template-columns: 1fr !important; }
        .it-tabs { overflow-x: auto; flex-wrap: nowrap; }
    }
</style>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Clock ──────────────────────────────────────────────────────
    function updateClock() {
        const now  = new Date();
        const time = now.toLocaleTimeString('id-ID', { hour12: false });
        const date = now.toLocaleDateString('id-ID', { weekday:'short', day:'numeric', month:'short', year:'numeric' });
        const el = document.getElementById('dashClock');
        const de = document.getElementById('dashDate');
        if (el) el.textContent = time;
        if (de) de.textContent = date;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Trend Chart ────────────────────────────────────────────────
    const labels    = {!! json_encode($days ?? []) !!};
    const chartData = {!! json_encode($counts ?? []) !!};
    const canvas    = document.getElementById('ticketsTrend');

    if (canvas && labels.length && chartData.length) {
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tiket per hari',
                    data: chartData,
                    fill: true,
                    tension: 0.35,
                    backgroundColor: 'rgba(29,111,184,0.08)',
                    borderColor: '#1d6fb8',
                    borderWidth: 2,
                    pointBackgroundColor: '#1d6fb8',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => `Tiket: ${ctx.raw}` }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: { size: 10 }, color: '#94a3b8' },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        border: { display: false }
                    },
                    x: {
                        ticks: { font: { size: 10 }, color: '#94a3b8' },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // ── Custom Tab Logic ───────────────────────────────────────────
    document.querySelectorAll('.it-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.it-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.it-tab-pane').forEach(p => p.style.display = 'none');
            this.classList.add('active');
            const pane = document.querySelector(this.dataset.target);
            if (pane) pane.style.display = 'block';
        });
    });

    // ── Monthly Chart ──────────────────────────────────────────────
    const monthLabels = {!! json_encode($months ?? []) !!};
    const monthCounts = {!! json_encode($ticketCountsByMonth ?? []) !!};
    const monthCanvas = document.getElementById('monthlyTicketsChart');

    if (monthCanvas && monthLabels.length && monthCounts.length) {
        new Chart(monthCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Jumlah Tiket',
                    data: monthCounts,
                    backgroundColor: 'rgba(13,148,136,0.15)',
                    borderColor: '#0d9488',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    barPercentage: 0.65,
                    categoryPercentage: 0.75,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => `${ctx.raw} tiket` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: { size: 10 }, color: '#94a3b8' },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        border: { display: false }
                    },
                    x: {
                        ticks: { font: { size: 10 }, color: '#94a3b8' },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    } else if (monthCanvas) {
        const noData = document.createElement('div');
        noData.style.cssText = 'text-align:center;padding:2rem;color:#94a3b8;font-size:.8rem;';
        noData.innerHTML = '<i class="fas fa-chart-bar" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>Tidak ada data chart';
        monthCanvas.parentNode.replaceChild(noData, monthCanvas);
    }

    // ── Resolve Modal ──────────────────────────────────────────────
    document.querySelectorAll('.resolve-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const number = this.dataset.number;
            const title  = this.dataset.title;

            document.getElementById('resolveTicketId').value       = id;
            document.getElementById('resolveTicketNumber').textContent = number;
            document.getElementById('resolveTicketTitle').textContent  = title;
            document.getElementById('resolveForm').action              = '/it/tickets/' + id + '/status';
            document.getElementById('resolution_notes').value          = '';

            new bootstrap.Modal(document.getElementById('resolveModal')).show();
        });
    });

});
</script>
@endpush
