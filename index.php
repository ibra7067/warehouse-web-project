<?php
require __DIR__ . '\include\config.php'; 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        <?php require __DIR__ . '\indexst.css'; ?>
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo-link">
            <img src="img/storage.png" alt="WMS Logo" class="header-logo">
            <span class="logo-text">WMS</span>
        </a>
        <nav>
            <a href="#">Home</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            <a href="#">Services</a>
        </nav>
        <div class="header-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="/warehouse_project/extra_fun/logout.php" class="btn primary">Logout</a>
            <?php else: ?>
                <a href="extra_fun/login.php" class="btn primary">Log In</a>
                <a href="extra_fun/choose_role.php" class="btn secondary">Sign Up</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section — background image slider -->
    <div class="hero-section">
        <!-- Background slider -->
        <div class="hero-slider">
            <img src="img/sea.avif" alt="Warehouse Image 1" class="hero-slide active">
            <img src="img/blue.jpg" alt="Warehouse Image 2" class="hero-slide">
            <img src="img/tech.jpg " alt="Warehouse Image 3" class="hero-slide">
            
            <div class="hero-overlay"></div>
        </div>

        <!-- Hero content -->
        <div class="hero-content">
            <h1 class="hero-title">Ready for Tomorrow</h1>
            <p class="hero-text">
                Welcome to our cutting-edge Warehouse Management System training program — a revolutionary approach to preparing staff for modern logistics operations. This interactive platform combines essential warehouse knowledge with practical digital tools to accelerate onboarding and enhance operational safety.
            </p>
            <div class="hero-buttons">
                <a href="#" class="hero-btn btn-explore">Explore</a>
                <a href="extra_fun/register.php" class="hero-btn btn-join">Join</a>
            </div>
        </div>

        <!-- Dots -->
        <div class="hero-dots">
            <span class="active"></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="main-content">

        <!-- About Section -->
        <div class="content-box">
            <div class="container">
                <div class="row0">
                    <div class="row00">
                        <div class="about-content">
                            <h2>About Our WMS</h2>
                            <p>We are a leading warehouse management system provider. Our platform ensures a specific training environment that adapts to each user's position and learning pace. Through immersive simulations and real-world scenario testing, we're redefining how warehouse teams develop critical operational competencies.</p>
                        </div>
                    </div>
                    <div class="row01">
                        <div class="about-image">
                            <img src="img/about.webp" alt="Warehouse Management System" class="img-fluid">
                        </div>
                    </div>
                </div>

                <div class="row1">
                    <div class="row10">
                        <div class="service-content">
                            <h2>Our Web Services</h2>
                            <p>Our services provide complete warehouse training for all roles. We cover essential operations and safety protocols through immersive, real-world scenarios that minimize learning time. Discover how our system prepares teams faster by letting them practice actual work without real risks.</p>
                        </div>
                    </div>
                    <div class="row11">
                        <div class="service-image1">
                            <img src="img/wms22.webp" alt="Web Services" class="img-fluid">
                        </div>
                    </div>
                </div>

                <div class="row2">
                    <div class="row20">
                        <div class="feature-content">
                            <h2>Advanced Features</h2>
                            <p>Discover our advanced features including AI-powered forecasting, mobile barcode scanning, and automated reporting. Transform your warehouse operations with cutting-edge technology.</p>
                        </div>
                    </div>
                    <div class="row21">
                        <div class="feature-image">
                            <img src="img/wms24.jpg" alt="Advanced Features" class="img-fluid">
                        </div>
                    </div>
                </div>

                <div class="row3">
                    <div class="row30">
                        <div class="service-content">
                            <h2>Safety Protocols</h2>
                            <p>Our comprehensive web services provide real-time inventory tracking, automated order processing, and seamless integration with your existing systems. Experience the power of cloud-based warehouse management.</p>
                        </div>
                    </div>
                    <div class="row31">
                        <div class="service-image">
                            <img src="img/wms23.jpg" alt="Safety Protocols" class="img-fluid">
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Loading Screen -->
    <div id="loadingScreen">
        <div class="text-center">
            <img src="/warehouse_project/img/gif3.webp" class="loading-gif mb-3" alt="Loading">
            <div class="loading-bar-container progress">
                <div id="loadingBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
            </div>
            <div class="loading-text">Loading ...</div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h4>Our Location</h4>
                    <p>123 Warehouse District<br>Industrial City, IC 10001</p>
                </div>
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> info@wms.example.com<br>
                       <i class="fas fa-phone"></i> (123) 456-7890</p>
                </div>
                <div class="footer-section">
                    <h4>Operating Hours</h4>
                    <p>Mon-Fri: 8AM - 6PM<br>Sat: 9AM - 3PM<br>Sun: Closed</p>
                </div>
                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="https://www.facebook.com" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.twitter.com" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.instagram.com" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // =========================================================
        // HERO BACKGROUND SLIDER
        // =========================================================
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.hero-dots span');

            if (slides.length > 0) {
                let currentIndex = 0;

                function showNext() {
                    slides[currentIndex].classList.remove('active');
                    dots[currentIndex].classList.remove('active');
                    currentIndex = (currentIndex + 1) % slides.length;
                    slides[currentIndex].classList.add('active');
                    dots[currentIndex].classList.add('active');
                }

                setInterval(showNext, 4000);

                dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => {
                        slides[currentIndex].classList.remove('active');
                        dots[currentIndex].classList.remove('active');
                        currentIndex = index;
                        slides[currentIndex].classList.add('active');
                        dots[currentIndex].classList.add('active');
                    });
                });
            }
        });

        // =========================================================
        // LOADING SCREEN
        // =========================================================
        document.addEventListener('DOMContentLoaded', function () {
            const loadingScreen = document.getElementById('loadingScreen');
            const loadingBar = document.getElementById('loadingBar');
            if (!loadingScreen || !loadingBar) return;

            const settings = {
                speed: 4,
                steps: 100,
                minLoad: 90,
                minTime: 1000,
                timeout: 5000
            };

            let progress = 0;
            let loadTimer;

            function updateLoad() {
                progress += settings.speed + Math.random() * 2;
                loadingBar.style.width = `${Math.min(progress, settings.minLoad)}%`;
                if (progress < settings.minLoad) {
                    loadTimer = setTimeout(updateLoad, settings.steps);
                }
            }

            function finishLoad() {
                clearTimeout(loadTimer);
                loadingBar.style.width = '100%';
                setTimeout(() => {
                    loadingScreen.style.opacity = '0';
                    setTimeout(() => loadingScreen.style.display = 'none', 300);
                }, 300);
            }

            function startLoad() {
                progress = 0;
                loadingScreen.style.display = 'flex';
                loadingScreen.style.opacity = '1';
                loadingBar.style.width = '0%';
                updateLoad();
                setTimeout(finishLoad, settings.timeout);
            }

            startLoad();
            window.addEventListener('load', finishLoad);

            document.querySelectorAll('a[href]').forEach(link => {
                link.addEventListener('click', function (e) {
                    if (this.target === '_blank' || this.href.includes('javascript:') ||
                        this.href === '#') return;

                    e.preventDefault();
                    const url = this.href;

                    startLoad();
                    setTimeout(() => {
                        const iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        iframe.src = url;
                        iframe.onload = () => {
                            finishLoad();
                            window.location.href = url;
                        };
                        document.body.appendChild(iframe);
                    }, settings.minTime);
                });
            });
        });
         // =========================================================
    // Header hide on scroll down, show on scroll up
    // =========================================================
    (function () {
        const header = document.querySelector('header');
        if (!header) return;

        let lastScrollY = window.scrollY;
        let ticking = false;

        function updateHeader() {
            const currentScrollY = window.scrollY;

            // Always show at the very top
            if (currentScrollY <= 10) {
                header.classList.remove('header-hidden');
            }
            // Scrolling DOWN → hide
            else if (currentScrollY > lastScrollY + 5) {
                header.classList.add('header-hidden');
            }
            // Scrolling UP → show
            else if (currentScrollY < lastScrollY - 5) {
                header.classList.remove('header-hidden');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });
    })();
    </script>
</body>
</html>