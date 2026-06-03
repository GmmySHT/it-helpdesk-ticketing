<aside class="sidebar" id="sidebar">
    <div class="sidebar__inner">
        <!-- Sidebar Header with User Info -->
        <div class="sidebar__header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <h6 class="user-name">{{ auth()->user()->name }}</h6>
                    <span class="user-role">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
        </div>

        <nav class="sidebar__nav">
            <ul class="sidebar__menu" id="sidebarMenu">
                <!-- Dashboard -->
                <li class="menu__item">
                    <a href="{{ route('dashboard') }}"
                       class="menu__link {{ request()->routeIs('dashboard') ? 'menu__link--active' : '' }}"
                       data-tooltip="Dashboard">
                        <div class="menu__icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="menu__text">Dashboard</span>
                    </a>
                </li>

                <!-- Admin Menu -->
                @if(auth()->user()->role === 'admin')
                <li class="menu__section">
                    <span class="menu__section-label">Administrasi</span>
                </li>

                <li class="menu__item">
                    <a href="{{ route('users.index') }}"
                       class="menu__link {{ request()->routeIs('users.*') ? 'menu__link--active' : '' }}"
                       data-tooltip="Manajemen User">
                        <div class="menu__icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <span class="menu__text">Manajemen User</span>
                    </a>
                </li>

                <li class="menu__item">
                    <a href="{{ route('tickets.index') }}"
                       class="menu__link {{ request()->routeIs('tickets.index') && !request()->has('filter') ? 'menu__link--active' : '' }}"
                       data-tooltip="Semua Ticket">
                        <div class="menu__icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <span class="menu__text">Semua Ticket</span>
                    </a>
                </li>
                @endif

                <!-- User Menu -->
                @if(auth()->user()->role === 'user')
                <li class="menu__section">
                    <span class="menu__section-label">Ticket</span>
                </li>

                <li class="menu__item">
                    <a href="{{ route('tickets.index', ['filter' => 'my']) }}"
                       class="menu__link {{ request()->get('filter') === 'my' ? 'menu__link--active' : '' }}"
                       data-tooltip="Ticket Saya">
                        <div class="menu__icon">
                            <i class="fas fa-ticket-alt center"></i>
                        </div>
                        <span class="menu__text">Ticket Saya</span>
                    </a>
                </li>

                <li class="menu__item">
                    <a href="{{ route('tickets.create') }}"
                       class="menu__link {{ request()->routeIs('tickets.create') ? 'menu__link--active' : '' }}"
                       data-tooltip="Buat Ticket">
                        <div class="menu__icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="menu__text">Buat Ticket</span>
                    </a>
                </li>
                @endif

                <!-- IT Staff & IT -->
                @if(in_array(auth()->user()->role, ['it', 'it_staff']))
                <li class="menu__section">
                    <span class="menu__section-label">Helpdesk</span>
                </li>

                {{-- Semua Ticket (Read-Only) --}}
                <li class="menu__item">
                    <a href="{{ route('it.tickets.all') }}"
                    class="menu__link {{ request()->routeIs('it.tickets.all') ? 'menu__link--active' : '' }}"
                    data-tooltip="Semua Ticket">
                        <div class="menu__icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <span class="menu__text">Semua Ticket</span>
                    </a>
                </li>

                {{-- Ticket Saya (Bisa Dikelola) --}}
                <li class="menu__item">
                    <a href="{{ route('it.tickets.my') }}"
                    class="menu__link {{ request()->routeIs('it.tickets.my') ? 'menu__link--active' : '' }}"
                    data-tooltip="Ticket Saya">
                        <div class="menu__icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <span class="menu__text">Ticket Saya</span>
                        @php
                            $myTicketsCount = App\Models\Ticket::where('assigned_to', auth()->id())
                                ->whereNotIn('status', ['resolved', 'closed'])
                                ->count();
                        @endphp
                        @if($myTicketsCount > 0)
                            <span class="menu__badge">{{ $myTicketsCount }}</span>
                        @endif
                    </a>
                </li>

                <li class="menu__item">
                    <a href="{{ route('tickets.create') }}"
                    class="menu__link {{ request()->routeIs('tickets.create') ? 'menu__link--active' : '' }}"
                    data-tooltip="Buat Ticket">
                        <div class="menu__icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="menu__text">Buat Ticket</span>
                    </a>
                </li>
                @endif

                <!-- Menu Laporan -->
                @if(in_array(auth()->user()->role, ['admin', 'it', 'it_staff']))
                <li class="menu__section">
                    <span class="menu__section-label">Analisis</span>
                </li>

                <li class="menu__item">
                    <a href="{{ route('reports.index') }}"
                       class="menu__link {{ request()->routeIs('reports.*') ? 'menu__link--active' : '' }}"
                       data-tooltip="Laporan">
                        <div class="menu__icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="menu__text">Laporan</span>
                    </a>
                </li>
                @endif

                <!-- Menu tambahan untuk Admin -->
                @if(auth()->user()->role === 'admin')
                <li class="menu__item">
                    <a href="{{ route('tickets.create') }}"
                       class="menu__link {{ request()->routeIs('tickets.create') ? 'menu__link--active' : '' }}"
                       data-tooltip="Buat Ticket">
                        <div class="menu__icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="menu__text">Buat Ticket</span>
                    </a>
                </li>

                <li class="menu__item">
                    <a href="{{ route('categories.index') }}"
                       class="menu__link {{ request()->routeIs('categories.*') ? 'menu__link--active' : '' }}"
                       data-tooltip="Kategori">
                        <div class="menu__icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <span class="menu__text">Kategori</span>
                    </a>
                </li>
                @endif

                <!-- Menu untuk User biasa (Laporan) -->
                @if(auth()->user()->role === 'user')
                <li class="menu__section">
                    <span class="menu__section-label">Laporan</span>
                </li>

                <li class="menu__item">
                    <a href="{{ route('reports.index') }}"
                       class="menu__link {{ request()->routeIs('reports.*') ? 'menu__link--active' : '' }}"
                       data-tooltip="Laporan Saya">
                        <div class="menu__icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="menu__text">Laporan Saya</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar__footer">
            <div class="sidebar__help">
                <i class="fas fa-headset"></i>
                <span>Butuh Bantuan?</span>
            </div>
            <a href="#" class="sidebar__help-link">
                <i class="fas fa-life-ring me-2"></i>Hubungi Support
            </a>
        </div>
    </div>
</aside>

<script>
    // Sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const body = document.body;

        // Cari tombol toggle sidebar
        let toggleBtn = document.getElementById('toggleSidebarBtn');

        // Jika tidak ada, cari tombol dengan class tertentu
        if (!toggleBtn) {
            toggleBtn = document.querySelector('.toggle-sidebar-btn, .sidebar-toggle, [data-toggle="sidebar"]');
        }

        // Toggle sidebar function
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);

            // Tambahkan class ke body untuk styling
            if (isCollapsed) {
                body.classList.add('sidebar-collapsed');
            } else {
                body.classList.remove('sidebar-collapsed');
            }
        }

        // Event listener untuk tombol toggle
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSidebar();
            });
        }

        // Load saved state dari localStorage
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        }

        // Handle mobile
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
            sidebar.classList.add('collapsed');
        }

        // Create backdrop for mobile
        let backdrop = document.querySelector('.sidebar__backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar__backdrop';
            document.body.appendChild(backdrop);
        }

        // Show sidebar on mobile when menu button clicked
        const menuBtn = document.querySelector('.mobile-menu-btn, .menu-toggle');
        if (menuBtn) {
            menuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-show');
                backdrop.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('mobile-show') ? 'hidden' : '';
            });
        }

        // Close sidebar when clicking backdrop
        backdrop.addEventListener('click', function() {
            sidebar.classList.remove('mobile-show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        });

        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('mobile-show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });
</script>
