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
                                @endphp
                                
                                <div class="list-group-item list-group-item-action border-bottom 
                                    {{ $isUnread ? 'bg-light' : '' }}" 
                                    data-notification-id="{{ $notification->id }}">
                                    
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center mb-1">
                                                @if(isset($data['icon']))
                                                    <i class="{{ $data['icon'] }} me-2 text-{{ $isUnread ? 'primary' : 'secondary' }}"></i>
                                                @else
                                                    <i class="fas fa-bell me-2 text-{{ $isUnread ? 'primary' : 'secondary' }}"></i>
                                                @endif
                                                
                                                <h6 class="mb-0 {{ $isUnread ? 'fw-bold' : '' }}">
                                                    {{ $data['message'] ?? 'Notifikasi baru' }}
                                                </h6>
                                                
                                                @if($isUnread)
                                                    <span class="badge bg-primary ms-2">Baru</span>
                                                @endif
                                            </div>
                                            
                                            @if(isset($data['ticket_number']) || isset($data['ticket_title']))
                                                <div class="mb-1">
                                                    @if(isset($data['ticket_number']))
                                                        <span class="badge bg-info me-1">
                                                            <i class="fas fa-ticket-alt me-1"></i>
                                                            {{ $data['ticket_number'] }}
                                                        </span>
                                                    @endif
                                                    
                                                    @if(isset($data['ticket_title']))
                                                        <span class="text-muted">
                                                            {{ Str::limit($data['ticket_title'], 50) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            @if(isset($data['assigned_by']))
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    Ditugaskan oleh: {{ $data['assigned_by'] }}
                                                </small>
                                            @endif
                                            
                                            @if(isset($data['user_name']))
                                                <small class="text-muted">
                                                    <i class="fas fa-user-plus me-1"></i>
                                                    Dibuat oleh: {{ $data['user_name'] }}
                                                </small>
                                            @endif
                                            
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
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
                                            
                                            @if(isset($data['url']))
                                                <a href="{{ $data['url'] }}" class="btn btn-sm btn-primary">
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
        border-left: 4px solid #4CAF50;
    }
    
    .mark-read-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // CSRF token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Mark single notification as read
    $(document).on('click', '.mark-read-btn', function() {
        const button = $(this);
        const notificationId = button.data('id');
        const listItem = button.closest('.list-group-item');
        
        $.ajax({
            url: '/api/notifications/' + notificationId + '/read',
            type: 'POST',
            beforeSend: function() {
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    listItem.removeClass('bg-light');
                    listItem.find('h6').removeClass('fw-bold');
                    listItem.find('.badge.bg-primary').remove();
                    button.remove();
                    
                    // Update unread count
                    updateUnreadCount();
                    
                    // Show success message
                    showToast('Notifikasi ditandai sebagai dibaca', 'success');
                }
            },
            error: function() {
                button.prop('disabled', false).html('<i class="fas fa-check"></i>');
                showToast('Gagal menandai notifikasi', 'error');
            }
        });
    });

    // Mark all notifications as read
    $('#markAllRead').click(function() {
        const button = $(this);
        
        Swal.fire({
            title: 'Tandai Semua Dibaca?',
            text: 'Semua notifikasi akan ditandai sebagai sudah dibaca',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4CAF50',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tandai Semua',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/notifications/read-all',
                    type: 'POST',
                    beforeSend: function() {
                        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update all UI items
                            $('.list-group-item').removeClass('bg-light');
                            $('.list-group-item h6').removeClass('fw-bold');
                            $('.list-group-item .badge.bg-primary').remove();
                            $('.mark-read-btn').remove();
                            
                            // Update unread count to 0
                            $('#unreadCount').text('0');
                            button.hide();
                            
                            // Show success message
                            showToast('Semua notifikasi ditandai sebagai dibaca', 'success');
                        }
                    },
                    error: function() {
                        button.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca');
                        showToast('Gagal menandai semua notifikasi', 'error');
                    }
                });
            }
        });
    });

    // Function to update unread count
    function updateUnreadCount() {
        $.ajax({
            url: '/api/notifications/count',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#unreadCount').text(response.count);
                    
                    // Show/hide mark all button
                    const markAllBtn = $('#markAllRead');
                    if (response.count > 0) {
                        markAllBtn.show();
                    } else {
                        markAllBtn.hide();
                    }
                    
                    // Update header badge
                    updateHeaderBadge();
                }
            }
        });
    }

    // Function to update header badge
    function updateHeaderBadge() {
        const headerBadge = $('#notifBadge');
        const headerCount = $('#notifHeaderCount');
        
        if (headerBadge.length) {
            headerBadge.text($('#unreadCount').text());
            const count = parseInt($('#unreadCount').text());
            
            if (count > 0) {
                headerBadge.show();
                if (headerCount.length) {
                    headerCount.text(count + ' Baru');
                }
            } else {
                headerBadge.hide();
                if (headerCount.length) {
                    headerCount.text('0 Baru');
                }
            }
        }
    }

    // Function to show toast notifications
    function showToast(message, type = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Initialize tooltips
    $('[title]').tooltip();
});
</script>
@endpush