@extends('layouts.app')

@section('title', 'Dashboard Tim IT')

@section('content')
<section class="section dashboard">
  <div class="row">

    <!-- KPI Cards -->
    <div class="col-lg-3 col-6">
      <div class="card info-card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="card-title text-muted mb-2">Antrian / Inbox</h5>
              <h3 class="mb-0">{{ $inQueueCount ?? 0 }}</h3>
              <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                Tiket belum di-assign
              </small>
            </div>
            <div class="bg-info bg-opacity-10 rounded-circle p-3">
              <i class="fas fa-inbox fa-2x text-info"></i>
            </div>
          </div>
          @if(($inQueueChange ?? null) && ($inQueueChange['value'] ?? 0) != 0)
            <div class="mt-2">
              @if($inQueueChange['trend'] == 'up')
                <span class="text-danger"><i class="fas fa-arrow-up"></i> +{{ $inQueueChange['value'] }}%</span>
              @elseif($inQueueChange['trend'] == 'down')
                <span class="text-success"><i class="fas fa-arrow-down"></i> {{ $inQueueChange['value'] }}%</span>
              @endif
              <span class="text-muted small"> dari bulan lalu</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="card info-card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="card-title text-muted mb-2">Tiket Aktif Saya</h5>
              <h3 class="mb-0">{{ $myActiveTicketsCount ?? 0 }}</h3>
              <small class="text-muted">
                <i class="fas fa-spinner fa-spin me-1"></i>
                Sedang dikerjakan
              </small>
            </div>
            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
              <i class="fas fa-tasks fa-2x text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="card info-card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="card-title text-muted mb-2">Tiket Selesai</h5>
              <h3 class="mb-0">{{ $myResolvedTicketsCount ?? 0 }}</h3>
              <small class="text-muted">
                <i class="fas fa-check-circle me-1 text-success"></i>
                Total diselesaikan
              </small>
            </div>
            <div class="bg-success bg-opacity-10 rounded-circle p-3">
              <i class="fas fa-check-circle fa-2x text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="card info-card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="card-title text-muted mb-2">Rata-rata Penyelesaian</h5>
              <h3 class="mb-0">
                @php
                    $avgHours = $avgResolutionHours ?? 0;
                    $totalMinutes = round($avgHours * 60);
                    if ($totalMinutes < 60) {
                        $durationDisplay = $totalMinutes . ' menit';
                    } else {
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                        $durationDisplay = $hours . ' jam';
                        if ($minutes > 0) {
                            $durationDisplay .= ' ' . $minutes . ' menit';
                        }
                    }
                @endphp
                {{ $durationDisplay }}
              </h3>
              <small class="text-muted">
                <i class="fas fa-hourglass-half me-1"></i>
                Per tiket
              </small>
            </div>
            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
              <i class="fas fa-chart-line fa-2x text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0">
            <i class="fas fa-chart-line me-2 text-primary"></i>Trend Tiket Masuk (14 Hari Terakhir)
          </h5>
        </div>
        <div class="card-body">
          <canvas id="ticketsTrend" style="max-height:300px; width:100%"></canvas>
        </div>
      </div>
    </div>

    <!-- Performance Summary -->
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0">
            <i class="fas fa-chart-pie me-2 text-primary"></i>Ringkasan Kinerja
          </h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>Tiket Selesai Bulan Ini</span>
              <span class="fw-bold">{{ $resolvedThisMonth ?? 0 }}</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-success" style="width: {{ min(100, ($resolvedThisMonth ?? 0) * 10) }}%"></div>
            </div>
          </div>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>Tiket Dalam Proses</span>
              <span class="fw-bold">{{ $inProgressCount ?? 0 }}</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-warning" style="width: {{ min(100, ($inProgressCount ?? 0) * 10) }}%"></div>
            </div>
          </div>
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>Rata-rata Respon</span>
              <span class="fw-bold">{{ $avgResponseTime ?? 'N/A' }}</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-info" style="width: 65%"></div>
            </div>
          </div>
          <hr>
          <div class="text-center">
            <small class="text-muted">
              <i class="fas fa-calendar-alt me-1"></i>
              Update: {{ now()->format('d M Y H:i') }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Inbox Table -->
    <div class="col-lg-12 mt-3">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="fas fa-inbox me-2 text-primary"></i>Antrian Ticket (Belum Di-assign)
            </h5>
            <a href="{{ route('tickets.index', ['filter'=>'inbox']) }}" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-arrow-right me-1"></i>Lihat Semua
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Ticket</th>
                  <th>Judul</th>
                  <th>Prioritas</th>
                  <th>Kategori</th>
                  <th>Dibuat</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($ticketsInbox ?? [] as $t)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <a href="{{ route('tickets.show', $t) }}" class="fw-bold text-decoration-none">
                        {{ $t->ticket_number }}
                      </a>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 50) }}</td>
                    <td>
                      <span class="badge bg-{{ $t->getPriorityBadgeAttribute() }}">
                        <i class="fas {{ $t->priority == 'low' ? 'fa-arrow-down' : ($t->priority == 'medium' ? 'fa-minus' : ($t->priority == 'high' ? 'fa-arrow-up' : 'fa-exclamation-triangle')) }} me-1"></i>
                        {{ ucfirst($t->priority) }}
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-tag me-1"></i>{{ $t->category->name ?? 'Umum' }}
                      </span>
                    </td>
                    <td>
                      <small>{{ $t->created_at->format('d M Y H:i') }}</small><br>
                      <small class="text-muted">{{ $t->created_at->diffForHumans() }}</small>
                    </td>
                    <td>
                      <form action="{{ route('tickets.take', $t) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Ambil ticket ini?')">
                          <i class="fas fa-hand-paper me-1"></i>Take
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                      <p class="text-muted mb-0">Tidak ada antrian ticket</p>
                    </td>
                  <tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- My Tickets dengan Tabs -->
    <div class="col-lg-12 mt-3">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <ul class="nav nav-tabs card-header-tabs" id="myTicketsTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                <i class="fas fa-tasks me-1"></i> Tiket Aktif
                <span class="badge bg-primary ms-1">{{ $myActiveTicketsCount ?? 0 }}</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved" type="button" role="tab">
                <i class="fas fa-check-circle me-1"></i> Tiket Selesai
                <span class="badge bg-success ms-1">{{ $myResolvedTicketsCount ?? 0 }}</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                <i class="fas fa-list me-1"></i> Semua Tiket Saya
                <span class="badge bg-secondary ms-1">{{ $myAssignedCount ?? 0 }}</span>
              </button>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content" id="myTicketsTabContent">

            <!-- Tab 1: Tiket Aktif -->
            <div class="tab-pane fade show active" id="active" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>No</th>
                      <th>Ticket</th>
                      <th>Judul</th>
                      <th>Status</th>
                      <th>Prioritas</th>
                      <th>SLA Deadline</th>
                      <th>Dibuat</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($myActiveTickets ?? [] as $t)
                      @php
                        $isOverdue = $t->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($t->sla_due_at));
                      @endphp
                      <tr @if($isOverdue) class="table-danger" @endif>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                          <a href="{{ route('tickets.show', $t) }}" class="fw-bold text-decoration-none">
                            {{ $t->ticket_number }}
                          </a>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 50) }}</td>
                        <td>
                          <span class="badge bg-{{ $t->getStatusBadgeAttribute() }}">
                            <i class="fas {{ $t->status == 'open' ? 'fa-envelope' : ($t->status == 'in_progress' ? 'fa-cogs' : 'fa-check-circle') }} me-1"></i>
                            {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                          </span>
                        </td>
                        <td>
                          <span class="badge bg-{{ $t->getPriorityBadgeAttribute() }}">
                            {{ ucfirst($t->priority) }}
                          </span>
                        </td>
                        <td>
                          @if($t->sla_due_at)
                            <small class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                              <i class="fas fa-hourglass-half me-1"></i>
                              {{ \Carbon\Carbon::parse($t->sla_due_at)->format('d M Y H:i') }}
                            </small>
                            @if($isOverdue)
                              <span class="badge bg-danger ms-1">OVERDUE</span>
                            @endif
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>
                          <small>{{ $t->created_at->format('d M Y') }}</small>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <a href="{{ route('tickets.show', $t) }}" class="btn btn-outline-primary">
                              <i class="fas fa-eye"></i> Detail
                            </a>
                            @if($t->status != 'resolved')
                              <button type="button" class="btn btn-outline-success resolve-btn"
                                      data-id="{{ $t->id }}"
                                      data-number="{{ $t->ticket_number }}"
                                      data-title="{{ addslashes(strip_tags($t->title)) }}">
                                <i class="fas fa-check-circle"></i> Resolve
                              </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="8" class="text-center py-4">
                          <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                          <p class="text-muted mb-0">Tidak ada tiket aktif yang sedang dikerjakan</p>
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab 2: Tiket Selesai -->
            <div class="tab-pane fade" id="resolved" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>No</th>
                      <th>Ticket</th>
                      <th>Judul</th>
                      <th>Status</th>
                      <th>Diselesaikan</th>
                      <th>Durasi</th>
                      <th>Reopen</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($myResolvedTickets ?? [] as $t)
                      @php
                        $diffMinutes = $t->created_at && $t->resolved_at ? $t->created_at->diffInMinutes($t->resolved_at) : 0;
                        $durationHours = floor($diffMinutes / 60);
                        $durationMinutes = $diffMinutes % 60;

                        if ($durationHours > 0 && $durationMinutes > 0) {
                            $durationDisplay = $durationHours . ' jam ' . $durationMinutes . ' menit';
                        } elseif ($durationHours > 0) {
                            $durationDisplay = $durationHours . ' jam';
                        } elseif ($durationMinutes > 0) {
                            $durationDisplay = $durationMinutes . ' menit';
                        } else {
                            $durationDisplay = '-';
                        }
                      @endphp
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                          <a href="{{ route('tickets.show', $t) }}" class="fw-bold text-decoration-none">
                            {{ $t->ticket_number }}
                          </a>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 50) }}</td>
                        <td>
                          <span class="badge bg-{{ $t->getStatusBadgeAttribute() }}">
                            {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                          </span>
                        </td>
                        <td>
                          @if($t->resolved_at)
                            <small>{{ \Carbon\Carbon::parse($t->resolved_at)->format('d M Y H:i') }}</small>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>
                          <span class="badge bg-info">{{ $durationDisplay }}</span>
                        </td>
                        <td>
                          @if($t->reopen_count > 0)
                            <span class="badge bg-warning" title="Dibuka kembali {{ $t->reopen_count }} kali">
                              <i class="fas fa-undo-alt me-1"></i> {{ $t->reopen_count }}x
                            </span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>
                          <a href="{{ route('tickets.show', $t) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye"></i> Lihat
                          </a>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="8" class="text-center py-4">
                          <i class="fas fa-check-circle fa-3x text-muted mb-2"></i>
                          <p class="text-muted mb-0">Belum ada tiket yang diselesaikan</p>
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab 3: Semua Tiket Saya -->
            <div class="tab-pane fade" id="all" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>No</th>
                      <th>Ticket</th>
                      <th>Judul</th>
                      <th>Status</th>
                      <th>Prioritas</th>
                      <th>Dibuat</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($ticketsMy ?? [] as $t)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                          <a href="{{ route('tickets.show', $t) }}" class="fw-bold text-decoration-none">
                            {{ $t->ticket_number }}
                          </a>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($t->title), 50) }}</td>
                        <td>
                          <span class="badge bg-{{ $t->getStatusBadgeAttribute() }}">
                            {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                          </span>
                        </td>
                        <td>
                          <span class="badge bg-{{ $t->getPriorityBadgeAttribute() }}">
                            {{ ucfirst($t->priority) }}
                          </span>
                        </td>
                        <td>
                          <small>{{ $t->created_at->format('d M Y') }}</small>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <a href="{{ route('tickets.show', $t) }}" class="btn btn-outline-primary">
                              <i class="fas fa-eye"></i> Detail
                            </a>
                            @if(!in_array($t->status, ['resolved', 'closed']))
                              <button type="button" class="btn btn-outline-success resolve-btn"
                                      data-id="{{ $t->id }}"
                                      data-number="{{ $t->ticket_number }}"
                                      data-title="{{ addslashes(strip_tags($t->title)) }}">
                                <i class="fas fa-check-circle"></i> Resolve
                              </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center py-4">
                          <i class="fas fa-ticket-alt fa-3x text-muted mb-2"></i>
                          <p class="text-muted mb-0">Belum ada tiket yang ditugaskan</p>
                        </table>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</section>

{{-- RESOLVE MODAL --}}
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="resolveForm" method="POST" action="" enctype="multipart/form-data">
      @csrf
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="fas fa-check-circle me-2"></i>Selesaikan Ticket <span id="resolveTicketNumber" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="status" value="resolved">
          <input type="hidden" name="ticket_id" id="resolveTicketId" />

          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Ticket:</strong> <span id="resolveTicketTitle"></span>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold text-success">
                <i class="fas fa-sticky-note me-1"></i>Catatan Solusi <span class="text-danger">*</span>
            </label>
            <textarea name="resolution_notes"
                      id="resolution_notes"
                      class="form-control"
                      rows="5"
                      required
                      placeholder="Jelaskan solusi yang diberikan untuk ticket ini..."></textarea>
            <div class="form-text">
              <i class="fas fa-info-circle me-1"></i>
              Catatan ini akan dicatat sebagai history ticket dan akan terlihat oleh pembuat ticket.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold text-success">
                <i class="fas fa-paperclip me-1"></i>Lampiran Solusi (Opsional)
            </label>
            <input type="file"
                   name="resolution_attachments[]"
                   class="form-control"
                   multiple
                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
            <div class="form-text">
              <i class="fas fa-info-circle me-1"></i>
              Bisa upload multiple file. Maksimal 5MB per file.
            </div>
          </div>

          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" name="notify_user" id="notifyUser" value="1" checked>
            <label for="notifyUser" class="form-check-label">
              <i class="fas fa-envelope me-1 text-success"></i>
              Kirim notifikasi email ke pembuat ticket bahwa ticket telah selesai
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times me-1"></i> Batal
          </button>
          <button type="submit" class="btn btn-success" id="submitResolveBtn">
              <i class="fas fa-check-circle me-1"></i> Selesaikan Ticket
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('styles')
<style>
  .nav-tabs .nav-link {
    border-bottom: 2px solid transparent;
    font-weight: 500;
    transition: all 0.2s;
  }
  .nav-tabs .nav-link.active {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
  }
  .nav-tabs .badge {
    font-size: 0.65rem;
    padding: 0.25em 0.5em;
  }
  .table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transition: background-color 0.2s;
  }
  .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  .btn-group {
    gap: 4px;
  }
  .info-card {
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  .progress {
    background-color: #e9ecef;
    border-radius: 10px;
  }
  .progress-bar {
    border-radius: 10px;
    transition: width 0.5s ease;
  }
  .table-danger {
    background-color: #fff5f5 !important;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Chart Trend
  const labels = {!! json_encode($days ?? []) !!};
  const chartData = {!! json_encode($counts ?? []) !!};

  if (labels.length && chartData.length) {
    const ctx = document.getElementById('ticketsTrend').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Jumlah tiket per hari',
          data: chartData,
          fill: true,
          tension: 0.3,
          backgroundColor: 'rgba(13, 110, 253, 0.1)',
          borderColor: '#0d6efd',
          borderWidth: 2,
          pointBackgroundColor: '#0d6efd',
          pointBorderColor: '#fff',
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `Tiket: ${context.raw}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              precision: 0
            },
            title: {
              display: true,
              text: 'Jumlah Tiket'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Tanggal'
            }
          }
        }
      }
    });
  }

  // Initialize tabs
  document.addEventListener('DOMContentLoaded', function() {
    var triggerTabList = [].slice.call(document.querySelectorAll('#myTicketsTab button'));
    triggerTabList.forEach(function (triggerEl) {
      var tabTrigger = new bootstrap.Tab(triggerEl);
      triggerEl.addEventListener('click', function (event) {
        event.preventDefault();
        tabTrigger.show();
      });
    });
  });

  // ==================== RESOLVE MODAL FUNCTION ====================
  document.addEventListener('DOMContentLoaded', function() {
    const resolveButtons = document.querySelectorAll('.resolve-btn');

    resolveButtons.forEach(function(button) {
      button.addEventListener('click', function(e) {
        e.preventDefault();

        const ticketId = this.getAttribute('data-id');
        const ticketNumber = this.getAttribute('data-number');
        const ticketTitle = this.getAttribute('data-title');

        console.log('Resolve button clicked:', ticketId, ticketNumber);

        document.getElementById('resolveTicketId').value = ticketId;
        document.getElementById('resolveTicketNumber').textContent = ticketNumber;
        document.getElementById('resolveTicketTitle').textContent = ticketTitle;

        const resolveForm = document.getElementById('resolveForm');
        if (resolveForm) {
          resolveForm.action = '/it/tickets/' + ticketId + '/status';
          console.log('Form action set to:', resolveForm.action);
        }

        document.getElementById('resolution_notes').value = '';

        const resolveModal = new bootstrap.Modal(document.getElementById('resolveModal'));
        resolveModal.show();
      });
    });
  });
</script>
@endpush
