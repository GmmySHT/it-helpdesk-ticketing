class AppNavigation {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebarBackdrop = document.getElementById('sidebarBackdrop');
        this.mobileSearchToggle = document.getElementById('mobileSearchToggle');
        this.mobileSearchClose = document.getElementById('mobileSearchClose');
        this.mobileSearch = document.getElementById('mobileSearch');
        this.notificationsDropdown = document.getElementById('notificationsDropdown');
        this.profileDropdown = document.getElementById('profileDropdown');

        // 🔥 ADDED — Element notifikasi
        this.notifBadge = document.getElementById('notifBadge');
        this.notifList = document.getElementById('notifList');
        this.notifHeaderCount = document.getElementById('notifHeaderCount');

        this.init();
    }

    init() {
        this.createBackdrop();
        this.setupEventListeners();
        this.setupSearchFunctionality();
        this.loadSavedState();
        this.setupResizeHandler();
        this.setupSidebarTooltips();

        // 🔥 ADDED: Memulai fetch notifikasi otomatis
        this.updateNotifications();
        setInterval(() => this.updateNotifications(), 25000);
    }

    // ----------------------------------------------------------
    // 🔥 ADDED — FETCH NOTIFIKASI & RENDER
    // ----------------------------------------------------------
    async updateNotifications() {
        if (!this.notifList || !this.notifBadge) return;

        const url = "/tickets/notifications"; // route('tickets.notifications')

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            const data = await res.json();
            const count = data.count || 0;

            // Update badge
            if (count > 0) {
                this.notifBadge.style.display = 'inline-block';
                this.notifBadge.textContent = count;
            } else {
                this.notifBadge.style.display = 'none';
            }

            // Header count
            if (this.notifHeaderCount) {
                this.notifHeaderCount.textContent = `${count} Baru`;
            }

            // Render list notifikasi
            this.notifList.innerHTML = "";

            if (!data.items || data.items.length === 0) {
                this.notifList.innerHTML = `
                    <div class="notif__empty">Belum ada notifikasi.</div>
                `;
                return;
            }

            data.items.forEach(item => {
                this.notifList.appendChild(this.renderNotifItem(item));
            });

        } catch (err) {
            console.error("Failed to load notifications", err);
            this.notifList.innerHTML = `<div class="notif__empty">Gagal memuat notifikasi.</div>`;
        }
    }

    renderNotifItem(item) {
        const wrapper = document.createElement('div');
        wrapper.className = "notif__item";

        let message = "";
        switch (item.action) {
            case "created":
                message = `Ticket <strong>${item.ticket_number}</strong> dibuat`;
                break;
            case "assigned":
                let to = item.meta?.new_assigned_name ?? "User";
                message = `Ticket <strong>${item.ticket_number}</strong> ditugaskan ke ${to}`;
                break;
            case "status_changed":
                message = `Status ${item.ticket_number} berubah jadi ${item.meta?.new_status}`;
                break;
            default:
                message = item.notes ?? `Aktivitas pada ticket ${item.ticket_number}`;
        }

        wrapper.innerHTML = `
            <div class="notif__icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="notif__content">
                <p class="notif__text">${message}</p>
                <span class="notif__time">${item.time_ago ?? ""}</span>
            </div>
        `;

        wrapper.addEventListener("click", () => {
            window.location.href = `/tickets/${item.ticket_id}`;
        });

        return wrapper;
    }

    // ----------------------------------------------------------
    // 🔥 END OF ADDED BLOCK
    // ----------------------------------------------------------

    setupEventListeners() {
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            });
        }

        if (this.mobileSearchToggle) {
            this.mobileSearchToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showMobileSearch();
            });
        }

        if (this.mobileSearchClose) {
            this.mobileSearchClose.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hideMobileSearch();
            });
        }

        // NOTIF DROPDOWN tetap pakai toggle bawaan kamu
        if (this.notificationsDropdown) {
            this.notificationsDropdown.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleDropdown('notifications');
            });
        }

        if (this.profileDropdown) {
            this.profileDropdown.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleDropdown('profile');
            });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.nav__item--dropdown') && !e.target.closest('.dropdown__menu')) {
                this.closeAllDropdowns();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
                this.hideMobileSearch();
                this.closeMobileSidebar();
            }
        });
    }

    // ==========================================================
    // SEMUA METHOD LAIN TIDAK DIUBAH (SAMA PERSIS DENGAN PUNYAMU)
    // ==========================================================

    createBackdrop() {
        if (!document.getElementById('sidebarBackdrop')) {
            const backdrop = document.createElement('div');
            backdrop.id = 'sidebarBackdrop';
            backdrop.className = 'sidebar__backdrop';
            backdrop.addEventListener('click', () => this.closeMobileSidebar());
            document.body.appendChild(backdrop);
            this.sidebarBackdrop = backdrop;
        }
    }

    toggleSidebar() {
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            const isShowing = this.sidebar.classList.contains('mobile-show');
            if (isShowing) {
                this.closeMobileSidebar();
            } else {
                this.openMobileSidebar();
            }
        } else {
            const isCollapsed = this.sidebar.classList.contains('collapsed');
            if (isCollapsed) {
                this.expandSidebar();
            } else {
                this.collapseSidebar();
            }
        }
    }

    openMobileSidebar() {
        this.sidebar.classList.add('mobile-show');
        if (this.sidebarBackdrop) {
            this.sidebarBackdrop.classList.add('show');
        }
        document.body.style.overflow = 'hidden';
    }

    closeMobileSidebar() {
        this.sidebar.classList.remove('mobile-show');
        if (this.sidebarBackdrop) {
            this.sidebarBackdrop.classList.remove('show');
        }
        document.body.style.overflow = '';
    }

    collapseSidebar() {
        this.sidebar.classList.add('collapsed');
        this.saveSidebarState();
    }

    expandSidebar() {
        this.sidebar.classList.remove('collapsed');
        this.saveSidebarState();
    }

    showMobileSearch() {
        if (this.mobileSearch) {
            this.mobileSearch.style.display = 'block';
            this.closeAllDropdowns();
            this.closeMobileSidebar();

            setTimeout(() => {
                const input = this.mobileSearch.querySelector('.search__input');
                if (input) {
                    input.focus();
                    input.select();
                }
            }, 50);
        }
    }

    hideMobileSearch() {
        if (this.mobileSearch) {
            this.mobileSearch.style.display = 'none';
        }
    }

    toggleDropdown(type) {
        let dropdown, menu;

        if (type === 'notifications') {
            dropdown = this.notificationsDropdown.closest('.nav__item--dropdown');
            menu = dropdown?.querySelector('.dropdown__menu');
        } else if (type === 'profile') {
            dropdown = this.profileDropdown.closest('.nav__item--dropdown');
            menu = dropdown?.querySelector('.dropdown__menu');
        }

        if (!dropdown || !menu) return;

        const isOpen = dropdown.classList.contains('show');

        this.closeAllDropdowns();

        if (!isOpen) {
            dropdown.classList.add('show');
            menu.style.display = 'block';
            this.adjustDropdownPosition(menu);

            const closeHandler = (e) => {
                if (!e.target.closest('.nav__item--dropdown') && !e.target.closest('.dropdown__menu')) {
                    this.closeAllDropdowns();
                    document.removeEventListener('click', closeHandler);
                }
            };

            setTimeout(() => {
                document.addEventListener('click', closeHandler);
            }, 0);
        }
    }

    closeAllDropdowns() {
        const dropdowns = document.querySelectorAll('.nav__item--dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
            const menu = dropdown.querySelector('.dropdown__menu');
            if (menu) menu.style.display = 'none';
        });
    }

    adjustDropdownPosition(menu) {
        if (!menu) return;

        const rect = menu.getBoundingClientRect();
        const viewportHeight = window.innerHeight;

        menu.style.top = '';
        menu.style.bottom = '';
        menu.style.marginTop = '';
        menu.style.marginBottom = '';

        if (rect.bottom > viewportHeight - 20) {
            menu.style.bottom = '100%';
            menu.style.marginBottom = '0.5rem';
        } else {
            menu.style.top = '100%';
            menu.style.marginTop = '0.5rem';
        }

        if (window.innerWidth <= 768) {
            menu.style.left = '1rem';
            menu.style.right = '1rem';
        } else {
            menu.style.left = '';
            menu.style.right = '0';
        }
    }

    setupSearchFunctionality() {
        const searchInputs = document.querySelectorAll('.search__input');
        const searchClearButtons = document.querySelectorAll('.search__clear');

        searchInputs.forEach((input, index) => {
            this.updateClearButtonVisibility(input, searchClearButtons[index]);

            input.addEventListener('input', (e) => {
                this.updateClearButtonVisibility(e.target, searchClearButtons[index]);
            });

            input.addEventListener('focus', () => {
                this.updateClearButtonVisibility(input, searchClearButtons[index]);
            });

            input.addEventListener('blur', () => {
                setTimeout(() => {
                    this.updateClearButtonVisibility(input, searchClearButtons[index]);
                }, 150);
            });

            const clearBtn = searchClearButtons[index];
            if (clearBtn) {
                clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    input.value = '';
                    input.focus();
                    this.updateClearButtonVisibility(input, clearBtn);
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }
        });
    }

    updateClearButtonVisibility(input, clearBtn) {
        if (!clearBtn) return;
        clearBtn.style.display = input.value ? 'flex' : 'none';
    }

    setupSidebarTooltips() {
        const menuLinks = document.querySelectorAll('.menu__link');
        menuLinks.forEach(link => {
            const textElement = link.querySelector('.menu__text');
            if (textElement) {
                link.setAttribute('data-tooltip', textElement.textContent.trim());
            }
        });
    }

    saveSidebarState() {
        if (this.sidebar) {
            const isCollapsed = this.sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
    }

    loadSavedState() {
        if (this.sidebar && window.innerWidth > 768) {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                this.sidebar.classList.add('collapsed');
            } else {
                this.sidebar.classList.remove('collapsed');
            }
        }
    }

    setupResizeHandler() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 100);
        });
    }

    handleResize() {
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            this.sidebar.classList.remove('collapsed');
        } else {
            this.closeMobileSidebar();
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                this.sidebar.classList.add('collapsed');
            } else {
                this.sidebar.classList.remove('collapsed');
            }
        }

        this.closeAllDropdowns();

        const openDropdowns = document.querySelectorAll('.nav__item--dropdown.show');
        openDropdowns.forEach(dropdown => {
            const menu = dropdown.querySelector('.dropdown__menu');
            if (menu && menu.style.display === 'block') {
                this.adjustDropdownPosition(menu);
            }
        });

        if (!isMobile && this.mobileSearch) {
            this.hideMobileSearch();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        window.appNavigation = new AppNavigation();
        console.log('AppNavigation initialized');
    }, 100);
});

document.addEventListener('click', (e) => {
    if (e.target.closest('.dropdown__menu')) {
        e.stopPropagation();
    }
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        if (window.appNavigation) {
            window.appNavigation.handleResize();
        }
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AppNavigation;
}
