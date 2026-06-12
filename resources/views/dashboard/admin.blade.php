@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}">
@endpush

{{-- Pre-compute semua nilai PHP sebelum render --}}
@php
    $slaRate      = $slaComplianceRate ?? 0;
    $slaCls       = $slaRate >= 90 ? 'good' : ($slaRate >= 70 ? 'medium' : 'bad');
    $catLabels    = isset($categoryCounts) ? $categoryCounts->pluck('name')          : collect(['Hardware','Software','Network','Email','Lainnya']);
    $catData      = isset($categoryCounts) ? $categoryCounts->pluck('tickets_count') : collect([88,77,63,34,22]);
    $sparkResVal  = isset($summary) ? ($summary['resolution_rate'] ?? 78) : 78;
    $sparkTimeVal = $avgResolutionTime ?? 4.2;
    $jsMonths     = $months ?? ['Jul','Agu','Sep','Okt','Nov','Des'];
    $jsTotals     = $ticketCountsByMonth ?? [38,52,45,61,74,67];
    $jsOpen       = $openCountsByMonth   ?? [12,18,14,22,28,47];
    $jsResolved   = $resolvedCountsByMonth ?? [24,30,29,36,42,34];
@endphp

@section('content')

<div class="dash-bg" aria-hidden="true"><div class="dash-bg-orb3"></div></div>

<div class="dash-wrapper">

    {{-- HEADER --}}
    <header class="dash-header">
        <div class="dash-header-inner">
            <div>
                <h1 class="dash-header-title">
                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i>Admin Dashboard
                </h1>
                <p class="dash-header-subtitle">Overview sistem ticketing RS Intan Husada</p>
            </div>
            <div class="dash-header-widgets">
                <div class="dash-clock" aria-label="Jam dan tanggal sekarang">
                    <div class="dash-clock-time" id="liveClock" aria-live="polite">--:--:--</div>
                    <div class="dash-clock-date" id="liveDate">-- --- ----</div>
                </div>
                <div class="dash-weather" aria-label="Informasi cuaca">
                    <div class="dash-weather-icon" id="wxIcon" aria-hidden="true">⛅</div>
                    <div class="dash-weather-body">
                        <div class="dash-weather-temp" id="wxTemp">--°C</div>
                        <div class="dash-weather-loc"  id="wxLoc">Memuat lokasi…</div>
                        <div class="dash-weather-desc" id="wxDesc">--</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- QUICK ACTIONS --}}
    <nav class="quick-actions-grid" aria-label="Aksi cepat">
        <a href="{{ route('tickets.index') }}"  class="quick-action-item">
            <div class="quick-action-icon indigo"><i class="fas fa-ticket-alt"></i></div>
            <div><div class="quick-action-label">Kelola Tickets</div><div class="quick-action-sub">Lihat dan kelola semua</div></div>
        </a>
        <a href="{{ route('users.index') }}" class="quick-action-item">
            <div class="quick-action-icon teal"><i class="fas fa-users-cog"></i></div>
            <div><div class="quick-action-label">Manajemen User</div><div class="quick-action-sub">Kelola pengguna dan akses</div></div>
        </a>
        <a href="{{ route('tickets.create') }}" class="quick-action-item">
            <div class="quick-action-icon emerald"><i class="fas fa-plus-circle"></i></div>
            <div><div class="quick-action-label">Buat Ticket</div><div class="quick-action-sub">Buat ticket baru</div></div>
        </a>
        <a href="{{ route('reports.index') }}" class="quick-action-item">
            <div class="quick-action-icon amber"><i class="fas fa-chart-bar"></i></div>
            <div><div class="quick-action-label">Laporan</div><div class="quick-action-sub">Analisis performa sistem</div></div>
        </a>
    </nav>

    {{-- STAT ROW 1 --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Total Tickets</div>
                <div class="stat-card-value">{{ $totalTickets ?? 0 }}</div>
                @if(isset($totalChange))
                <div class="stat-card-trend {{ $totalChange['trend'] === 'up' ? 'up-good' : ($totalChange['trend'] === 'down' ? 'down-bad' : 'neutral') }}">
                    <i class="fas fa-arrow-{{ $totalChange['trend'] === 'up' ? 'up' : ($totalChange['trend'] === 'down' ? 'down' : 'minus') }}"></i>
                    {{ abs($totalChange['value']) }}% dari bulan lalu
                </div>
                @endif
            </div>
            <div class="stat-card-icon indigo"><i class="fas fa-ticket-alt"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Open / In Progress</div>
                <div class="stat-card-value warn">{{ $openTickets ?? 0 }}</div>
                @if(isset($openChange))
                <div class="stat-card-trend {{ $openChange['trend'] === 'up' ? 'up-bad' : ($openChange['trend'] === 'down' ? 'down-good' : 'neutral') }}">
                    <i class="fas fa-arrow-{{ $openChange['trend'] === 'up' ? 'up' : ($openChange['trend'] === 'down' ? 'down' : 'minus') }}"></i>
                    {{ abs($openChange['value']) }}% dari bulan lalu
                </div>
                @endif
            </div>
            <div class="stat-card-icon amber"><i class="fas fa-spinner"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Resolved</div>
                <div class="stat-card-value">{{ $resolvedTickets ?? 0 }}</div>
                @if(isset($resolvedChange))
                <div class="stat-card-trend {{ $resolvedChange['trend'] === 'up' ? 'up-good' : ($resolvedChange['trend'] === 'down' ? 'down-bad' : 'neutral') }}">
                    <i class="fas fa-arrow-{{ $resolvedChange['trend'] === 'up' ? 'up' : ($resolvedChange['trend'] === 'down' ? 'down' : 'minus') }}"></i>
                    {{ abs($resolvedChange['value']) }}% dari bulan lalu
                </div>
                @endif
            </div>
            <div class="stat-card-icon emerald"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Closed</div>
                <div class="stat-card-value">{{ $closedTickets ?? 0 }}</div>
                @if(isset($closedChange))
                <div class="stat-card-trend {{ $closedChange['trend'] === 'up' ? 'up-good' : ($closedChange['trend'] === 'down' ? 'down-bad' : 'neutral') }}">
                    <i class="fas fa-arrow-{{ $closedChange['trend'] === 'up' ? 'up' : ($closedChange['trend'] === 'down' ? 'down' : 'minus') }}"></i>
                    {{ abs($closedChange['value']) }}% dari bulan lalu
                </div>
                @endif
            </div>
            <div class="stat-card-icon slate"><i class="fas fa-archive"></i></div>
        </div>
    </div>

    {{-- STAT ROW 2 (ADMIN) --}}
    @if(isset($itStaffCount) && isset($ticketsUnassigned))
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Tiket Baru (7 hari)</div>
                <div class="stat-card-value">{{ $recentTicketsCount ?? 0 }}</div>
            </div>
            <div class="stat-card-icon teal"><i class="fas fa-inbox"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">IT Staff Aktif</div>
                <div class="stat-card-value">{{ $itStaffCount ?? 0 }}</div>
            </div>
            <div class="stat-card-icon primary"><i class="fas fa-user-shield"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Unassigned</div>
                <div class="stat-card-value warn">{{ $ticketsUnassigned ?? 0 }}</div>
                @if(isset($unassignedChange))
                <div class="stat-card-trend {{ $unassignedChange['trend'] === 'up' ? 'up-bad' : ($unassignedChange['trend'] === 'down' ? 'down-good' : 'neutral') }}">
                    <i class="fas fa-arrow-{{ $unassignedChange['trend'] === 'up' ? 'up' : ($unassignedChange['trend'] === 'down' ? 'down' : 'minus') }}"></i>
                    {{ abs($unassignedChange['value']) }}%
                </div>
                @endif
            </div>
            <div class="stat-card-icon amber"><i class="fas fa-inbox"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-info">
                <div class="stat-card-label">Overdue Tickets</div>
                <div class="stat-card-value danger">{{ $overdueTickets ?? 0 }}</div>
            </div>
            <div class="stat-card-icon rose"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    @endif

    {{-- CHARTS ROW --}}
    <div class="charts-row">
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title"><i class="fas fa-chart-line"></i>Tren tiket 6 bulan terakhir</h5>
            </div>
            <div class="glass-card-body">
                <div class="chart-legend" id="trendLegend" aria-hidden="true"></div>
                <div class="chart-wrap chart-wrap--tall">
                    <canvas id="monthlyTrendChart" role="img" aria-label="Tren tiket 6 bulan."></canvas>
                </div>
            </div>
        </div>
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title"><i class="fas fa-chart-pie"></i>Status tiket</h5>
            </div>
            <div class="glass-card-body">
                <div class="chart-wrap chart-wrap--donut" style="position:relative">
                    <canvas id="statusDonutChart" role="img" aria-label="Distribusi status tiket."></canvas>
                    <div class="donut-center" aria-hidden="true">
                        <div class="donut-center-val">{{ $totalTickets ?? 0 }}</div>
                        <div class="donut-center-lbl">total</div>
                    </div>
                </div>
                <div class="chart-legend chart-legend--center" id="statusLegend" aria-hidden="true"></div>
            </div>
        </div>
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title"><i class="fas fa-tags"></i>Tiket per kategori</h5>
            </div>
            <div class="glass-card-body">
                <div class="chart-wrap chart-wrap--tall">
                    <canvas id="categoryBarChart" role="img" aria-label="Tiket per kategori."></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- SPARKLINES --}}
    <div class="spark-row">
        <div class="spark-card">
            <div class="spark-card-label">Resolution rate</div>
            <div class="spark-card-val spark-card-val--green">{{ $sparkResVal }}%</div>
            <div class="chart-wrap chart-wrap--spark">
                <canvas id="sparkResolution" role="img" aria-label="Sparkline resolution rate."></canvas>
            </div>
        </div>
        <div class="spark-card">
            <div class="spark-card-label">Avg resolution time</div>
            <div class="spark-card-val spark-card-val--teal">
                {{ $sparkTimeVal }} <span class="spark-card-unit">jam</span>
            </div>
            <div class="chart-wrap chart-wrap--spark">
                <canvas id="sparkResTime" role="img" aria-label="Sparkline avg resolution time."></canvas>
            </div>
        </div>
    </div>

    {{-- SLA CARDS --}}
    <div class="sla-grid">
        <div class="sla-card">
            <div class="sla-card-label"><i class="fas fa-chart-pie"></i> SLA compliance rate</div>
            <div class="sla-big-value {{ $slaCls }}">{{ $slaRate }}%</div>
            <p class="sla-desc">Tiket selesai tepat waktu</p>
            <div class="sla-progress-track">
                <div class="sla-progress-fill {{ $slaCls }}" style="width:{{ $slaRate }}%"></div>
            </div>
        </div>
        <div class="sla-card">
            <div class="sla-card-label"><i class="fas fa-hourglass-half"></i> Avg resolution time</div>
            <div class="sla-big-value teal">{{ $sparkTimeVal }} <span class="sla-unit">jam</span></div>
            <p class="sla-desc">Rata-rata waktu penyelesaian</p>
        </div>
        <div class="sla-card">
            <div class="sla-card-label"><i class="fas fa-undo-alt"></i> Reopen rate</div>
            <div class="sla-big-value amber">{{ $reopenRate ?? 0 }}<span class="sla-unit">%</span></div>
            <p class="sla-desc">Ticket yang dibuka kembali</p>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="main-grid">

        {{-- Tickets table --}}
        <div class="glass-card">
            <div class="glass-card-header">
                <h5 class="glass-card-title"><i class="fas fa-clock"></i>Tickets terbaru</h5>
                <a href="{{ route('tickets.index') }}" class="btn-glass">
                    <i class="fas fa-arrow-right"></i>Lihat Semua
                </a>
            </div>
            @if(isset($recentTickets) && $recentTickets->count() > 0)
            <div class="table-scroll">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>Ticket #</th><th>Judul</th><th>User</th>
                            <th>Prioritas</th><th>Status</th><th>SLA</th><th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTickets as $ticket)
                        @php
                            $isOverdue = $ticket->sla_due_at
                                && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at))
                                && !in_array($ticket->status, ['resolved','closed']);
                        @endphp
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">{{ $ticket->ticket_number }}</a></td>
                            <td>
                                <span class="ticket-title-text" title="{{ strip_tags($ticket->title) }}">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 40) }}
                                </span>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar-xs">{{ strtoupper(substr($ticket->user->name ?? 'U', 0, 2)) }}</div>
                                    <span class="user-cell-name">{{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 14) }}</span>
                                </div>
                            </td>
                            <td><span class="badge-pill badge-priority-{{ $ticket->priority ?? 'medium' }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td><span class="badge-pill badge-status-{{ str_replace(' ','_',$ticket->status) }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span></td>
                            <td>
                                @if($ticket->sla_due_at)
                                    <span class="badge-pill {{ $isOverdue ? 'badge-sla-overdue' : 'badge-sla-ok' }}">
                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                                        {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d/m') }}
                                    </span>
                                @else
                                    <span class="badge-pill badge-sla-none">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap text-small">{{ $ticket->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Tidak ada tickets untuk ditampilkan</p>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="sidebar-stack">

            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title"><i class="fas fa-tags"></i>Tickets by category</h5>
                </div>
                <div class="glass-card-body">
                    @forelse($categoryCounts ?? [] as $category)
                    <div class="category-item">
                        <div class="category-dot-label">
                            <div class="category-dot" style="background:{{ $category->color ?? '#6366f1' }}"></div>
                            <span>{{ $category->name }}</span>
                        </div>
                        <span class="category-count">{{ $category->tickets_count }}</span>
                    </div>
                    @empty
                    <div class="empty-state" style="padding:1.5rem 0">
                        <i class="fas fa-tag" style="font-size:1.75rem"></i>
                        <p>Tidak ada data kategori</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title"><i class="fas fa-user-shield"></i>Performa IT staff</h5>
                </div>
                <div class="glass-card-body">
                    @forelse($itStaffPerformance ?? [] as $staff)
                    <div class="staff-item">
                        <div class="staff-row">
                            <div class="staff-identity">
                                <div class="avatar-xs">{{ strtoupper(substr($staff->name, 0, 1)) }}</div>
                                <span class="staff-name">{{ $staff->name }}</span>
                            </div>
                            <span class="staff-rate">{{ $staff->completion_rate ?? 0 }}%</span>
                        </div>
                        <div class="staff-progress-track">
                            <div class="staff-progress-fill" style="width:{{ $staff->completion_rate ?? 0 }}%"></div>
                        </div>
                        <div class="staff-meta">
                            <span class="ok"><i class="fas fa-check-circle"></i>{{ $staff->resolved_count ?? 0 }} selesai</span>
                            <span class="wip"><i class="fas fa-spinner"></i>{{ $staff->active_count ?? 0 }} aktif</span>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state" style="padding:1.5rem 0">
                        <i class="fas fa-users" style="font-size:1.75rem"></i>
                        <p>Belum ada data IT staff</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title"><i class="fas fa-chart-line"></i>Trend (6 bulan)</h5>
                </div>
                <div class="glass-card-body">
                    <div class="chart-wrap" style="height:160px">
                        <canvas id="ticketTrendChart" role="img" aria-label="Tren tiket 6 bulan."></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── Clock ───────────────────────────────────────────────── */
    var DAYS = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var MON  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    function tick() {
        var d = new Date();
        var pad = function (n) { return String(n).padStart(2,'0'); };
        document.getElementById('liveClock').textContent =
            pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        document.getElementById('liveDate').textContent =
            DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + MON[d.getMonth()] + ' ' + d.getFullYear();
        setTimeout(tick, 1000);
    }
    tick();

    /* ── Weather (Open-Meteo, no API key) ───────────────────── */
    var WX_ICONS = {0:'☀️',1:'🌤️',2:'⛅',3:'☁️',45:'🌫️',48:'🌫️',51:'🌦️',53:'🌦️',55:'🌧️',61:'🌧️',63:'🌧️',65:'🌧️',71:'🌨️',73:'🌨️',75:'❄️',80:'🌦️',81:'🌧️',82:'⛈️',95:'⛈️',96:'⛈️',99:'⛈️'};
    var WX_DESC  = {0:'Cerah',1:'Sebagian berawan',2:'Berawan',3:'Mendung',45:'Berkabut',48:'Berkabut',51:'Gerimis ringan',53:'Gerimis',55:'Gerimis deras',61:'Hujan ringan',63:'Hujan sedang',65:'Hujan deras',71:'Salju ringan',73:'Salju',75:'Salju lebat',80:'Hujan lokal',81:'Hujan',82:'Hujan deras',95:'Badai',96:'Badai + es',99:'Badai besar'};

    function loadWeather(lat, lon) {
        fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current_weather=true&timezone=auto')
            .then(function(r){ return r.json(); })
            .then(function(d){
                var cw = d.current_weather;
                document.getElementById('wxTemp').textContent = Math.round(cw.temperature) + '°C';
                document.getElementById('wxIcon').textContent = WX_ICONS[cw.weathercode] || '🌡️';
                document.getElementById('wxDesc').textContent = WX_DESC[cw.weathercode]  || '--';
            })
            .catch(function(){ document.getElementById('wxTemp').textContent = '--°C'; });
    }

    function loadCity(lat, lon) {
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon)
            .then(function(r){ return r.json(); })
            .then(function(d){
                var a = d.address;
                document.getElementById('wxLoc').textContent = a.city || a.town || a.village || a.county || 'Lokasi kamu';
            })
            .catch(function(){ document.getElementById('wxLoc').textContent = 'Lokasi kamu'; });
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos){
                var lat = pos.coords.latitude.toFixed(4);
                var lon = pos.coords.longitude.toFixed(4);
                loadCity(lat, lon);
                loadWeather(lat, lon);
            },
            function(){
                document.getElementById('wxLoc').textContent = 'Garut';
                loadWeather(-7.2167, 107.9000);
            }
        );
    } else {
        document.getElementById('wxLoc').textContent = 'Garut';
        loadWeather(-7.2167, 107.9000);
    }

    /* ── Chart helpers ──────────────────────────────────────── */
    var GRID   = 'rgba(0,0,0,0.05)';
    var TICK   = { color: '#94a3b8', font: { size: 10 } };
    var MONTHS = @json($jsMonths);

    function sparkOpts() {
        return {
            responsive: true, maintainAspectRatio: false,
            elements: { point: { radius: 0 } },
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false, beginAtZero: false } }
        };
    }

    /* ── Trend line ─────────────────────────────────────────── */
    var trendCtx = document.getElementById('monthlyTrendChart');
    if (trendCtx) {
        var datasets = [
            { label:'Total',    data: @json($jsTotals),   borderColor:'#1d6fb8', backgroundColor:'rgba(29,111,184,0.10)', tension:.4, fill:true,  borderWidth:2, pointBackgroundColor:'#1d6fb8', pointRadius:4, pointHoverRadius:6 },
            { label:'Open',     data: @json($jsOpen),     borderColor:'#d97706', backgroundColor:'transparent',           tension:.4, fill:false, borderWidth:2, borderDash:[5,4], pointBackgroundColor:'#d97706', pointRadius:3 },
            { label:'Resolved', data: @json($jsResolved), borderColor:'#059669', backgroundColor:'transparent',           tension:.4, fill:false, borderWidth:2, pointStyle:'rect', pointBackgroundColor:'#059669', pointRadius:4 }
        ];

        var legendEl = document.getElementById('trendLegend');
        var sw = ['background:#1d6fb8','background:transparent;border-top:2px dashed #d97706;width:16px;height:0;border-radius:0','background:#059669'];
        datasets.forEach(function(ds, i){
            var s = document.createElement('span');
            s.className = 'chart-legend-item';
            s.innerHTML = '<span class="chart-legend-swatch" style="' + sw[i] + '"></span>' + ds.label;
            legendEl.appendChild(s);
        });

        new Chart(trendCtx, {
            type: 'line',
            data: { labels: MONTHS, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: GRID }, ticks: TICK, border: { dash: [3,3] } },
                    y: { beginAtZero: true, grid: { color: GRID }, ticks: Object.assign({ precision: 0 }, TICK), border: { dash: [3,3] } }
                }
            }
        });
    }

    /* ── Status donut ───────────────────────────────────────── */
    var statusCtx = document.getElementById('statusDonutChart');
    if (statusCtx) {
        var sd = [{{ $openTickets ?? 47 }}, {{ $resolvedTickets ?? 221 }}, {{ $closedTickets ?? 16 }}];
        var total = sd.reduce(function(a,b){ return a+b; }, 0);

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open','Resolved','Closed'],
                datasets: [{ data: sd, backgroundColor: ['#4f46e5','#059669','#94a3b8'], borderWidth: 2, borderColor: '#ffffff', hoverOffset: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(c){ return ' ' + c.label + ': ' + c.parsed + ' (' + (total > 0 ? Math.round(c.parsed/total*100) : 0) + '%)'; } } }
                }
            }
        });

        var sl = document.getElementById('statusLegend');
        ['#4f46e5','#059669','#94a3b8'].forEach(function(col, i){
            var s = document.createElement('span');
            s.className = 'chart-legend-item';
            s.innerHTML = '<span class="chart-legend-swatch" style="background:' + col + '"></span>' + ['Open','Resolved','Closed'][i];
            sl.appendChild(s);
        });
    }

    /* ── Category bar ───────────────────────────────────────── */
    var catCtx = document.getElementById('categoryBarChart');
    if (catCtx) {
        var catLabels = @json($catLabels);
        var catData   = @json($catData);
        var catColors = ['#1d6fb8','#4f46e5','#d97706','#7c3aed','#94a3b8','#059669','#e11d48'];

        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{ label:'Tiket', data: catData, backgroundColor: catColors.slice(0, catLabels.length), borderRadius: 6, borderSkipped: false }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: TICK },
                    y: { beginAtZero: true, grid: { color: GRID }, ticks: Object.assign({ precision: 0 }, TICK) }
                }
            }
        });
    }

    /* ── Sidebar trend ──────────────────────────────────────── */
    var sideCtx = document.getElementById('ticketTrendChart');
    if (sideCtx) {
        new Chart(sideCtx, {
            type: 'line',
            data: { labels: MONTHS, datasets: [{ data: @json($jsTotals), borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,0.12)', tension:.4, fill:true, borderWidth:2, pointBackgroundColor:'#6366f1', pointRadius:3, pointHoverRadius:5 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor:'rgba(15,20,40,0.9)', titleColor:'#f1f5f9', bodyColor:'#94a3b8', borderColor:'rgba(99,102,241,.3)', borderWidth:1, padding:8, cornerRadius:8 } },
                scales: {
                    x: { grid: { display: false }, ticks: Object.assign({ font: { size: 9 } }, TICK) },
                    y: { beginAtZero: true, grid: { color: GRID }, ticks: Object.assign({ font: { size: 9 }, precision: 0 }, TICK) }
                }
            }
        });
    }

    /* ── Sparklines ─────────────────────────────────────────── */
    var spkRes = document.getElementById('sparkResolution');
    if (spkRes) {
        new Chart(spkRes, {
            type: 'line',
            data: { labels: MONTHS, datasets: [{ data:[72,75,71,78,80,{{ $sparkResVal }}], borderColor:'#059669', backgroundColor:'rgba(5,150,105,.15)', tension:.4, fill:true, borderWidth:2 }] },
            options: sparkOpts()
        });
    }

    var spkTime = document.getElementById('sparkResTime');
    if (spkTime) {
        new Chart(spkTime, {
            type: 'line',
            data: { labels: MONTHS, datasets: [{ data:[5.2,4.8,5.5,4.1,3.9,{{ $sparkTimeVal }}], borderColor:'#0d9488', backgroundColor:'rgba(13,148,136,.15)', tension:.4, fill:true, borderWidth:2 }] },
            options: sparkOpts()
        });
    }

})();
</script>
@endpush
