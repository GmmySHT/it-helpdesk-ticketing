<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Sistem Manajemen Ticket</title>
    <link rel="icon" href="{{ asset('img/login.jpg') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: url("{{ asset('assets/img/dasboard.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            /* background: linear-gradient(135deg, #2b2e3a 0%, #764ba2 50%, #1e3c72 100%); */
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.22) 0%, transparent 50%);
            animation: pulse 8s ease-in-out infinite;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 80% 50%, rgba(24, 181, 160, 0.1) 0%, transparent 50%);
            animation: pulse 8s ease-in-out infinite reverse;
            pointer-events: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.738);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Main Container */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Glass Card */
        .login-card {
            max-width: 1200px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Side - Branding */
        .login-brand {
            background: linear-gradient(135deg, rgba(40, 196, 175, 0.707), rgba(10, 79, 182, 0.476));
            padding: 3rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .brand-logo img {
            width: 120px;
            height: auto;
            filter: drop-shadow(0 4px 20px rgba(0, 0, 0, 0.2));
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .brand-features {
            margin-top: 2rem;
            position: relative;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: white;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .feature-text {
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Right Side - Form */
        .login-form {
            padding: 3rem;
            background: rgba(255, 255, 255, 0.95);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .form-control-custom {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #18b5a0;
            box-shadow: 0 0 0 3px rgba(24, 181, 160, 0.1);
        }

        .form-control-custom.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* Toggle Password */
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .toggle-password:hover {
            color: #18b5a0;
        }

        /* Button Login */
        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #18b5a0, #0d6efd);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(24, 181, 160, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Developer Note */
        .developer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #6c757d;
        }

        .developer-note a {
            color: #18b5a0;
            text-decoration: none;
            font-weight: 600;
        }

        .developer-note a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                max-width: 450px;
            }

            .login-brand {
                display: none;
            }

            .login-form {
                padding: 2rem;
            }

            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Left Side - Branding -->
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="login-brand">
                        <div class="brand-logo">
                            <img src="{{ asset('assets/img/foto.png') }}" alt="Logo">
                        </div>
                        <h2 class="brand-title">Sistem Informasi<br>Manajemen Ticket</h2>
                        <p class="brand-subtitle">
                            Solusi terintegrasi untuk mengelola permintaan bantuan IT dengan efisien dan profesional.
                        </p>
                        <div class="brand-features">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div class="feature-text">Manajemen Ticket Terpusat</div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="feature-text">Laporan & Analisis Real-time</div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-text">Keamanan Data Terjamin</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Form -->
                <div class="col-lg-6">
                    <div class="login-form">
                        <div class="form-header">
                            <h3 class="form-title">Selamat Datang Kembali</h3>
                            <p class="form-subtitle">Silakan masukkan kredensial Anda untuk melanjutkan</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <!-- ✅ MODIFIKASI: Ubah dari email menjadi login (username atau email) -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user me-2"></i>Email atau Username
                                </label>
                                <div class="input-group-custom">
                                    <i class="fas fa-envelope"></i>
                                    <input type="text"
                                           name="login"
                                           id="login"
                                           class="form-control-custom @error('login') error @enderror"
                                           placeholder="contoh: admin@example.com atau johndoe"
                                           value="{{ old('login') }}"
                                           required
                                           autofocus>
                                </div>
                                @error('login')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="input-group-custom">
                                    <i class="fas fa-lock"></i>
                                    <input type="password"
                                           name="password"
                                           id="password"
                                           class="form-control-custom @error('password') error @enderror"
                                           placeholder="Masukkan password Anda"
                                           required>
                                    <span class="toggle-password" onclick="togglePassword()">
                                        <i class="fas fa-eye-slash" id="toggleIcon"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn-login" id="loginButton">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem
                            </button>

                            <div class="developer-note">
                                <p>
                                    <i class="fas fa-headset me-1"></i>
                            Butuh bantuan? Hubungi
                            <a href="https://wa.me/6288223644049?text=Halo%20saya%20ingin%20bertanya" target="_blank">
                                FirazF
                            </a>
                         </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>

    <script>
        // Floating Particles Effect
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                const size = Math.random() * 5 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = Math.random() * 15 + 5 + 's';
                particle.style.animationDelay = Math.random() * 10 + 's';
                particlesContainer.appendChild(particle);
            }
        }

        createParticles();

        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }

        // Form Submit Loading State
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const login = document.getElementById('login')?.value;
            const password = document.getElementById('password')?.value;

            if (!login || !password) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Email/Username dan password harus diisi!',
                    confirmButtonColor: '#18b5a0'
                });
                return false;
            }

            const button = document.getElementById('loginButton');
            button.classList.add('loading');
            button.innerHTML = 'Memproses <span class="spinner"></span>';
            button.disabled = true;
        });

        // Remove error class on input
        const inputs = document.querySelectorAll('.form-control-custom');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    this.classList.remove('error');
                    const errorMessage = this.parentElement.nextElementSibling;
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.style.display = 'none';
                    }
                }
            });
        });

        // Show error messages with SweetAlert
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#18b5a0',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if($errors->any())
            @if($errors->has('login') || $errors->has('email') || $errors->has('password'))
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    text: 'Email/Username atau password yang Anda masukkan salah.',
                    confirmButtonColor: '#18b5a0',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif
        @endif
    </script>
</body>
</html>
