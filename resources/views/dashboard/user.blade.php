@extends('layouts.app')

@section('title', 'Dashboard - My Tickets')

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
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Saya
                </h1>
                <p class="dash-header-subtitle">Kelola dan pantau ticket permintaan Anda</p>
            </div>
            <div class="dash-header-widgets">
                <a href="{{ route('tickets.create') }}" class="btn-header">
                    <i class="fas fa-plus-circle me-1"></i> Buat Ticket Baru
                </a>
            </div>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="stat-grid" style="grid-template-columns: repeat(3, 1fr);">

        {{-- Total Ticket --}}
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Total Ticket Saya</div>
                <div class="stat-card-value">{{ $myTicketsCount ?? 0 }}</div>
                <div class="stat-card-trend neutral">Semua ticket yang Anda buat</div>
            </div>
            <div class="stat-card-icon indigo">
                <i class="fas fa-ticket-alt"></i>
            </div>
        </div>

        {{-- Ticket Aktif --}}
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Ticket Aktif</div>
                <div class="stat-card-value warn">{{ $myOpenCount ?? 0 }}</div>
                <div class="stat-card-trend neutral">Open / In Queue / In Progress</div>
            </div>
            <div class="stat-card-icon amber">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        {{-- Status Global --}}
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Status Terbanyak (Global)</div>
                @if(!empty($statusCounts))
                    <div class="mt-2">
                        @php
                            $statusIcons = [
                                'open'        => '📋',
                                'in_queue'    => '⏳',
                                'in_progress' => '⚙️',
                                'resolved'    => '✅',
                                'closed'      => '📦',
                            ];
                        @endphp
                        @foreach($statusCounts as $st => $cnt)
                            <div class="d-flex justify-content-between" style="font-size:.78rem; margin-bottom:4px;">
                                <span style="color:var(--text-secondary)">
                                    {{ $statusIcons[$st] ?? '📌' }} {{ str_replace('_', ' ', ucfirst($st)) }}
                                </span>
                                <strong style="color:var(--accent-primary)">{{ $cnt }}</strong>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="font-size:.78rem; color:var(--text-muted); margin-top:8px;">Belum ada data status.</div>
                @endif
            </div>
            <div class="stat-card-icon teal">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>

    </div>

    {{-- ── MAIN GRID: Tabel + Sidebar ── --}}
    <div class="main-grid">

        {{-- ── TABEL TICKET TERBARU ── --}}
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title">
                    <i class="fas fa-history"></i> Ticket Terbaru Saya
                </h5>
                <a href="{{ route('tickets.index', ['mine' => 1]) }}" class="btn-glass">
                    <i class="fas fa-eye me-1"></i> Lihat Semua
                </a>
            </div>

            <div class="table-scroll">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ticket</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMyTickets as $t)
                        @php
                            $priorityBadge = [
                                'low'    => ['cls' => 'badge-priority-low',    'icon' => 'fas fa-arrow-down'],
                                'medium' => ['cls' => 'badge-priority-medium', 'icon' => 'fas fa-minus'],
                                'high'   => ['cls' => 'badge-priority-high',   'icon' => 'fas fa-arrow-up'],
                                'urgent' => ['cls' => 'badge-priority-urgent', 'icon' => 'fas fa-exclamation-triangle'],
                            ];
                            $statusBadge = [
                                'open'        => ['cls' => 'badge-status-open',        'icon' => 'fas fa-envelope'],
                                'in_queue'    => ['cls' => 'badge-status-open',        'icon' => 'fas fa-clock'],
                                'in_progress' => ['cls' => 'badge-status-in_progress', 'icon' => 'fas fa-cogs'],
                                'resolved'    => ['cls' => 'badge-status-resolved',    'icon' => 'fas fa-check-circle'],
                                'closed'      => ['cls' => 'badge-status-closed',      'icon' => 'fas fa-archive'],
                            ];
                            $pb = $priorityBadge[$t->priority] ?? ['cls'=>'badge-priority-medium','icon'=>'fas fa-flag'];
                            $sb = $statusBadge[$t->status]     ?? ['cls'=>'badge-status-open','icon'=>'fas fa-question'];
                        @endphp
                        <tr>
                            <td style="font-weight:700; color:var(--accent-primary)">{{ $loop->iteration }}</td>

                            <td>
                                <a href="{{ route('tickets.show', $t) }}" class="ticket-link">
                                    {{ $t->ticket_number }}
                                </a>
                            </td>

                            <td>
                                <span class="ticket-title-text">
                                    {{ \Illuminate\Support\Str::limit($t->title, 45) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge-pill" style="background:#ede9fe; color:#4338ca;">
                                    <i class="fas fa-tag" style="font-size:.65rem"></i>
                                    {{ $t->category->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge-pill {{ $pb['cls'] }}">
                                    <i class="{{ $pb['icon'] }}" style="font-size:.65rem"></i>
                                    {{ ucfirst($t->priority) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge-pill {{ $sb['cls'] }}">
                                    <i class="{{ $sb['icon'] }}" style="font-size:.65rem"></i>
                                    {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <div class="text-small">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $t->created_at->format('d M Y') }}
                                </div>
                                <div style="font-size:.7rem; color:var(--text-muted); margin-top:2px;">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $t->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <td style="text-align:center">
                                <div style="display:flex; gap:4px; justify-content:center;">
                                    <a href="{{ route('tickets.show', $t) }}"
                                       class="btn-action"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($t->status, ['open','in_queue']))
                                        <a href="{{ route('tickets.edit', $t) }}"
                                           class="btn-action btn-action--warn"
                                           title="Edit Ticket">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-ticket-alt"></i>
                                    <p>Anda belum membuat ticket</p>
                                    <a href="{{ route('tickets.create') }}" class="btn-glass mt-2">
                                        <i class="fas fa-plus-circle me-1"></i> Buat Ticket Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── SIDEBAR ── --}}
        <div class="sidebar-stack">

            {{-- Chart --}}
            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title">
                        <i class="fas fa-chart-bar"></i> Tiket Per Bulan
                    </h5>
                    <span style="font-size:.7rem; color:var(--text-muted);">6 bulan terakhir</span>
                </div>
                <div class="glass-card-body">
                    <div style="position:relative; height:200px;">
                        <canvas id="userTicketsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title">
                        <i class="fas fa-lightbulb"></i> Tips Penting
                    </h5>
                </div>
                <div class="glass-card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach([
                            ['fas fa-check-circle', 'Jelaskan masalah secara singkat dan sertakan langkah reproduksi bila ada.'],
                            ['fas fa-tag',          'Tambahkan kategori dan prioritas dengan tepat agar admin dapat menindaklanjuti cepat.'],
                            ['fas fa-bell',         'Periksa notifikasi untuk mengetahui update progress ticket Anda.'],
                            ['fas fa-comment',      'Jangan ragu berkomentar jika ada informasi tambahan yang perlu disampaikan.'],
                        ] as [$icon, $text])
                        <li style="display:flex; align-items:flex-start; gap:8px; margin-bottom:.85rem; font-size:.8rem; color:var(--text-secondary); line-height:1.5;">
                            <i class="{{ $icon }}" style="color:var(--accent-primary); margin-top:2px; flex-shrink:0;"></i>
                            <span>{{ $text }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>{{-- /sidebar-stack --}}
    </div>{{-- /main-grid --}}

</div>{{-- /dash-wrapper --}}

{{-- ── Tambahan CSS khusus halaman user ── --}}
<style>
    /* Override: hapus fixed bg admin agar background page tetap */
    .dash-wrapper { min-height: unset; }

    /* Action button bulat */
    .btn-action {
        width: 30px; height: 30px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: var(--bg-surface);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .75rem; color: var(--accent-primary);
        text-decoration: none;
        transition: background .15s;
    }
    .btn-action:hover { background: #dbeafe; color: var(--accent-primary); }
    .btn-action--warn { color: var(--accent-amber); }
    .btn-action--warn:hover { background: #fef3c7; }

    /* Header button putih */
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
    .btn-header:hover { background: rgba(255,255,255,.28); color: #fff; }

    /* Stat grid 3 kolom di layar kecil */
    @media (max-width: 768px) {
        .stat-grid { grid-template-columns: 1fr 1fr !important; }
        .stat-grid .stat-card:last-child { grid-column: 1 / -1; }
        .main-grid { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 480px) {
        .stat-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const months     = {!! json_encode($months ?? []) !!};
    const dataCounts = {!! json_encode($ticketCountsByMonth ?? []) !!};
    const canvas     = document.getElementById('userTicketsChart');

    if (!canvas) return;

    if (months.length && dataCounts.length) {
        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Jumlah Tiket',
                    data: dataCounts,
                    backgroundColor: 'rgba(29,111,184,0.15)',
                    borderColor: '#1d6fb8',
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
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.raw} tiket`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, stepSize: 1, font: { size: 10 }, color: '#94a3b8' },
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
    } else {
        const noData = document.createElement('div');
        noData.style.cssText = 'text-align:center; padding:2rem; color:#94a3b8; font-size:.8rem;';
        noData.innerHTML = '<i class="fas fa-chart-bar" style="font-size:1.5rem; display:block; margin-bottom:.5rem; opacity:.4;"></i>Tidak ada data chart';
        canvas.parentNode.replaceChild(noData, canvas);
    }
});
</script>
@endpush
