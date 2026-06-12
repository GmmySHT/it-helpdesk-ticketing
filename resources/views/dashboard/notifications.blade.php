{{-- resources/views/dashboard/notifications.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifikasi - Sistem Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notification-item {
            transition: background-color 0.2s ease;
            border-left: 3px solid transparent;
        }

        .notification-item.unread {
            background-color: #e8f4ff;
            border-left-color: #0d6efd;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 18px;
        }

        .notification-icon.status {
            background-color: #fff3cd;
            color: #ffc107;
        }

        .notification-icon.assignment {
            background-color: #d1ecf1;
            color: #17a2b8;
        }

        .notification-icon.response {
            background-color: #d4edda;
            color: #28a745;
        }

        .notification-icon.ticket {
            background-color: #cce5ff;
            color: #007bff;
        }

        .notification-icon.resolution {
            background-color: #d4edda;
            color: #28a745;
        }

        .time-text {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .mark-read-btn {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .notification-item:hover .mark-read-btn {
            opacity: 1;
        }

        .btn-icon {
            padding: 4px 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar (sesuaikan dengan layout Anda) --}}
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar" style="min-height: 100vh;">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('tickets.index') }}">
                                <i class="fas fa-ticket-alt"></i> Tiket
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active bg-primary text-white" href="{{ route('notifications.page') }}">
                                <i class="fas fa-bell"></i> Notifikasi
                                @if($unreadCount > 0)
                                    <span class="badge bg-danger rounded-pill ms-2">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </li>
                        @can('view reports')
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-line"></i> Laporan
                            </a>
                        </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user"></i> Profil
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            {{-- Main Content --}}
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-bell"></i> Notifikasi
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @if($unreadCount > 0)
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="markAllReadBtn">
                                <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Notifications List --}}
                <div class="card">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="notificationTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                    Semua
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread" type="button" role="tab">
                                    Belum Dibaca
                                    @if($unreadCount > 0)
                                        <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content">
                            {{-- All Notifications Tab --}}
                            <div class="tab-pane fade show active" id="all" role="tabpanel">
                                <div class="list-group list-group-flush">
                                    @forelse($notifications as $notification)
                                        <div class="list-group-item notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}"
                                             data-notification-id="{{ $notification->id }}">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    @php
                                                        $type = explode('\\', $notification->type);
                                                        $type = end($type);
                                                        $icon = 'bell';
                                                        $iconClass = 'general';

                                                        if(str_contains($notification->type, 'Status')) {
                                                            $icon = 'sync-alt';
                                                            $iconClass = 'status';
                                                        } elseif(str_contains($notification->type, 'Assigned')) {
                                                            $icon = 'user-check';
                                                            $iconClass = 'assignment';
                                                        } elseif(str_contains($notification->type, 'Response')) {
                                                            $icon = 'reply';
                                                            $iconClass = 'response';
                                                        } elseif(str_contains($notification->type, 'Created')) {
                                                            $icon = 'plus-circle';
                                                            $iconClass = 'ticket';
                                                        }
                                                    @endphp
                                                    <div class="notification-icon {{ $iconClass }}">
                                                        <i class="fas fa-{{ $icon }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notifikasi' }}</h6>
                                                            <p class="mb-1 text-muted small">{{ $notification->data['message'] ?? '' }}</p>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="time-text">{{ $notification->created_at->diffForHumans() }}</small>
                                                            @if(is_null($notification->read_at))
                                                                <button class="btn btn-sm btn-link mark-read-btn mark-read"
                                                                        data-id="{{ $notification->id }}"
                                                                        style="color: #0d6efd;">
                                                                    <i class="fas fa-check-circle"></i> Tandai dibaca
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if(isset($notification->data['url']))
                                                        <div class="mt-2">
                                                            <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary btn-icon">
                                                                <i class="fas fa-eye"></i> Lihat Detail
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak ada notifikasi</h5>
                                            <p class="text-muted small">Anda akan melihat notifikasi di sini ketika ada aktivitas</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Unread Notifications Tab --}}
                            <div class="tab-pane fade" id="unread" role="tabpanel">
                                <div class="list-group list-group-flush" id="unread-notifications">
                                    {{-- Load via JavaScript --}}
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Load unread notifications
            $('#unread-tab').on('shown.bs.tab', function() {
                loadUnreadNotifications();
            });

            // Mark single notification as read
            $('.mark-read').on('click', function() {
                const id = $(this).data('id');
                markAsRead(id);
            });

            // Mark all as read
            $('#markAllReadBtn').on('click', function() {
                Swal.fire({
                    title: 'Tandai semua dibaca?',
                    text: "Semua notifikasi akan ditandai telah dibaca",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, tandai semua!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        markAllAsRead();
                    }
                });
            });

            // Refresh page
            $('#refreshBtn').on('click', function() {
                location.reload();
            });
        });

        function markAsRead(id) {
            $.ajax({
                url: `/api/notifications/${id}/read`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI
                        $(`.notification-item[data-notification-id="${id}"]`).removeClass('unread');
                        $(`.mark-read[data-id="${id}"]`).remove();

                        // Update counter
                        updateNotificationCount();

                        Swal.fire('Berhasil!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error!', 'Gagal menandai notifikasi', 'error');
                }
            });
        }

        function markAllAsRead() {
            $.ajax({
                url: '/api/notifications/read-all',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Update all notifications to read
                        $('.notification-item').removeClass('unread');
                        $('.mark-read').remove();
                        $('#markAllReadBtn').hide();

                        // Update counter
                        updateNotificationCount();

                        Swal.fire('Berhasil!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error!', 'Gagal menandai semua notifikasi', 'error');
                }
            });
        }

        function loadUnreadNotifications() {
            $.ajax({
                url: '/api/notifications',
                type: 'GET',
                data: { limit: 50 },
                success: function(response) {
                    if (response.success) {
                        const unreadNotifications = response.notifications.filter(n => !n.read_at);

                        if (unreadNotifications.length === 0) {
                            $('#unread-notifications').html(`
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h5 class="text-muted">Semua notifikasi sudah dibaca</h5>
                                    <p class="text-muted small">Selamat! Anda sudah membaca semua notifikasi</p>
                                </div>
                            `);
                        } else {
                            let html = '';
                            unreadNotifications.forEach(notif => {
                                html += `
                                    <div class="list-group-item notification-item unread" data-notification-id="${notif.id}">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <div class="notification-icon ${getIconClass(notif.type)}">
                                                    <i class="fas fa-${getIcon(notif.type)}"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">${notif.title}</h6>
                                                        <p class="mb-1 text-muted small">${notif.message}</p>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="time-text">${notif.created_at}</small>
                                                        <button class="btn btn-sm btn-link mark-read-btn mark-read"
                                                                data-id="${notif.id}"
                                                                style="color: #0d6efd;">
                                                            <i class="fas fa-check-circle"></i> Tandai dibaca
                                                        </button>
                                                    </div>
                                                </div>
                                                ${notif.url && notif.url !== '#' ? `
                                                    <div class="mt-2">
                                                        <a href="${notif.url}" class="btn btn-sm btn-outline-primary btn-icon">
                                                            <i class="fas fa-eye"></i> Lihat Detail
                                                        </a>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            $('#unread-notifications').html(html);

                            // Re-attach event handlers
                            $('.mark-read').on('click', function() {
                                const id = $(this).data('id');
                                markAsRead(id);
                            });
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    $('#unread-notifications').html(`
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h5 class="text-muted">Gagal memuat notifikasi</h5>
                            <button class="btn btn-sm btn-primary mt-2" onclick="loadUnreadNotifications()">
                                <i class="fas fa-sync-alt"></i> Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        function updateNotificationCount() {
            $.ajax({
                url: '/api/notifications/count',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const unreadCount = response.unread_count;

                        // Update badge di sidebar dan header
                        $('.badge.bg-danger').each(function() {
                            if (unreadCount > 0) {
                                $(this).text(unreadCount).show();
                            } else {
                                $(this).hide();
                            }
                        });

                        // Update title badge
                        if (unreadCount > 0) {
                            $('#unread-tab .badge').text(unreadCount).show();
                        } else {
                            $('#unread-tab .badge').hide();
                        }
                    }
                }
            });
        }

        function getIcon(type) {
            const icons = {
                'status': 'sync-alt',
                'assignment': 'user-check',
                'response': 'reply',
                'ticket': 'plus-circle',
                'resolution': 'check-circle'
            };
            return icons[type] || 'bell';
        }

        function getIconClass(type) {
            const classes = {
                'status': 'status',
                'assignment': 'assignment',
                'response': 'response',
                'ticket': 'ticket',
                'resolution': 'resolution'
            };
            return classes[type] || 'general';
        }

        // Polling untuk notifikasi baru (setiap 30 detik)
        setInterval(function() {
            updateNotificationCount();
            if ($('#unread-tab').hasClass('active')) {
                loadUnreadNotifications();
            }
        }, 30000);
    </script>
</body>
</html>
