<header class="header">
    <div class="header__container">
        <!-- Logo & Toggle -->
        <div class="header__brand">
            <a href="{{ route('dashboard') }}" class="brand__link">
                <div class="brand__logo">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="RSIH"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="brand__fallback">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>
                <div class="brand__text">
                    <span class="brand__title">RS Intan Husada</span>
                    <span class="brand__subtitle">Ticketing System</span>
                </div>
            </a>

            <button class="sidebar__toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="header__search">
            <div class="search__container">
                <form class="search__form" method="GET" action="{{ route('tickets.search') }}">
                    <div class="search__group">
                        <i class="fas fa-search search__icon"></i>
                        <input type="text" name="q" class="search__input"
                               placeholder="Cari ticket, user, atau kategori..."
                               value="{{ request('q') }}">
                        <button type="button" class="search__clear">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <button type="submit" class="search__submit">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="header__nav">
            <ul class="nav__list">
                <!-- Mobile Search Toggle -->
                <li class="nav__item nav__item--mobile">
                    <button class="nav__button" id="mobileSearchToggle">
                        <i class="fas fa-search"></i>
                    </button>
                </li>

                <!-- Notifications -->
                <li class="nav__item nav__item--dropdown">
                    <button class="nav__button nav__button--notif" id="notificationsDropdown">
                        <i class="fas fa-bell"></i>
                        <span class="notif__badge" id="notifBadge" style="display:none">0</span>
                    </button>

                    <div class="dropdown__menu notif__menu" id="notifMenu" style="display:none;">
                        <div class="dropdown__header">
                            <h3>Notifikasi</h3>
                            <span class="badge badge--primary" id="notifHeaderCount">0 Baru</span>
                        </div>

                        <div class="notif__list" id="notifList">
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-bell-slash me-1"></i> Tidak ada notifikasi
                            </div>
                        </div>

                        <div class="dropdown__footer">
                            <a href="{{ route('notifications.page') }}" class="link__view-all">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
                    </div>
                </li>

                <!-- User Profile -->
                <li class="nav__item nav__item--dropdown">
                    <button class="nav__button nav__button--profile" id="profileDropdown">
                        <div class="user__avatar">
                            <div class="avatar__image">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="user__info">
                            <span class="user__name">{{ Auth::user()->name }}</span>
                            <span class="user__role">{{ Auth::user()->role }}</span>
                        </div>
                        <i class="fas fa-chevron-down user__dropdown-icon"></i>
                    </button>

                    <div class="dropdown__menu profile__menu">
                        <div class="profile__header">
                            <div class="profile__avatar">
                                <div class="avatar__image avatar__image--large">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="profile__info">
                                <h4 class="profile__name">{{ Auth::user()->name }}</h4>
                                <span class="profile__email">{{ Auth::user()->email }}</span>
                            </div>
                        </div>

                        <div class="dropdown__divider"></div>

                        <a href="{{ route('profile.edit') }}" class="dropdown__item">
                            <i class="fas fa-user-cog"></i>
                            <span>Edit Profil</span>
                        </a>

                        <div class="dropdown__divider"></div>

                        <form method="POST" action="{{ route('logout') }}" class="dropdown__form">
                            @csrf
                            <button type="submit" class="dropdown__item dropdown__item--logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Mobile Search -->
    <div class="mobile-search" id="mobileSearch">
        <div class="mobile-search__container">
            <form class="mobile-search__form" method="GET" action="{{ route('tickets.search') }}">
                <div class="search__group">
                    <i class="fas fa-search search__icon"></i>
                    <input type="text" name="q" class="search__input" placeholder="Cari ticket...">
                    <button type="button" class="search__close" id="mobileSearchClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</header>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/header.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sidebar.css') }}">

<style>
/* ==================== NOTIFICATION POPUP STYLES ==================== */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 380px;
}

.notification-popup {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    min-width: 300px;
    max-width: 380px;
    animation: slideInRight 0.3s ease forwards;
    position: relative;
    overflow: hidden;
    border-left: 4px solid;
}

.notification-popup--success {
    border-left-color: #28a745;
}

.notification-popup--error {
    border-left-color: #dc3545;
}

.notification-popup--warning {
    border-left-color: #ffc107;
}

.notification-popup--info {
    border-left-color: #17a2b8;
}

.notification-popup--primary {
    border-left-color: #18b5a0;
}

.notification-popup__icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.notification-popup__icon.success {
    background: #d4edda;
    color: #28a745;
}

.notification-popup__icon.error {
    background: #f8d7da;
    color: #dc3545;
}

.notification-popup__icon.warning {
    background: #fff3cd;
    color: #ffc107;
}

.notification-popup__icon.info {
    background: #d1ecf1;
    color: #17a2b8;
}

.notification-popup__icon.primary {
    background: #e0f2fe;
    color: #18b5a0;
}

.notification-popup__content {
    flex: 1;
}

.notification-popup__title {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 4px;
}

.notification-popup__message {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
}

.notification-popup__time {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 6px;
}

.notification-popup__close {
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.notification-popup__close:hover {
    background: #f3f4f6;
    color: #dc3545;
}

.notification-popup__progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: #18b5a0;
    animation: progress 10s linear forwards;
}

.notification-popup--success .notification-popup__progress {
    background: #28a745;
}

.notification-popup--error .notification-popup__progress {
    background: #dc3545;
}

.notification-popup--warning .notification-popup__progress {
    background: #ffc107;
}

.notification-popup--info .notification-popup__progress {
    background: #17a2b8;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

.notification-popup.hide {
    animation: slideOutRight 0.3s ease forwards;
}

/* Notification dropdown styles */
.notif__menu {
    width: 380px;
    max-width: calc(100vw - 20px);
    padding: 0;
}

.notif__list {
    max-height: 400px;
    overflow-y: auto;
}

.notif__item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    transition: background 0.2s ease;
    position: relative;
}

.notif__item:hover {
    background-color: #f9fafb;
}

.notif__item--unread {
    background-color: #f0f9ff;
}

.notif__item--unread:hover {
    background-color: #e0f2fe;
}

.notif__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}

.notif__content {
    flex: 1;
    min-width: 0;
}

.notif__message {
    font-size: 13px;
    color: #1f2937;
    margin-bottom: 4px;
    line-height: 1.4;
    word-break: break-word;
}

.notif__item--unread .notif__message {
    font-weight: 600;
}

.notif__time {
    font-size: 11px;
    color: #9ca3af;
}

.notif__mark-read {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.notif__mark-read:hover {
    background-color: #10b981;
    color: white;
}

.dropdown__header {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown__header h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

.dropdown__footer {
    padding: 10px 16px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.link__view-all {
    font-size: 13px;
    color: #18b5a0;
    text-decoration: none;
}

.link__view-all:hover {
    text-decoration: underline;
}

/* Notif badge */
.notif__badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 600;
    min-width: 18px;
    height: 18px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}

/* Mobile search */
.mobile-search {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: flex-start;
    justify-content: center;
    padding-top: 60px;
}

.mobile-search.active {
    display: flex;
}

.mobile-search__container {
    width: 90%;
    max-width: 400px;
}

.mobile-search__form .search__group {
    display: flex;
    background: white;
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.mobile-search__form .search__input {
    flex: 1;
    border: none;
    padding: 12px 16px;
    font-size: 16px;
}

.nav__item--mobile {
    display: none;
}

@media (max-width: 768px) {
    .nav__item--mobile {
        display: block;
    }

    .notif__menu {
        width: 320px;
        right: 0;
        left: auto;
    }

    .toast-container {
        top: 10px;
        right: 10px;
        left: 10px;
        max-width: none;
    }

    .notification-popup {
        max-width: none;
        width: calc(100% - 20px);
    }
}

.pulse-animation {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==================== POPUP NOTIFICATION FUNCTION ====================
    window.showNotificationPopup = function(title, message, type = 'primary', duration = 10000) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const popup = document.createElement('div');
        popup.className = `notification-popup notification-popup--${type}`;

        let iconHtml = '';
        switch(type) {
            case 'success': iconHtml = '<i class="fas fa-check-circle"></i>'; break;
            case 'error': iconHtml = '<i class="fas fa-times-circle"></i>'; break;
            case 'warning': iconHtml = '<i class="fas fa-exclamation-triangle"></i>'; break;
            case 'info': iconHtml = '<i class="fas fa-info-circle"></i>'; break;
            default: iconHtml = '<i class="fas fa-bell"></i>'; type = 'primary';
        }

        const timeString = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        popup.innerHTML = `
            <div class="notification-popup__icon ${type}">${iconHtml}</div>
            <div class="notification-popup__content">
                <div class="notification-popup__title">${escapeHtml(title)}</div>
                <div class="notification-popup__message">${escapeHtml(message)}</div>
                <div class="notification-popup__time">${timeString}</div>
            </div>
            <button class="notification-popup__close" onclick="this.closest('.notification-popup').remove()">
                <i class="fas fa-times"></i>
            </button>
            <div class="notification-popup__progress"></div>
        `;

        container.appendChild(popup);

        const timeout = setTimeout(() => {
            if (popup && popup.parentNode) {
                popup.classList.add('hide');
                setTimeout(() => { if (popup && popup.parentNode) popup.remove(); }, 300);
            }
        }, duration);

        popup.querySelector('.notification-popup__close').addEventListener('click', () => clearTimeout(timeout));
        return popup;
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==================== NOTIFICATION FUNCTIONS ====================
    const notifBtn = document.getElementById('notificationsDropdown');
    const notifMenu = document.getElementById('notifMenu');
    const notifList = document.getElementById('notifList');
    const notifBadge = document.getElementById('notifBadge');
    const notifHeaderCount = document.getElementById('notifHeaderCount');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // VARIABEL PENTING UNTUK MENCEGAH DUPLIKASI POPUP
    let lastShownNotificationIds = new Set(); // Menyimpan ID notifikasi yang sudah ditampilkan popupnya
    let pollingInterval;
    let isFirstLoad = true;
    let lastNotificationCount = 0;
    let popupQueue = [];
    let isShowingPopup = false;

    // Fungsi untuk mengambil notifikasi dari server
    async function loadNotifications() {
        try {
            const response = await fetch('/api/notifications', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();

            if (data.success) {
                const unreadCount = data.notifications.filter(n => !n.read_at).length;
                updateNotificationList(data.notifications);
                updateNotificationBadge(unreadCount);

                // HANYA tampilkan popup untuk notifikasi BARU dan BELUM DITAMPILKAN
                if (!isFirstLoad) {
                    const newNotifications = data.notifications.filter(n =>
                        !lastShownNotificationIds.has(n.id) && !n.read_at
                    );

                    if (newNotifications.length > 0) {
                        // Simpan ID notifikasi yang sudah ditampilkan
                        newNotifications.forEach(n => lastShownNotificationIds.add(n.id));

                        // Tampilkan popup untuk notifikasi baru
                        newNotifications.forEach(notif => {
                            const dataNotif = notif.data || {};
                            const title = getNotificationTitle(dataNotif);
                            const message = getNotificationMessage(dataNotif);
                            const type = getNotificationType(dataNotif);
                            showNotificationPopup(title, message, type);
                        });
                    }
                }

                isFirstLoad = false;
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    function getNotificationTitle(data) {
        const action = data.action || data.status || '';
        switch(action) {
            case 'created': return 'Ticket Baru';
            case 'assigned': return 'Ticket Ditugaskan';
            case 'taken': return 'Ticket Diambil';
            case 'resolved': return 'Ticket Selesai';
            case 'reopened': return 'Ticket Dibuka Kembali';
            default: return 'Notifikasi';
        }
    }

    function getNotificationMessage(data) {
        if (data.message) return data.message;
        const action = data.action || data.status || '';
        const ticketNumber = data.ticket_number ? `#${data.ticket_number}` : '';
        switch(action) {
            case 'created': return `Ticket ${ticketNumber} telah dibuat`;
            case 'assigned': return `Ticket ${ticketNumber} ditugaskan kepada Anda`;
            case 'taken': return `Ticket ${ticketNumber} telah diambil`;
            case 'resolved': return `Ticket ${ticketNumber} telah diselesaikan`;
            case 'reopened': return `Ticket ${ticketNumber} dibuka kembali`;
            default: return 'Ada notifikasi baru';
        }
    }

    function getNotificationType(data) {
        const action = data.action || data.status || '';
        switch(action) {
            case 'created': return 'info';
            case 'assigned': return 'warning';
            case 'taken': return 'primary';
            case 'resolved': return 'success';
            case 'reopened': return 'warning';
            default: return 'info';
        }
    }

    function updateNotificationBadge(count) {
        if (count > 0) {
            notifBadge.textContent = count;
            notifBadge.style.display = 'flex';
            if (notifHeaderCount) notifHeaderCount.textContent = count + ' Baru';
        } else {
            notifBadge.style.display = 'none';
            if (notifHeaderCount) notifHeaderCount.textContent = '0 Baru';
        }
        lastNotificationCount = count;
    }

    function updateNotificationList(notifications) {
        if (!notifList) return;

        if (!notifications || notifications.length === 0) {
            notifList.innerHTML = `<div class="text-center py-4 text-muted">
                <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                <span>Tidak ada notifikasi</span>
            </div>`;
            return;
        }

        let html = '';
        notifications.slice(0, 10).forEach(notif => {
            const isUnread = !notif.read_at;
            const data = notif.data || {};
            const timeAgo = notif.time_ago || new Date(notif.created_at).toLocaleString('id-ID');

            let icon = 'fas fa-bell';
            let iconColor = '#6c757d';
            const action = data.action || data.status || '';

            switch(action) {
                case 'created': icon = 'fas fa-plus-circle'; iconColor = '#28a745'; break;
                case 'assigned': icon = 'fas fa-user-plus'; iconColor = '#ffc107'; break;
                case 'taken': icon = 'fas fa-hand-paper'; iconColor = '#17a2b8'; break;
                case 'resolved': icon = 'fas fa-check-circle'; iconColor = '#28a745'; break;
                case 'reopened': icon = 'fas fa-undo-alt'; iconColor = '#ffc107'; break;
            }

            let message = data.message || getNotificationMessage(data);

            html += `
                <div class="notif__item ${isUnread ? 'notif__item--unread' : ''}" data-id="${notif.id}">
                    <div class="notif__icon" style="background: ${iconColor}20; color: ${iconColor}">
                        <i class="${icon}"></i>
                    </div>
                    <div class="notif__content">
                        <div class="notif__message">${escapeHtml(message)}</div>
                        <div class="notif__time">${timeAgo}</div>
                    </div>
                    ${isUnread ? `<button class="notif__mark-read" onclick="markNotificationRead('${notif.id}')"><i class="fas fa-check"></i></button>` : ''}
                </div>
            `;
        });

        notifList.innerHTML = html;
    }

    window.markNotificationRead = async function(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (data.success) {
                loadNotifications();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };

    // HANYA update count, TANPA popup
    async function updateNotificationCountOnly() {
        try {
            const response = await fetch('/api/notifications/count', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (data.success) {
                updateNotificationBadge(data.count);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Start polling - hanya update badge, popup hanya dari loadNotifications
    function startPolling() {
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(() => {
            updateNotificationCountOnly();
            loadNotifications(); // Ini akan mengecek dan menampilkan popup untuk notifikasi baru
        }, 10000);
    }

    // ==================== DROPDOWN TOGGLE ====================
    if (notifBtn && notifMenu) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = notifMenu.style.display === 'block';
            document.querySelectorAll('.dropdown__menu').forEach(menu => {
                if (menu !== notifMenu) menu.style.display = 'none';
            });
            notifMenu.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) loadNotifications();
        });
    }

    // Profile dropdown
    const profileBtn = document.getElementById('profileDropdown');
    const profileMenu = document.querySelector('.profile__menu');

    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = profileMenu.style.display === 'block';
            if (notifMenu) notifMenu.style.display = 'none';
            profileMenu.style.display = isVisible ? 'none' : 'block';
        });
    }

    // Close dropdowns
    document.addEventListener('click', function(e) {
        if (notifMenu && !notifBtn?.contains(e.target) && !notifMenu.contains(e.target)) {
            notifMenu.style.display = 'none';
        }
        if (profileMenu && !profileBtn?.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.style.display = 'none';
        }
    });

    // ==================== MOBILE SEARCH ====================
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const mobileSearch = document.getElementById('mobileSearch');
    const mobileSearchClose = document.getElementById('mobileSearchClose');

    if (mobileSearchToggle && mobileSearch) {
        mobileSearchToggle.addEventListener('click', () => mobileSearch.classList.add('active'));
    }
    if (mobileSearchClose && mobileSearch) {
        mobileSearchClose.addEventListener('click', () => mobileSearch.classList.remove('active'));
    }

    // ==================== SEARCH CLEAR ====================
    const searchInput = document.querySelector('.search__input');
    const searchClear = document.querySelector('.search__clear');

    if (searchClear && searchInput) {
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.focus();
        });
        searchInput.addEventListener('input', () => {
            searchClear.style.display = searchInput.value.length > 0 ? 'flex' : 'none';
        });
        searchClear.style.display = searchInput.value.length > 0 ? 'flex' : 'none';
    }

    // ==================== SIDEBAR TOGGLE ====================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
    }

    // Start polling dan load awal
    startPolling();
    loadNotifications();
});
</script>
