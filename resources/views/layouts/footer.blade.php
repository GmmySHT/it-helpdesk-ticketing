{{-- Footer Premium --}}
<footer class="footer-premium mt-5">
    <div class="container-fluid px-4">
        <!-- Main Footer Content -->
        <div class="footer-main">
            <div class="row g-4">
                <!-- Column 1: Brand & Description -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <i class="fas fa-headset me-2"></i>
                            <span>GMMY<span class="text-gradient">System</span></span>
                        </div>
                        <p class="footer-description mt-3">
                            Sistem manajemen ticket berbasis web untuk memudahkan
                            pengelolaan permintaan bantuan IT dan layanan helpdesk
                            yang profesional dan efisien.
                        </p>
                        <div class="footer-social mt-4">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-links">
                        <h5 class="footer-title">
                            <i class="fas fa-link me-2"></i>Link Cepat
                        </h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('dashboard') }}"><i class="fas fa-chevron-right me-2"></i>Dashboard</a></li>
                            <li><a href="{{ route('tickets.index') }}"><i class="fas fa-chevron-right me-2"></i>Semua Ticket</a></li>
                            <li><a href="{{ route('tickets.create') }}"><i class="fas fa-chevron-right me-2"></i>Buat Ticket</a></li>
                            <li><a href="{{ route('reports.index') }}"><i class="fas fa-chevron-right me-2"></i>Laporan</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 3: Support -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-links">
                        <h5 class="footer-title">
                            <i class="fas fa-headset me-2"></i>Bantuan & Dukungan
                        </h5>
                        <ul class="list-unstyled">
                            <li><a href="#"><i class="fas fa-question-circle me-2"></i>FAQ</a></li>
                            <li><a href="#"><i class="fas fa-life-ring me-2"></i>Panduan Penggunaan</a></li>
                            <li><a href="#"><i class="fas fa-envelope me-2"></i>Hubungi Kami</a></li>
                            <li><a href="#"><i class="fas fa-shield-alt me-2"></i>Kebijakan Privasi</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 4: Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-contact">
                        <h5 class="footer-title">
                            <i class="fas fa-address-card me-2"></i>Kontak Kami
                        </h5>
                        <ul class="list-unstyled">
                            <li>
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <span>Bandung, Indonesia</span>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt me-2 text-primary"></i>
                                <span>+62 123 4567 890</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <span>shtgmmy@gmail.com</span>
                            </li>
                            <li>
                                <i class="fas fa-clock me-2 text-primary"></i>
                                <span>Senin - Jumat: 08:00 - 17:00</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter Section -->
        <div class="footer-newsletter">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="newsletter-text">
                        <i class="fas fa-envelope-open-text me-2"></i>
                        <span>Dapatkan update terbaru dari kami!</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Email Anda...">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-paper-plane me-1"></i>Subscribe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="footer-copyright">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <div class="copyright-text">
                        <i class="far fa-copyright me-1"></i>
                        {{ date('Y') }} <span class="fw-bold text-gradient">GMMYSHT</span>.
                        All Rights Reserved.
                    </div>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="copyright-links">
                        <a href="#">Terms of Service</a>
                        <span class="mx-2">|</span>
                        <a href="#">Privacy Policy</a>
                        <span class="mx-2">|</span>
                        <a href="#">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Footer Premium Styles */
    .footer-premium {
        background: linear-gradient(135deg, #0a0e1a 0%, #0f1629 50%, #1a1f35 100%);
        position: relative;
        overflow: hidden;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 2rem;
    }

    /* Animated Background */
    .footer-premium::before {
        content: '';
        position: absolute;
        top: 0;
        left: -50%;
        width: 200%;
        height: 100%;
        background: radial-gradient(circle at 20% 50%, rgba(24, 181, 160, 0.08) 0%, transparent 50%);
        animation: footerGlow 8s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes footerGlow {
        0%, 100% { transform: translateX(-10%) translateY(0); opacity: 0.5; }
        50% { transform: translateX(10%) translateY(-5%); opacity: 1; }
    }

    /* Main Footer Content */
    .footer-main {
        padding: 3rem 0 2rem;
        position: relative;
        z-index: 1;
    }

    /* Brand / Logo */
    .footer-logo {
        font-size: 1.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, #fff 0%, #18b5a0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .footer-logo i {
        background: linear-gradient(135deg, #18b5a0, #0d6efd);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.75rem;
    }

    .text-gradient {
        background: linear-gradient(135deg, #18b5a0, #0d6efd);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .footer-description {
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
        font-size: 0.9rem;
    }

    /* Social Icons */
    .footer-social {
        display: flex;
        gap: 0.75rem;
    }

    .social-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 1rem;
    }

    .social-icon:hover {
        background: linear-gradient(135deg, #18b5a0, #0d6efd);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(24, 181, 160, 0.3);
        color: white;
    }

    /* Footer Links */
    .footer-title {
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        position: relative;
        display: inline-block;
    }

    .footer-title::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 40px;
        height: 2px;
        background: linear-gradient(90deg, #18b5a0, #0d6efd);
        border-radius: 2px;
    }

    .footer-links ul li {
        margin-bottom: 0.75rem;
    }

    .footer-links ul li a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        display: inline-block;
    }

    .footer-links ul li a:hover {
        color: #18b5a0;
        transform: translateX(5px);
    }

    .footer-links ul li a i {
        font-size: 0.7rem;
        transition: all 0.3s ease;
    }

    .footer-links ul li a:hover i {
        transform: translateX(3px);
        color: #18b5a0;
    }

    /* Contact Info */
    .footer-contact ul li {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }

    .footer-contact ul li i {
        width: 24px;
        font-size: 1rem;
    }

    /* Newsletter Section */
    .footer-newsletter {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1rem 0;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .newsletter-text {
        color: white;
        font-size: 1rem;
        font-weight: 500;
    }

    .newsletter-text i {
        font-size: 1.25rem;
        color: #18b5a0;
    }

    .newsletter-form .input-group {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        overflow: hidden;
    }

    .newsletter-form .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.75rem 1rem;
    }

    .newsletter-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .newsletter-form .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: #18b5a0;
        box-shadow: none;
    }

    .newsletter-form .btn-primary {
        background: linear-gradient(135deg, #18b5a0, #0d6efd);
        border: none;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .newsletter-form .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 181, 160, 0.4);
    }

    /* Copyright Bar */
    .footer-copyright {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem 0;
        margin-top: 1rem;
    }

    .copyright-text {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
    }

    .copyright-text .fw-bold {
        font-size: 1rem;
        letter-spacing: 1px;
    }

    .copyright-links a {
        color: rgba(255, 255, 255, 0.6);
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .copyright-links a:hover {
        color: #18b5a0;
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #18b5a0, #0d6efd);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        box-shadow: 0 4px 15px rgba(24, 181, 160, 0.3);
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(24, 181, 160, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .footer-main {
            padding: 2rem 0 1rem;
        }

        .footer-newsletter {
            padding: 1rem;
        }

        .newsletter-text {
            text-align: center;
            margin-bottom: 1rem;
        }

        .footer-copyright {
            text-align: center;
        }

        .copyright-links {
            margin-top: 0.5rem;
        }

        .back-to-top {
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }
</style>

<script>
    // Back to Top Button
    document.addEventListener('DOMContentLoaded', function() {
        const backToTop = document.createElement('div');
        backToTop.className = 'back-to-top';
        backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
        document.body.appendChild(backToTop);

        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>
