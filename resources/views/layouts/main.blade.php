
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Web RSIH')</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('img/loggweb.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/loggweb.png') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/phosphor/regular/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/fontawesome.css') }}" />

    <!-- Vendor CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <!-- Optional Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/plugins/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/plugins/notyf.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.bubble.css" rel="stylesheet" />

    @stack('styles')

    <style>
        .preview-content table {
            width: 100%;
            border-collapse: collapse;
        }

        .preview-content table,
        .preview-content th,
        .preview-content td {
            border: 1px solid #000;
        }

        .preview-content th,
        .preview-content td {
            padding: 8px;
            text-align: left;
        }
    </style>

    <!-- Main CSS -->
    <link href="{{ asset('assets/css/admin_style.css') }}" rel="stylesheet">
</head>

<body data-pc-theme="light">
    <!-- Loader -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <!-- SweetAlert -->
    @include('sweetalert::alert')

    <!-- Header -->
    @include('layouts.header')

    <!-- Sidebar -->
    @include('layouts.sidebar')


    <!-- Main Content -->
    <main id="main" class="glassmorph-wrapper">
        @yield('content')
    </main>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>SIRS</span></strong>. All Rights Reserved - 11.2025
    </div>
    <div class="credits">Designed by <a href="/">SIRS</a>
    </div>
  </footer><!-- End Footer -->


    <!-- Back to top -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/admin.js') }}"></script>

    <!-- Optional Plugins JS -->
    <script src="{{ asset('assets/admin/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/plugins/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/admin/js/plugins/notyf.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-table-better@1/dist/quill-table-better.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

    <!-- Dashboard Script -->
    <script src="{{ asset('assets/admin/js/pages/dashboard-analytics.js') }}"></script>

    @stack('scripts')


    <!-- Notification Script -->
 <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
$(document).ready(function() {
    // Cek jika user login
    @if(auth()->check())
    try {
        var pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            encrypted: true,
            forceTLS: true
        });

        // Channel name harus sesuai dengan Laravel
        var channelName = 'private-App.Models.User.{{ auth()->id() }}';
        console.log('Connecting to channel:', channelName);

        var channel = pusher.subscribe(channelName);

        // Listen untuk event notifikasi baru
        channel.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', function(data) {
            console.log('New notification received via Pusher:', data);

            // Update badge count
            updateNotificationBadge();

            // Show toast notification
            if (data.data && data.data.message) {
                showToast(data.data.message);
            }

            // Refresh dropdown jika terbuka
            if ($('#notifMenu').is(':visible')) {
                loadNotifications();
            }
        });

        console.log('Pusher initialized successfully for channel:', channelName);
    } catch (error) {
        console.error('Pusher initialization error:', error);
    }
    @endif

    // Fungsi untuk update badge notifikasi
    function updateNotificationBadge() {
        $.ajax({
            url: '{{ route("notifications.count") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var badge = $('#notifBadge');
                    var headerCount = $('#notifHeaderCount');

                    badge.text(response.count);

                    if (response.count > 0) {
                        badge.show();
                        if (headerCount.length) {
                            headerCount.text(response.count + ' Baru');
                        }
                    } else {
                        badge.hide();
                        if (headerCount.length) {
                            headerCount.text('0 Baru');
                        }
                    }
                }
            },
            error: function(xhr) {
                console.log('Error fetching notification count');
            }
        });
    }

    // Fungsi untuk menampilkan toast
    function showToast(message) {
        // Hapus toast sebelumnya jika ada
        $('.toast-notification').remove();

        var toast = $('<div class="toast-notification">' + message + '</div>');
        $('body').append(toast);

        setTimeout(function() {
            toast.addClass('show');
            setTimeout(function() {
                toast.remove();
            }, 5000);
        }, 100);
    }

    // Toggle dropdown notifikasi
    $('#notificationsDropdown').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var menu = $('#notifMenu');
        var isVisible = menu.is(':visible');

        // Tutup semua dropdown lain
        $('.dropdown__menu').not(menu).hide();

        if (isVisible) {
            menu.hide();
        } else {
            loadNotifications();
            menu.show();
        }
    });

    // Tutup dropdown ketika klik di luar
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#notificationsDropdown, #notifMenu').length) {
            $('#notifMenu').hide();
        }
    });

    // Event delegation untuk mark as read
    $(document).on('click', '.notif__mark-read', function() {
        var $button = $(this);
        var notificationId = $button.closest('.notif__item').data('notification-id');
        var $item = $button.closest('.notif__item');

        markAsRead(notificationId, $item, $button);
    });

    // Fungsi untuk load notifikasi
    function loadNotifications() {
        $.ajax({
            url: '{{ route("notifications.get") }}',
            type: 'GET',
            success: function(response) {
                if (response.success && response.notifications) {
                    updateNotificationList(response.notifications);
                }
            },
            error: function(xhr) {
                console.log('Error loading notifications');
                $('#notifList').html('<div class="notif__empty">Gagal memuat notifikasi</div>');
            }
        });
    }

    // Fungsi untuk update list notifikasi
    function updateNotificationList(notifications) {
        var $list = $('#notifList');

        if (!notifications || notifications.length === 0) {
            $list.html('<div class="notif__empty">Tidak ada notifikasi</div>');
            return;
        }

        var html = '';
        notifications.forEach(function(notification) {
            var itemClass = (!notification.read_at) ? 'notif__item notif__item--unread' : 'notif__item';
            var icon = notification.data.icon || 'fas fa-bell';
            var message = notification.data.message || 'Notifikasi baru';
            var timeAgo = notification.time_ago || 'Baru saja';

            html += `
                <div class="${itemClass}" data-notification-id="${notification.id}">
                    <div class="notif__icon">
                        <i class="${icon}"></i>
                    </div>
                    <div class="notif__content">
                        <p class="notif__text">${message}</p>
                        <span class="notif__time">${timeAgo}</span>
                    </div>
                    ${!notification.read_at ?
                        '<button class="notif__mark-read" title="Tandai dibaca">' +
                            '<i class="fas fa-check"></i>' +
                        '</button>' :
                        ''
                    }
                </div>
            `;
        });

        $list.html(html);
    }

    // Fungsi untuk mark notification as read
    function markAsRead(notificationId, $item, $button) {
        $.ajax({
            url: '{{ url("notifications") }}/' + notificationId + '/read',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $item.removeClass('notif__item--unread');
                    $button.remove();
                    updateNotificationBadge();
                }
            },
            error: function(xhr) {
                console.log('Error marking notification as read');
                showToast('Gagal menandai notifikasi sebagai dibaca');
            }
        });
    }

    // Load notifikasi pertama kali
    updateNotificationBadge();

    // Debug info
    console.log('Notification routes initialized');
    console.log('Notifications get route:', '{{ route("notifications.get") }}');
    console.log('Notifications count route:', '{{ route("notifications.count") }}');
});
</script>

<style>
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #4CAF50;
    color: white;
    padding: 12px 20px;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    z-index: 9999;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.3s, transform 0.3s;
    max-width: 300px;
    word-wrap: break-word;
}
.toast-notification.show {
    opacity: 1;
    transform: translateY(0);
}

.notif__item--unread {
    background-color: rgba(76, 175, 80, 0.1) !important;
    border-left: 3px solid #4CAF50 !important;
}

.notif__mark-read {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px 10px;
    font-size: 14px;
    transition: color 0.3s;
}

.notif__mark-read:hover {
    color: #4CAF50;
}

.notif__empty {
    padding: 20px;
    text-align: center;
    color: #6c757d;
    font-style: italic;
}

/* Pastikan dropdown z-index cukup tinggi */
.notif__menu {
    z-index: 10000 !important;
}
</style>
</body>

</html>
