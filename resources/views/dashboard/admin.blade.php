@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
{{-- Container utama dengan Glassmorphism --}}
<div class="glass-container">
    <div class="container-fluid px-4">
        {{-- Page Header dengan Container Biru --}}
        <div class="page-header-wrapper mb-4">
            <div class="page-header-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                        </h1>
                        <p class="page-subtitle">Overview sistem ticketing RS Intan Husada</p>
                    </div>
                    <div>
                        <span class="text-light">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ now()->format('d F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="{{ route('tickets.index') }}" class="text-decoration-none">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 hover-shadow transition">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-ticket-alt text-primary fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Kelola Tickets</h6>
                                            <small class="text-muted">Lihat dan kelola semua tickets</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('users.index') }}" class="text-decoration-none">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 hover-shadow transition">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-users-cog text-info fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Manajemen User</h6>
                                            <small class="text-muted">Kelola pengguna dan akses</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('tickets.create') }}" class="text-decoration-none">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 hover-shadow transition">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-plus-circle text-success fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Buat Ticket</h6>
                                            <small class="text-muted">Buat ticket baru untuk user</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('reports.index') }}" class="text-decoration-none">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 hover-shadow transition">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-chart-bar text-warning fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Laporan</h6>
                                            <small class="text-muted">Analisis performa sistem</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards - Baris 1 --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Total Tickets</p>
                                <h2 class="mb-0 fw-bold">{{ $totalTickets ?? 0 }}</h2>
                                @if(isset($totalChange))
                                <small class="text-{{ $totalChange['trend'] == 'up' ? 'success' : ($totalChange['trend'] == 'down' ? 'danger' : 'secondary') }}">
                                    <i class="fas fa-arrow-{{ $totalChange['trend'] == 'up' ? 'up' : ($totalChange['trend'] == 'down' ? 'down' : 'minus') }} me-1"></i>
                                    {{ abs($totalChange['value']) }}% dari bulan lalu
                                </small>
                                @endif
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
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Open / In Progress</p>
                                <h2 class="mb-0 fw-bold">{{ $openTickets ?? 0 }}</h2>
                                @if(isset($openChange))
                                <small class="text-{{ $openChange['trend'] == 'up' ? 'danger' : ($openChange['trend'] == 'down' ? 'success' : 'secondary') }}">
                                    <i class="fas fa-arrow-{{ $openChange['trend'] == 'up' ? 'up' : ($openChange['trend'] == 'down' ? 'down' : 'minus') }} me-1"></i>
                                    {{ abs($openChange['value']) }}% dari bulan lalu
                                </small>
                                @endif
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-spinner fa-spin text-warning fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Resolved</p>
                                <h2 class="mb-0 fw-bold">{{ $resolvedTickets ?? 0 }}</h2>
                                @if(isset($resolvedChange))
                                <small class="text-{{ $resolvedChange['trend'] == 'up' ? 'success' : ($resolvedChange['trend'] == 'down' ? 'danger' : 'secondary') }}">
                                    <i class="fas fa-arrow-{{ $resolvedChange['trend'] == 'up' ? 'up' : ($resolvedChange['trend'] == 'down' ? 'down' : 'minus') }} me-1"></i>
                                    {{ abs($resolvedChange['value']) }}% dari bulan lalu
                                </small>
                                @endif
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
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Closed</p>
                                <h2 class="mb-0 fw-bold">{{ $closedTickets ?? 0 }}</h2>
                                @if(isset($closedChange))
                                <small class="text-{{ $closedChange['trend'] == 'up' ? 'success' : ($closedChange['trend'] == 'down' ? 'danger' : 'secondary') }}">
                                    <i class="fas fa-arrow-{{ $closedChange['trend'] == 'up' ? 'up' : ($closedChange['trend'] == 'down' ? 'down' : 'minus') }} me-1"></i>
                                    {{ abs($closedChange['value']) }}% dari bulan lalu
                                </small>
                                @endif
                            </div>
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-archive text-secondary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards - Baris 2 (Admin Specific) --}}
        @if(isset($usersCount) && isset($itStaffCount) && isset($ticketsUnassigned))
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Total Users</p>
                                <h2 class="mb-0 fw-bold">{{ $usersCount ?? 0 }}</h2>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-users text-info fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">IT Staff</p>
                                <h2 class="mb-0 fw-bold">{{ $itStaffCount ?? 0 }}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-user-shield text-primary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Unassigned Tickets</p>
                                <h2 class="mb-0 fw-bold text-warning">{{ $ticketsUnassigned ?? 0 }}</h2>
                                @if(isset($unassignedChange))
                                <small class="text-{{ $unassignedChange['trend'] == 'up' ? 'danger' : ($unassignedChange['trend'] == 'down' ? 'success' : 'secondary') }}">
                                    <i class="fas fa-arrow-{{ $unassignedChange['trend'] == 'up' ? 'up' : ($unassignedChange['trend'] == 'down' ? 'down' : 'minus') }} me-1"></i>
                                    {{ abs($unassignedChange['value']) }}%
                                </small>
                                @endif
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-inbox text-warning fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Overdue Tickets</p>
                                <h2 class="mb-0 fw-bold text-danger">{{ $overdueTickets ?? 0 }}</h2>
                            </div>
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- SLA Performance Summary --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-chart-pie text-primary me-2"></i>SLA Compliance Rate
                            </h6>
                        </div>
                        <div class="text-center">
                            <div class="display-4 fw-bold text-{{ ($slaComplianceRate ?? 0) >= 90 ? 'success' : (($slaComplianceRate ?? 0) >= 70 ? 'warning' : 'danger') }}">
                                {{ $slaComplianceRate ?? 0 }}%
                            </div>
                            <p class="text-muted small">Tiket selesai tepat waktu</p>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-{{ ($slaComplianceRate ?? 0) >= 90 ? 'success' : (($slaComplianceRate ?? 0) >= 70 ? 'warning' : 'danger') }}"
                                     style="width: {{ $slaComplianceRate ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-hourglass-half text-primary me-2"></i>Avg Resolution Time
                            </h6>
                        </div>
                        <div class="text-center">
                            <div class="display-4 fw-bold text-info">
                                {{ $avgResolutionTime ?? 0 }} <small class="fs-6">jam</small>
                            </div>
                            <p class="text-muted small">Rata-rata waktu penyelesaian</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-undo-alt text-primary me-2"></i>Reopen Rate
                            </h6>
                        </div>
                        <div class="text-center">
                            <div class="display-4 fw-bold text-warning">
                                {{ $reopenRate ?? 0 }}<small class="fs-6">%</small>
                            </div>
                            <p class="text-muted small">Ticket yang dibuka kembali</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="row g-4">
            {{-- Recent Tickets --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock text-primary me-2"></i>Tickets Terbaru
                        </h5>
                        <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right me-1"></i>Lihat Semua
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if(isset($recentTickets) && $recentTickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-primary bg-opacity-10">
                                        <tr>
                                            <th class="ps-3">Ticket #</th>
                                            <th>Judul</th>
                                            <th>User</th>
                                            <th>Prioritas</th>
                                            <th>Status</th>
                                            <th>SLA</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentTickets as $ticket)
                                        @php
                                            $isOverdue = $ticket->sla_due_at && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($ticket->sla_due_at)) && !in_array($ticket->status, ['resolved', 'closed']);
                                            $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                                            $statusColors = ['open' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('tickets.show', $ticket) }}" class="fw-bold text-primary text-decoration-none">
                                                    {{ $ticket->ticket_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit(strip_tags($ticket->title), 40) }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-sm bg-secondary">
                                                        {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <span class="small">{{ \Illuminate\Support\Str::limit($ticket->user->name ?? '-', 15) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} bg-opacity-10 text-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} px-3 py-2">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusColors[$ticket->status] ?? 'secondary' }} px-3 py-2">
                                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($ticket->sla_due_at)
                                                    <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-success' }} bg-opacity-10 text-{{ $isOverdue ? 'danger' : 'success' }}">
                                                        <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-check-circle' }} me-1"></i>
                                                        {{ \Carbon\Carbon::parse($ticket->sla_due_at)->format('d/m') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <small>{{ $ticket->created_at->format('d/m/Y') }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada tickets</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="col-lg-4">
                {{-- Category Statistics --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tags text-primary me-2"></i>Tickets by Category
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($categoryCounts) && $categoryCounts->count() > 0)
                            @foreach ($categoryCounts as $category)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle" style="width: 10px; height: 10px; background: {{ $category->color ?? '#3b82f6' }}"></div>
                                    <span>{{ $category->name }}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $category->tickets_count }}</span>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-tag fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada data kategori</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- IT Staff Performance --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-shield text-primary me-2"></i>Performa IT Staff
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($itStaffPerformance) && $itStaffPerformance->count() > 0)
                            @foreach ($itStaffPerformance as $staff)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-primary">
                                            {{ substr($staff->name, 0, 1) }}
                                        </div>
                                        <span class="fw-semibold">{{ $staff->name }}</span>
                                    </div>
                                    <span class="small text-muted">{{ $staff->completion_rate ?? 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: {{ $staff->completion_rate ?? 0 }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle text-success me-1"></i>{{ $staff->resolved_count ?? 0 }} selesai
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-spinner text-warning me-1"></i>{{ $staff->active_count ?? 0 }} aktif
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Belum ada data IT Staff</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Monthly Ticket Chart --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>Ticket Trend (6 Bulan)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($months) && isset($ticketCountsByMonth))
                            <canvas id="ticketTrendChart" height="200"></canvas>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada data chart</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSS Glassmorphism untuk Container Utama --}}
<style>
    /* Glassmorphism Container Utama */
    .glass-container {
        background: rgba(255, 255, 255, 0.25) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-radius: 24px !important;
        margin: 1rem !important;
        padding: 1.5rem 0 !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15) !important;
    }

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

    .table td, .table th {
        padding: 1rem 0.75rem;
    }

    .hover-shadow {
        transition: all 0.2s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .transition {
        transition: all 0.2s ease;
    }

    .progress {
        background-color: #e5e7eb;
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .table td, .table th {
            padding: 0.75rem 0.5rem;
        }

        .avatar-sm {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }

        .glass-container {
            margin: 0.5rem !important;
            padding: 0.75rem 0 !important;
        }
    }
</style>

@if(isset($months) && isset($ticketCountsByMonth))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('ticketTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [{
                    label: 'Jumlah Tickets',
                    data: @json($ticketCountsByMonth),
                    borderColor: '#18b5a0',
                    backgroundColor: 'rgba(24, 181, 160, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#18b5a0',
                    pointBorderColor: '#fff',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    });
</script>
@endif

@endsection
