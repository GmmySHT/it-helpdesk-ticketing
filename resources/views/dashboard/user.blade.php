@extends('layouts.app')

@section('title', 'Dashboard - My Tickets')

@section('content')
<div class="container-fluid px-4">
    {{-- Page Header dengan Container Biru --}}
    <div class="page-header-wrapper mb-4">
        <div class="page-header-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard Saya
                    </h1>
                    <p class="page-subtitle">Kelola dan pantau ticket permintaan Anda</p>
                </div>
                <div>
                    <a href="{{ route('tickets.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-2"></i>Buat Ticket Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- KPI Cards -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>{{ $myTicketsCount ?? 0 }}</h3>
                        <span>Total Ticket Saya</span>
                        <small class="text-muted d-block mt-1">Semua ticket yang Anda buat</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>{{ $myOpenCount ?? 0 }}</h3>
                        <span>Ticket Aktif</span>
                        <small class="text-muted d-block mt-1">Open / In Queue / In Progress</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-info w-100">
                        <span>Status Terbanyak (Global)</span>
                        @if(!empty($statusCounts))
                            <div class="mt-2">
                                @foreach($statusCounts as $st => $cnt)
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-capitalize">
                                            @php
                                                $statusIcons = [
                                                    'open' => '📋',
                                                    'in_queue' => '⏳',
                                                    'in_progress' => '⚙️',
                                                    'resolved' => '✅',
                                                    'closed' => '📦'
                                                ];
                                                $icon = $statusIcons[$st] ?? '📌';
                                            @endphp
                                            {{ $icon }} {{ str_replace('_',' ', $st) }}
                                        </span>
                                        <strong class="text-primary">{{ $cnt }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="small text-muted mt-2">Belum ada data status.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent My Tickets (table) -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>Ticket Terbaru Saya
                        </h5>
                        <a href="{{ route('tickets.index', ['mine'=>1]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10">
                                <tr>
                                    <th class="ps-3 py-3">#</th>
                                    <th class="py-3">Ticket</th>
                                    <th class="py-3">Judul</th>
                                    <th class="py-3">Kategori</th>
                                    <th class="py-3">Prioritas</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3">Dibuat</th>
                                    <th class="text-center py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMyTickets as $t)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $t) }}" class="fw-semibold text-decoration-none text-dark">
                                            {{ $t->ticket_number }}
                                        </a>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($t->title, 50) }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $t->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityColors = [
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'urgent' => 'danger'
                                            ];
                                            $priorityIcons = [
                                                'low' => 'fas fa-arrow-down',
                                                'medium' => 'fas fa-minus',
                                                'high' => 'fas fa-arrow-up',
                                                'urgent' => 'fas fa-exclamation-triangle'
                                            ];
                                            $color = $priorityColors[$t->priority] ?? 'primary';
                                            $icon = $priorityIcons[$t->priority] ?? 'fas fa-flag';
                                        @endphp
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2">
                                            <i class="{{ $icon }} me-1"></i> {{ ucfirst($t->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusIcons = [
                                                'open' => 'fas fa-envelope',
                                                'in_queue' => 'fas fa-clock',
                                                'in_progress' => 'fas fa-cogs',
                                                'resolved' => 'fas fa-check-circle',
                                                'closed' => 'fas fa-archive'
                                            ];
                                            $statusColors = [
                                                'open' => 'primary',
                                                'in_queue' => 'info',
                                                'in_progress' => 'warning',
                                                'resolved' => 'success',
                                                'closed' => 'secondary'
                                            ];
                                            $statusIcon = $statusIcons[$t->status] ?? 'fas fa-question';
                                            $statusColor = $statusColors[$t->status] ?? 'primary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} px-3 py-2">
                                            <i class="{{ $statusIcon }} me-1"></i>
                                            {{ ucfirst(str_replace('_',' ', $t->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="small">
                                            <i class="far fa-calendar-alt me-1 text-muted"></i>
                                            {{ $t->created_at->format('d M Y') }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $t->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('tickets.show', $t) }}"
                                               class="btn btn-sm btn-outline-primary rounded-circle"
                                               style="width: 32px; height: 32px;"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(in_array($t->status, ['open','in_queue']))
                                                <a href="{{ route('tickets.edit', $t) }}"
                                                   class="btn btn-sm btn-outline-warning rounded-circle"
                                                   style="width: 32px; height: 32px;"
                                                   title="Edit Ticket">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-ticket-alt fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-3">Anda belum membuat ticket</p>
                                            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle me-1"></i>
                                                Buat Ticket Pertama
                                            </a>
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

        <!-- Chart / Summary di sebelah kanan -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Tiket Per Bulan
                    </h5>
                    <small class="text-muted">6 bulan terakhir</small>
                </div>
                <div class="card-body">
                    <canvas id="userTicketsChart" style="max-height: 240px; width: 100%;"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-primary me-2 mt-1"></i>
                            <span class="small">Jelaskan masalah secara singkat dan sertakan langkah reproduksi bila ada.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-tag text-primary me-2 mt-1"></i>
                            <span class="small">Tambahkan kategori dan prioritas dengan tepat agar admin dapat menindaklanjuti cepat.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-bell text-primary me-2 mt-1"></i>
                            <span class="small">Periksa notifikasi untuk mengetahui update progress ticket Anda.</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-comment text-primary me-2 mt-1"></i>
                            <span class="small">Jangan ragu untuk berkomentar jika ada informasi tambahan yang perlu disampaikan.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Stat Card styling */
    .stat-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: white !important;
        overflow: hidden;
        position: relative;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, var(--tosca-primary), var(--tosca-dark));
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card .card-body {
        display: flex;
        align-items: flex-start;
        padding: 1.5rem;
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 1.5rem;
        color: white;
    }

    .stat-info {
        flex: 1;
    }

    .stat-info h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #1f2937;
    }

    .stat-info span {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    /* Card header styling */
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

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    /* Table styling */
    .table td, .table th {
        padding: 1rem 0.75rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-card .card-body {
            padding: 1rem;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
        }

        .stat-icon i {
            font-size: 1.2rem;
        }

        .stat-info h3 {
            font-size: 1.25rem;
        }

        .table td, .table th {
            padding: 0.75rem 0.5rem;
        }

        .btn-sm.rounded-circle {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.7rem;
        }
    }

    /* Tips list styling */
    .list-unstyled li {
        transition: transform 0.2s ease;
    }

    .list-unstyled li:hover {
        transform: translateX(5px);
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data untuk chart
        const months = {!! json_encode($months ?? []) !!};
        const dataCounts = {!! json_encode($ticketCountsByMonth ?? []) !!};

        const chartCanvas = document.getElementById('userTicketsChart');

        if (months.length && dataCounts.length && chartCanvas) {
            const ctx = chartCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Jumlah Tiket',
                        data: dataCounts,
                        borderWidth: 1,
                        backgroundColor: 'rgba(24, 181, 160, 0.7)',
                        borderColor: '#18b5a0',
                        borderRadius: 8,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Jumlah: ${context.raw} tiket`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        } else if (chartCanvas) {
            // Tampilkan pesan jika tidak ada data
            const parentCard = chartCanvas.closest('.card');
            if (parentCard) {
                const bodyCard = parentCard.querySelector('.card-body');
                if (bodyCard && !bodyCard.querySelector('.no-data-message')) {
                    const noDataMsg = document.createElement('div');
                    noDataMsg.className = 'text-center text-muted py-4 no-data-message';
                    noDataMsg.innerHTML = '<i class="fas fa-chart-line fa-2x mb-2 d-block"></i>Tidak ada data chart untuk ditampilkan';
                    bodyCard.appendChild(noDataMsg);
                    chartCanvas.style.display = 'none';
                }
            }
        }
    });
</script>
@endpush
