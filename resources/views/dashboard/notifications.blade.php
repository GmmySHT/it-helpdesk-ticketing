@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i> Notifikasi Saya
                    </h4>
                    <div class="d-flex align-items-center gap-2">
                        @if(auth()->user()->unreadNotifications->count() > 0)
                        <button id="markAllRead" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
                        </button>
                        @endif
                        <span class="badge bg-primary">
                            <i class="fas fa-envelope me-1"></i>
                            <span id="unreadCount">{{ auth()->user()->unreadNotifications->count() }}</span> belum dibaca
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                @php
                                    $data = $notification->data ?? [];
                                    $isUnread = is_null($notification->read_at);

                                    // Tentukan icon berdasarkan action
                                    $action = $data['action'] ?? $data['status'] ?? '';
                                    $icon = 'fas fa-bell';
                                    $iconColor = 'secondary';

                                    switch($action) {
                                        case 'created':
                                            $icon = 'fas fa-plus-circle';
                                            $iconColor = 'success';
                                            break;
                                        case 'assigned':
                                            $icon = 'fas fa-user-plus';
                                            $iconColor = 'warning';
                                            break;
                                        case 'taken':
                                            $icon = 'fas fa-hand-paper';
                                            $iconColor = 'info';
                                            break;
                                        case 'resolved':
                                            $icon = 'fas fa-check-circle';
                                            $iconColor = 'success';
                                            break;
                                        case 'reopened':
                                            $icon = 'fas fa-undo-alt';
                                            $iconColor = 'warning';
                                            break;
                                        case 'status_changed':
                                            $icon = 'fas fa-sync-alt';
                                            $iconColor = 'primary';
                                            break;
                                        case 'in_progress':
                                            $icon = 'fas fa-cogs';
                                            $iconColor = 'info';
                                            break;
                                        case 'closed':
                                            $icon = 'fas fa-archive';
                                            $iconColor = 'secondary';
                                            break;
                                    }

                                    // Ambil pesan
                                    $message = $data['message'] ?? '';
                                    if (empty($message) && isset($data['ticket_number'])) {
                                        switch($action) {
                                            case 'created':
                                                $message = "Ticket baru #{$data['ticket_number']} telah dibuat";
                                                break;
                                            case 'assigned':
                                                $message = "Ticket #{$data['ticket_number']} telah ditugaskan kepada Anda";
                                                break;
                                            case 'resolved':
                                                $message = "Ticket #{$data['ticket_number']} telah diselesaikan";
                                                break;
                                            case 'reopened':
                                                $message = "Ticket #{$data['ticket_number']} telah dibuka kembali";
                                                break;
                                            default:
                                                $message = $data['message'] ?? 'Notifikasi baru';
                                        }
                                    }

                                    // Tentukan URL untuk tombol lihat
                                    $url = $data['url'] ?? ($data['ticket_id'] ? route('tickets.show', $data['ticket_id']) : '#');
                                @endphp

                                <div class="list-group-item list-group-item-action border-bottom
                                    {{ $isUnread ? 'bg-light' : '' }}"
                                    data-notification-id="{{ $notification->id }}">

                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="{{ $icon }} me-2 text-{{ $iconColor }}"></i>

                                                <h6 class="mb-0 {{ $isUnread ? 'fw-bold' : '' }}">
                                                    {{ $message }}
                                                </h6>

                                                @if($isUnread)
                                                    <span class="badge bg-primary ms-2">Baru</span>
                                                @endif
                                            </div>

                                            @if(isset($data['ticket_number']) || isset($data['title']))
                                                <div class="mb-1">
                                                    @if(isset($data['ticket_number']))
                                                        <span class="badge bg-info me-1">
                                                            <i class="fas fa-ticket-alt me-1"></i>
                                                            {{ $data['ticket_number'] }}
                                                        </span>
                                                    @endif

                                                    @if(isset($data['title']))
                                                        <span class="text-muted">
                                                            {{ Str::limit($data['title'], 50) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if(isset($data['created_by']) || isset($data['updated_by']) || isset($data['reopened_by']))
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    @if(isset($data['created_by']))
                                                        Dibuat oleh: {{ $data['created_by'] }}
                                                    @elseif(isset($data['updated_by']))
                                                        Diperbarui oleh: {{ $data['updated_by'] }}
                                                    @elseif(isset($data['reopened_by']))
                                                        Dibuka kembali oleh: {{ $data['reopened_by'] }}
                                                    @endif
                                                </small>
                                            @endif

                                            @if(isset($data['reason']))
                                                <div class="mt-1">
                                                    <small class="text-warning">
                                                        <i class="fas fa-comment me-1"></i>
                                                        Alasan: {{ $data['reason'] }}
                                                    </small>
                                                </div>
                                            @endif

                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                    <span class="mx-1">•</span>
                                                    {{ $notification->created_at->format('d M Y H:i') }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column align-items-end gap-2">
                                            @if($isUnread)
                                                <button class="btn btn-sm btn-outline-success mark-read-btn"
                                                        data-id="{{ $notification->id }}"
                                                        title="Tandai sebagai dibaca">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif

                                            @if($url && $url !== '#')
                                                <a href="{{ $url }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> Lihat
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }}
                                    dari {{ $notifications->total() }} notifikasi
                                </div>
                                <div>
                                    {{ $notifications->links() }}
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-bell-slash fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">Tidak ada notifikasi</h5>
                            <p class="text-muted">Semua notifikasi sudah dibaca</p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-home me-1"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .list-group-item {
        transition: all 0.2s ease;
        padding: 1rem;
    }

    .list-group-item:hover {
        background-color: #f8f9fa !important;
    }

    .list-group-item.bg-light {
        border-left: 4px solid #0d6efd;
        background-color: #f8f9ff !important;
    }

    .mark-read-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .mark-read-btn:hover {
        background-color: #28a745;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF token setup
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Mark single notification as read
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            const listItem = this.closest('.list-group-item');
            const btn = this;

            fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    listItem.classList.remove('bg-light');
                    const title = listItem.querySelector('h6');
                    if (title) title.classList.remove('fw-bold');
                    const badge = listItem.querySelector('.badge.bg-primary');
                    if (badge) badge.remove();
                    btn.remove();

                    // Update unread count
                    updateUnreadCount();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Mark all notifications as read
    const markAllBtn = document.getElementById('markAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            if (confirm('Tandai semua notifikasi sebagai dibaca?')) {
                fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update all UI items
                        document.querySelectorAll('.list-group-item').forEach(item => {
                            item.classList.remove('bg-light');
                            const title = item.querySelector('h6');
                            if (title) title.classList.remove('fw-bold');
                            const badge = item.querySelector('.badge.bg-primary');
                            if (badge) badge.remove();
                            const markBtn = item.querySelector('.mark-read-btn');
                            if (markBtn) markBtn.remove();
                        });

                        // Update unread count to 0
                        document.getElementById('unreadCount').textContent = '0';
                        markAllBtn.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    }

    // Function to update unread count
    function updateUnreadCount() {
        fetch('/api/notifications/count', {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const unreadCountSpan = document.getElementById('unreadCount');
                if (unreadCountSpan) {
                    unreadCountSpan.textContent = data.count;
                }

                // Update header badge
                const headerBadge = document.getElementById('notifBadge');
                if (headerBadge) {
                    if (data.count > 0) {
                        headerBadge.textContent = data.count;
                        headerBadge.style.display = 'flex';
                    } else {
                        headerBadge.style.display = 'none';
                    }
                }

                // Show/hide mark all button
                if (markAllBtn) {
                    if (data.count > 0) {
                        markAllBtn.style.display = 'inline-flex';
                    } else {
                        markAllBtn.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>
@endpush
