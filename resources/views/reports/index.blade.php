@extends('layouts.app')

@section('title', 'Laporan Ticketing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/reports-index.css') }}">
@endpush

@section('content')
<div class="rpt-page container-fluid px-4">

    {{-- ===================== HEADER ===================== --}}
    <div class="rpt-header mb-4">
        <div class="rpt-header__left">
            <h1 class="rpt-header__title">
                <i class="fas fa-chart-bar"></i>
                Laporan Ticketing
            </h1>
            <p class="rpt-header__sub">Analisis dan performa sistem helpdesk</p>
        </div>
        <div class="rpt-header__actions">
            <a href="{{ route('reports.export.pdf') }}?{{ http_build_query(request()->only(['start_date','end_date','category_id','status','user_id','assigned_to'])) }}"
               class="rpt-btn-export">
                <span class="rpt-btn-export__dot rpt-btn-export__dot--pdf"></span>
                Export PDF
            </a>
            <a href="{{ route('reports.export.excel') }}?{{ http_build_query(request()->only(['start_date','end_date','category_id','status','user_id','assigned_to'])) }}"
               class="rpt-btn-export">
                <span class="rpt-btn-export__dot rpt-btn-export__dot--excel"></span>
                Export Excel
            </a>
        </div>
    </div>

    {{-- ===================== FILTER ===================== --}}
    <div class="rpt-card mb-4">
        <div class="rpt-card__body">
            <form action="{{ route('reports.index') }}" method="GET" id="filterForm">
                <div class="rpt-filter-grid">
                    <div class="rpt-filter-field">
                        <label class="rpt-filter-label">
                            <i class="fas fa-calendar-alt"></i> Tanggal mulai
                        </label>
                        <input type="date" class="rpt-input" name="start_date"
                               value="{{ $startDate }}" max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="rpt-filter-field">
                        <label class="rpt-filter-label">
                            <i class="fas fa-calendar-check"></i> Tanggal akhir
                        </label>
                        <input type="date" class="rpt-input" name="end_date"
                               value="{{ $endDate }}" max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="rpt-filter-field">
                        <label class="rpt-filter-label">
                            <i class="fas fa-tag"></i> Kategori
                        </label>
                        <select class="rpt-input" name="category_id">
                            <option value="">Semua kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rpt-filter-field">
                        <label class="rpt-filter-label">
                            <i class="fas fa-list"></i> Status
                        </label>
                        <select class="rpt-input" name="status">
                            <option value="">Semua status</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(auth()->user()->role === 'admin')
                    <div class="rpt-filter-field">
                        <label class="rpt-filter-label">
                            <i class="fas fa-user"></i> User
                        </label>
                        <select class="rpt-input" name="user_id">
                            <option value="">Semua user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rpt-filter-field">
                        <label class="rpt-filter-label">
                            <i class="fas fa-user-check"></i> Assigned to
                        </label>
                        <select class="rpt-input" name="assigned_to">
                            <option value="">Semua IT staff</option>
                            @foreach($itStaff as $staff)
                                <option value="{{ $staff->id }}" {{ $assignedTo == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="rpt-filter-field rpt-filter-field--actions">
                        <label class="rpt-filter-label">&nbsp;</label>
                        <div class="rpt-filter-btns">
                            <button type="submit" class="rpt-btn-primary">
                                <i class="fas fa-filter"></i> Terapkan
                            </button>
                            <a href="{{ route('reports.index') }}" class="rpt-btn-outline">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                <div class="rpt-period-badge">
                    <i class="fas fa-calendar-week"></i>
                    Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}
                    – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== METRIC CARDS ===================== --}}
    <div class="rpt-metrics mb-4">

        <div class="rpt-metric-card">
            <div class="rpt-metric-card__body">
                <p class="rpt-metric-card__label">Total tiket</p>
                <h2 class="rpt-metric-card__value">{{ number_format($summary['total_tickets']) }}</h2>
                @if(isset($summary['total_delta']))
                <p class="rpt-metric-card__delta rpt-metric-card__delta--up">
                    <i class="fas fa-arrow-up"></i> {{ $summary['total_delta'] }}% dari bulan lalu
                </p>
                @endif
            </div>
            <div class="rpt-metric-card__icon rpt-metric-card__icon--teal">
                <i class="fas fa-ticket-alt"></i>
            </div>
        </div>

        <div class="rpt-metric-card">
            <div class="rpt-metric-card__body">
                <p class="rpt-metric-card__label">Open tiket</p>
                <h2 class="rpt-metric-card__value">{{ number_format($summary['open_tickets']) }}</h2>
                @if(isset($summary['open_delta']))
                <p class="rpt-metric-card__delta rpt-metric-card__delta--warn">
                    <i class="fas fa-arrow-up"></i> {{ $summary['open_delta'] }} dari minggu lalu
                </p>
                @endif
            </div>
            <div class="rpt-metric-card__icon rpt-metric-card__icon--amber">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="rpt-metric-card">
            <div class="rpt-metric-card__body">
                <p class="rpt-metric-card__label">Resolved</p>
                <h2 class="rpt-metric-card__value">{{ number_format($summary['resolved_tickets']) }}</h2>
                @if(isset($summary['resolved_delta']))
                <p class="rpt-metric-card__delta rpt-metric-card__delta--up">
                    <i class="fas fa-arrow-up"></i> {{ $summary['resolved_delta'] }}% dari bulan lalu
                </p>
                @endif
            </div>
            <div class="rpt-metric-card__icon rpt-metric-card__icon--green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="rpt-metric-card">
            <div class="rpt-metric-card__body">
                <p class="rpt-metric-card__label">Resolution rate</p>
                <h2 class="rpt-metric-card__value">{{ $summary['resolution_rate'] }}%</h2>
                <p class="rpt-metric-card__delta rpt-metric-card__delta--info">
                    <i class="fas fa-bullseye"></i> Target: 80%
                </p>
            </div>
            <div class="rpt-metric-card__icon rpt-metric-card__icon--blue">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

    </div>

    {{-- ===================== CHARTS ===================== --}}
    <div class="rpt-charts mb-4">

        {{-- Trend Chart --}}
        <div class="rpt-card rpt-charts__main">
            <div class="rpt-card__head">
                <span class="rpt-card__head-title">
                    <i class="fas fa-chart-line"></i>
                    Tren tiket 6 bulan terakhir
                </span>
            </div>
            <div class="rpt-card__body">
                <div class="rpt-chart-legend" id="trendLegend" aria-hidden="true"></div>
                <div class="rpt-chart-wrap">
                    <canvas id="monthlyChart"
                            role="img"
                            aria-label="Grafik tren tiket per bulan — total, open, dan resolved.">
                    </canvas>
                </div>
            </div>
        </div>

        {{-- Category Donut --}}
        <div class="rpt-card rpt-charts__side">
            <div class="rpt-card__head">
                <span class="rpt-card__head-title">
                    <i class="fas fa-chart-pie"></i>
                    Distribusi kategori
                </span>
            </div>
            <div class="rpt-card__body">
                <div class="rpt-donut-wrap">
                    <canvas id="categoryChart"
                            role="img"
                            aria-label="Donut chart distribusi tiket per kategori.">
                    </canvas>
                </div>
                <div class="rpt-donut-legend" id="categoryLegend" aria-hidden="true"></div>
            </div>
        </div>

    </div>

    {{-- ===================== TABS TABLE ===================== --}}
    <div class="rpt-card">
        <div class="rpt-tabs" role="tablist">
            <button class="rpt-tab rpt-tab--active" role="tab"
                    id="tab-tickets" aria-controls="pane-tickets" aria-selected="true"
                    data-target="pane-tickets">
                <i class="fas fa-ticket-alt"></i>
                Tickets
                <span class="rpt-tab__count">{{ $tickets->total() }}</span>
            </button>
            <button class="rpt-tab" role="tab"
                    id="tab-history" aria-controls="pane-history" aria-selected="false"
                    data-target="pane-history">
                <i class="fas fa-history"></i>
                Activity history
                <span class="rpt-tab__count">{{ $histories->total() }}</span>
            </button>
        </div>

        {{-- TICKETS PANE --}}
        <div class="rpt-pane" id="pane-tickets" role="tabpanel" aria-labelledby="tab-tickets">
            <div class="rpt-table-wrap">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Judul</th>
                            <th>User</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Assigned to</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="rpt-ticket-link">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="rpt-table__title-cell">
                                {{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 48) }}
                            </td>
                            <td>
                                <div class="rpt-user-cell">
                                    <div class="rpt-avatar rpt-avatar--{{ $loop->index % 4 }}">
                                        {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <span>{{ $ticket->user->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="rpt-badge rpt-badge--category">
                                    {{ $ticket->category->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="rpt-badge rpt-badge--{{ $ticket->getStatusBadgeAttribute() }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="rpt-badge rpt-badge--priority-{{ $ticket->priority }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="rpt-table__assigned">
                                {{ $ticket->assignedTo->name ?? '—' }}
                            </td>
                            <td>
                                <div class="rpt-table__date">{{ $ticket->created_at->format('d/m/Y') }}</div>
                                <div class="rpt-table__time">{{ $ticket->created_at->format('H:i') }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="rpt-btn-view" aria-label="Lihat tiket {{ $ticket->ticket_number }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="rpt-empty">
                                <i class="fas fa-inbox"></i>
                                <p>Tidak ada tiket ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
            <div class="rpt-pagination">
                <div class="rpt-pagination__info">
                    Menampilkan {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}
                    dari {{ $tickets->total() }} tiket
                </div>
                {{ $tickets->appends(request()->except('page'))->links('vendor.pagination.reports') }}
            </div>
            @endif
        </div>

        {{-- HISTORY PANE --}}
        <div class="rpt-pane rpt-pane--hidden" id="pane-history" role="tabpanel" aria-labelledby="tab-history">
            <div class="rpt-table-wrap">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Notes</th>
                            <th>Timestamp</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $history)
                        @php
                            $actionColors = [
                                'created'        => 'success',
                                'updated'        => 'info',
                                'assigned'       => 'warning',
                                'taken'          => 'primary',
                                'status_changed' => 'secondary',
                                'reopened'       => 'warning',
                                'resolved'       => 'success',
                            ];
                            $actionColor = $actionColors[$history->action] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>
                                @if($history->ticket)
                                    <a href="{{ route('tickets.show', $history->ticket) }}" class="rpt-ticket-link">
                                        {{ $history->ticket->ticket_number }}
                                    </a>
                                @else
                                    <span class="rpt-table__muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="rpt-badge rpt-badge--{{ $actionColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                </span>
                            </td>
                            <td>
                                <div class="rpt-user-cell">
                                    <div class="rpt-avatar rpt-avatar--{{ $loop->index % 4 }}">
                                        {{ strtoupper(substr($history->user->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <span>{{ $history->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="rpt-history-notes">
                                    {{ $history->notes }}
                                    @if($history->meta)
                                        @php
                                            $meta    = json_decode($history->meta, true);
                                            $changes = [];
                                            if (is_array($meta)) {
                                                foreach ($meta as $key => $val) {
                                                    if (is_array($val) && isset($val['old'], $val['new'])) {
                                                        $changes[] = "$key: {$val['old']} → {$val['new']}";
                                                    }
                                                }
                                            }
                                        @endphp
                                        @foreach($changes as $change)
                                            <div class="rpt-history-change">• {{ $change }}</div>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="rpt-table__date">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                <div class="rpt-table__time">{{ $history->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-center">
                                @if($history->ticket)
                                    <a href="{{ route('tickets.show', $history->ticket) }}"
                                       class="rpt-btn-view" aria-label="Lihat tiket">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="rpt-empty">
                                <i class="fas fa-history"></i>
                                <p>Tidak ada activity history ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($histories->hasPages())
            <div class="rpt-pagination">
                <div class="rpt-pagination__info">
                    Menampilkan {{ $histories->firstItem() }}–{{ $histories->lastItem() }}
                    dari {{ $histories->total() }} aktivitas
                </div>
                {{ $histories->appends(request()->except('history_page'))->links('vendor.pagination.reports') }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    /* ─── Tab switching ─────────────────────────────────────── */
    document.querySelectorAll('.rpt-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.rpt-tab').forEach(function (b) {
                b.classList.remove('rpt-tab--active');
                b.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('.rpt-pane').forEach(function (p) {
                p.classList.add('rpt-pane--hidden');
            });

            btn.classList.add('rpt-tab--active');
            btn.setAttribute('aria-selected', 'true');
            document.getElementById(btn.dataset.target).classList.remove('rpt-pane--hidden');
        });
    });

    /* ─── Chart helpers ─────────────────────────────────────── */
    var COLORS = {
        teal:   '#1D9E75',
        tealBg: 'rgba(29,158,117,0.10)',
        amber:  '#EF9F27',
        green:  '#639922',
        blue:   '#185FA5',
        purple: '#7F77DD',
        gray:   '#B4B2A9',
    };

    function gridColor() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'rgba(255,255,255,0.07)'
            : 'rgba(0,0,0,0.06)';
    }
    function tickColor() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? '#888' : '#999';
    }

    /* ─── Fetch & render charts ─────────────────────────────── */
    var analyticsUrl = '{{ route("reports.analytics") }}'
        + '?start_date={{ $startDate }}&end_date={{ $endDate }}';

    fetch(analyticsUrl)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            renderMonthlyChart(data.monthly_data);
            renderCategoryChart(data.category_data);
        })
        .catch(function (err) {
            console.error('[Reports] Chart data error:', err);
        });

    /* ─── Monthly trend line chart ──────────────────────────── */
    function renderMonthlyChart(monthlyData) {
        var labels      = monthlyData.map(function (d) { return d.month; });
        var totalData   = monthlyData.map(function (d) { return d.total; });
        var openData    = monthlyData.map(function (d) { return d.open; });
        var resolvedData = monthlyData.map(function (d) { return d.resolved; });

        var datasets = [
            {
                label: 'Total',
                data: totalData,
                borderColor: COLORS.teal,
                backgroundColor: COLORS.tealBg,
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: COLORS.teal,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
            {
                label: 'Open',
                data: openData,
                borderColor: COLORS.amber,
                backgroundColor: 'transparent',
                tension: 0.4,
                fill: false,
                borderWidth: 2,
                borderDash: [6, 4],
                pointBackgroundColor: COLORS.amber,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
            {
                label: 'Resolved',
                data: resolvedData,
                borderColor: COLORS.green,
                backgroundColor: 'transparent',
                tension: 0.4,
                fill: false,
                borderWidth: 2,
                pointStyle: 'rect',
                pointBackgroundColor: COLORS.green,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ];

        /* Build custom HTML legend */
        var legendEl = document.getElementById('trendLegend');
        var styles   = [
            'background:' + COLORS.teal,
            'background:' + COLORS.amber + '; outline: 1px dashed ' + COLORS.amber,
            'background:' + COLORS.green,
        ];
        datasets.forEach(function (ds, i) {
            var item  = document.createElement('span');
            item.className = 'rpt-legend-item';
            item.innerHTML = '<span class="rpt-legend-swatch" style="' + styles[i] + '"></span>' + ds.label;
            legendEl.appendChild(item);
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: gridColor() },
                        ticks: { color: tickColor(), font: { size: 11 } },
                        border: { dash: [4, 4] },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor() },
                        ticks: { color: tickColor(), font: { size: 11 }, precision: 0 },
                        border: { dash: [4, 4] },
                    },
                },
            },
        });
    }

    /* ─── Category donut chart ──────────────────────────────── */
    function renderCategoryChart(categoryData) {
        var palette = [
            COLORS.teal, COLORS.blue, COLORS.amber,
            COLORS.purple, COLORS.gray,
            '#D4537E', '#E24B4A',
        ];

        var labels = categoryData.map(function (d) { return d.name; });
        var data   = categoryData.map(function (d) { return d.count; });
        var total  = data.reduce(function (a, b) { return a + b; }, 0);
        var colors = palette.slice(0, labels.length);

        var dark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: dark ? '#1a1a1a' : '#fff',
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var pct = total > 0
                                    ? Math.round((ctx.parsed / total) * 100)
                                    : 0;
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            },
                        },
                    },
                },
            },
        });

        /* Build custom HTML legend with inline bars */
        var legendEl = document.getElementById('categoryLegend');
        var maxCount = Math.max.apply(null, data);

        labels.forEach(function (label, i) {
            var pct     = total > 0 ? Math.round((data[i] / total) * 100) : 0;
            var barPct  = maxCount > 0 ? Math.round((data[i] / maxCount) * 100) : 0;
            var row     = document.createElement('div');
            row.className = 'rpt-donut-row';
            row.innerHTML =
                '<div class="rpt-donut-row__left">'
                +   '<span class="rpt-donut-row__swatch" style="background:' + colors[i] + '"></span>'
                +   '<span class="rpt-donut-row__name">' + label + '</span>'
                + '</div>'
                + '<div class="rpt-donut-row__bar-wrap">'
                +   '<div class="rpt-donut-row__bar" style="width:' + barPct + '%;background:' + colors[i] + '"></div>'
                + '</div>'
                + '<span class="rpt-donut-row__pct">' + pct + '%</span>';
            legendEl.appendChild(row);
        });
    }
})();
</script>
@endpush
