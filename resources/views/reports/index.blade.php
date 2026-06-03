@extends('layouts.app')

@section('title', 'Laporan Ticketing')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header dengan Container Biru --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-chart-bar me-2"></i>Laporan Ticketing
                    </h1>
                    <p class="page-subtitle">Analisis dan laporan performa sistem ticketing</p>
                </div>
                <div class="export-buttons">
                    <button class="btn btn-light" id="exportPdf">
                        <i class="fas fa-file-pdf me-1 text-danger"></i> Export PDF
                    </button>
                    <button class="btn btn-light" id="exportExcel">
                        <i class="fas fa-file-excel me-1 text-success"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Laporan
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-calendar-alt me-1"></i>Tanggal Mulai
                        </label>
                        <input type="date" class="form-control" name="start_date"
                               value="{{ $startDate }}" max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-calendar-check me-1"></i>Tanggal Akhir
                        </label>
                        <input type="date" class="form-control" name="end_date"
                               value="{{ $endDate }}" max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-tags me-1"></i>Kategori
                        </label>
                        <select class="form-select" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-chart-line me-1"></i>Status
                        </label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->role === 'admin')
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-user me-1"></i>User
                        </label>
                        <select class="form-select" name="user_id">
                            <option value="">Semua User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="row g-3 mt-2">
                    @if(auth()->user()->role === 'admin')
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-user-check me-1"></i>Assigned To
                        </label>
                        <select class="form-select" name="assigned_to">
                            <option value="">Semua IT Staff</option>
                            @foreach($itStaff as $staff)
                                <option value="{{ $staff->id }}" {{ $assignedTo == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                            <div class="ms-auto">
                                <span class="badge bg-primary bg-opacity-10 text-primary p-2">
                                    <i class="fas fa-calendar-week me-1"></i>
                                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Tickets</p>
                            <h2 class="mb-0 fw-bold">{{ $summary['total_tickets'] }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-ticket-alt text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Open Tickets</p>
                            <h2 class="mb-0 fw-bold">{{ $summary['open_tickets'] }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clock text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Resolved Tickets</p>
                            <h2 class="mb-0 fw-bold">{{ $summary['resolved_tickets'] }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Resolution Rate</p>
                            <h2 class="mb-0 fw-bold">{{ $summary['resolution_rate'] }}%</h2>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-chart-line text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Trend Tickets (6 Bulan Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Distribusi Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs for Tickets and History --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button">
                        <i class="fas fa-ticket-alt me-1"></i> Tickets
                        <span class="badge bg-primary ms-1">{{ $tickets->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
                        <i class="fas fa-history me-1"></i> Activity History
                        <span class="badge bg-info ms-1">{{ $histories->total() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="reportTabsContent">

                {{-- Tickets Tab --}}
                <div class="tab-pane fade show active" id="tickets" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3">Ticket #</th>
                                    <th>Judul</th>
                                    <th>User</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Prioritas</th>
                                    <th>Assigned To</th>
                                    <th>Tanggal Dibuat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="fw-bold text-primary text-decoration-none">
                                            {{ $ticket->ticket_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 50) }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm bg-secondary">
                                                {{ substr($ticket->user->name ?? 'U', 0, 1) }}
                                            </div>
                                            <span class="small">{{ $ticket->user->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            {{ $ticket->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->getStatusBadgeAttribute() }} bg-opacity-10 text-{{ $ticket->getStatusBadgeAttribute() }} px-3 py-2">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->getPriorityBadgeAttribute() }} bg-opacity-10 text-{{ $ticket->getPriorityBadgeAttribute() }} px-3 py-2">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->assignedTo)
                                            <span class="small">{{ $ticket->assignedTo->name }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            {{ $ticket->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ $ticket->created_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-0">Tidak ada tickets ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($tickets->hasPages())
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Menampilkan {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} dari {{ $tickets->total() }} tickets
                            </div>
                            <div>
                                {{ $tickets->appends(request()->except('page'))->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- History Tab --}}
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3">Ticket #</th>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>Notes</th>
                                    <th>Timestamp</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($histories as $history)
                                <tr>
                                    <td class="ps-3">
                                        @if($history->ticket)
                                            <a href="{{ route('tickets.show', $history->ticket) }}" class="fw-bold text-primary text-decoration-none">
                                                {{ $history->ticket->ticket_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $actionBadges = [
                                                'created' => 'success',
                                                'updated' => 'info',
                                                'assigned' => 'warning',
                                                'taken' => 'primary',
                                                'status_changed' => 'secondary',
                                                'reopened' => 'warning',
                                                'resolved' => 'success'
                                            ];
                                            $badgeColor = $actionBadges[$history->action] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} px-3 py-2">
                                            {{ ucfirst(str_replace('_', ' ', $history->action)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm bg-secondary">
                                                {{ substr($history->user->name ?? 'S', 0, 1) }}
                                            </div>
                                            <span class="small">{{ $history->user->name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small">{{ $history->notes }}</div>
                                        @if($history->meta)
                                            @php
                                                $meta = json_decode($history->meta, true);
                                                $changes = [];
                                                if(is_array($meta)) {
                                                    foreach($meta as $key => $value) {
                                                        if(is_array($value) && isset($value['old'], $value['new'])) {
                                                            $changes[] = "$key: {$value['old']} → {$value['new']}";
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @if(!empty($changes))
                                                <div class="mt-1 small text-muted">
                                                    @foreach($changes as $change)
                                                        <div>• {{ $change }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="small text-muted">{{ $history->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($history->ticket)
                                            <a href="{{ route('tickets.show', $history->ticket) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-0">Tidak ada activity history ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($histories->hasPages())
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Menampilkan {{ $histories->firstItem() }} - {{ $histories->lastItem() }} dari {{ $histories->total() }} activities
                            </div>
                            <div>
                                {{ $histories->appends(request()->except('history_page'))->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .card-header {
        background: white !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 1rem 1.5rem;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
    }

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

    .table td, .table th {
        padding: 1rem 0.75rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #18b5a0;
        box-shadow: 0 0 0 0.2rem rgba(24, 181, 160, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #18b5a0, #0e8b7a);
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 181, 160, 0.3);
    }

    .btn-outline-primary {
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        transform: translateY(-1px);
    }

    .export-buttons .btn-light {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .export-buttons .btn-light:hover {
        background: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tabs
        var triggerTabList = [].slice.call(document.querySelectorAll('#reportTabs button'));
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });

        // Load chart data
        loadChartData();

        // Export buttons
        document.getElementById('exportPdf').addEventListener('click', function() {
            window.location.href = '{{ route("reports.export.pdf") }}?start_date={{ $startDate }}&end_date={{ $endDate }}';
        });

        document.getElementById('exportExcel').addEventListener('click', function() {
            window.location.href = '{{ route("reports.export.excel") }}?start_date={{ $startDate }}&end_date={{ $endDate }}';
        });
    });

    function loadChartData() {
        fetch('{{ route("reports.analytics") }}?start_date={{ $startDate }}&end_date={{ $endDate }}')
            .then(response => response.json())
            .then(data => {
                renderMonthlyChart(data.monthly_data);
                renderCategoryChart(data.category_data);
            })
            .catch(error => console.error('Error loading chart data:', error));
    }

    function renderMonthlyChart(monthlyData) {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const labels = monthlyData.map(item => item.month);
        const totalData = monthlyData.map(item => item.total);
        const openData = monthlyData.map(item => item.open);
        const resolvedData = monthlyData.map(item => item.resolved);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Tickets',
                        data: totalData,
                        borderColor: '#18b5a0',
                        backgroundColor: 'rgba(24, 181, 160, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Open Tickets',
                        data: openData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 2
                    },
                    {
                        label: 'Resolved Tickets',
                        data: resolvedData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function renderCategoryChart(categoryData) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        const labels = categoryData.map(item => item.name);
        const data = categoryData.map(item => item.count);

        const colors = ['#18b5a0', '#0d6efd', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: 'white'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }
</script>
@endpush
