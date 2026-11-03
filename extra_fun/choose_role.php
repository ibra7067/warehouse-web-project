<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Role - WMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            z-index: 100;
        }

        .logo-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }

        .header-logo {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(90deg, #38bdf8, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav {
            display: flex;
            gap: 30px;
        }

        nav a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #38bdf8;
        }

        .header-buttons .btn {
            padding: 10px 24px;
            border-radius: 30px;
            background: linear-gradient(90deg, #38bdf8, #a855f7);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .header-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.4);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px 80px;
            position: relative;
        }

        .page-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .page-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, #38bdf8, #a855f7, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title p {
            color: #94a3b8;
            margin-top: 10px;
            font-size: 1rem;
        }

        /* Carousel */
        .carousel-container {
            width: 100%;
            max-width: 1400px;
            height: 520px;
            perspective: 2000px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
        }

        .carousel-track {
            position: relative;
            width: 340px;
            height: 460px;
            transform-style: preserve-3d;
        }

        /* Cards */
        .role-card {
            position: absolute;
            top: 0;
            left: 0;
            width: 340px;
            height: 460px;
            transform-style: preserve-3d;
            transition: transform 0.7s cubic-bezier(0.23, 1, 0.32, 1),
                        opacity 0.7s ease;
            cursor: pointer;
            will-change: transform, opacity;
        }

        .role-card.flipped .card-inner {
            transform: rotateY(180deg);
        }

        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .card-face {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            border-radius: 24px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .card-face::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.15;
            background: radial-gradient(circle at 30% 20%, #fff, transparent 60%);
            pointer-events: none;
        }

        .card-front {
            background: linear-gradient(145deg, #1e293b, #0f172a);
        }

        .card-back {
            background: linear-gradient(145deg, #0f172a, #1e293b);
            transform: rotateY(180deg);
            justify-content: space-between;
        }

        /* Card accent per role */
        .role-card[data-role="customer"] .card-front {
            background: linear-gradient(145deg, #1e3a8a, #0f172a);
        }
        .role-card[data-role="customer"] .card-back {
            background: linear-gradient(145deg, #0f172a, #1e3a8a);
        }

        .role-card[data-role="employee"] .card-front {
            background: linear-gradient(145deg, #065f46, #0f172a);
        }
        .role-card[data-role="employee"] .card-back {
            background: linear-gradient(145deg, #0f172a, #065f46);
        }

        .role-card[data-role="manager"] .card-front {
            background: linear-gradient(145deg, #7c2d12, #0f172a);
        }
        .role-card[data-role="manager"] .card-back {
            background: linear-gradient(145deg, #0f172a, #7c2d12);
        }

        .role-card[data-role="supplier"] .card-front {
            background: linear-gradient(145deg, #581c87, #0f172a);
        }
        .role-card[data-role="supplier"] .card-back {
            background: linear-gradient(145deg, #0f172a, #581c87);
        }

        .card-icon {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.15);
            font-size: 3rem;
            transition: transform 0.4s;
        }

        .role-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .card-face h3 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .card-face .tagline {
            color: #cbd5e1;
            font-size: 0.9rem;
            opacity: 0.85;
        }

        .card-face .flip-hint {
            position: absolute;
            bottom: 20px;
            font-size: 0.75rem;
            color: #94a3b8;
            opacity: 0.7;
            letter-spacing: 1px;
        }

        /* Card back content */
        .card-back h3 {
            font-size: 1.4rem;
            margin-bottom: 12px;
        }

        .card-back .description {
            color: #cbd5e1;
            font-size: 0.88rem;
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
            display: flex;
            align-items: center;
        }

        .card-back .features {
            list-style: none;
            width: 100%;
            margin-bottom: 20px;
            text-align: left;
        }

        .card-back .features li {
            font-size: 0.8rem;
            color: #cbd5e1;
            padding: 4px 0;
            padding-left: 20px;
            position: relative;
        }

        .card-back .features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #38bdf8;
            font-weight: 700;
        }

        .select-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg, #38bdf8, #a855f7);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1px;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .select-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.5);
        }

        /* Navigation arrows */
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 50;
            backdrop-filter: blur(10px);
        }

        .nav-arrow:hover {
            background: linear-gradient(90deg, #38bdf8, #a855f7);
            transform: translateY(-50%) scale(1.1);
        }

        .nav-arrow.prev { left: 40px; }
        .nav-arrow.next { right: 40px; }

        .nav-arrow:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Dots */
        .dots {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            z-index: 60;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active {
            width: 30px;
            border-radius: 5px;
            background: linear-gradient(90deg, #38bdf8, #a855f7);
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-wrap: wrap;
                gap: 15px;
            }

            nav { display: none; }

            .page-title h1 { font-size: 1.7rem; }

            .carousel-container { height: 480px; }

            .carousel-track, .role-card {
                width: 280px;
                height: 420px;
            }

            .nav-arrow {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .nav-arrow.prev { left: 10px; }
            .nav-arrow.next { right: 10px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <a href="/warehouse_project/index.php" class="logo-link">
            <img src="/warehouse_project/img/storage.png" alt="WMS Logo" class="header-logo">
            <span class="logo-text">WMS</span>
        </a>
        <nav>
            <a href="/warehouse_project/index.php">Home</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            <a href="#">Services</a>
        </nav>
        <div class="header-buttons">
            <a href="login.php" class="btn">Log In</a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title">
            <h1>Choose Your Account Type</h1>
            <p>Click a card to flip it and learn more about each role</p>
        </div>

        <div class="carousel-container">
            <button class="nav-arrow prev" id="prevBtn" aria-label="Previous">&#10094;</button>

            <div class="carousel-track" id="carouselTrack">
                <!-- Customer -->
                <div class="role-card" data-role="customer" data-index="0">
                    <div class="card-inner">
                        <div class="card-face card-front">
                            <div class="card-icon">🛒</div>
                            <h3>Customer</h3>
                            <p class="tagline">Browse & order products</p>
                            <span class="flip-hint">CLICK TO FLIP →</span>
                        </div>
                        <div class="card-face card-back">
                            <h3>Customer</h3>
                            <p class="description">Browse the catalog, place orders, and track your deliveries in real time.</p>
                            <ul class="features">
                                <li>View product catalog</li>
                                <li>Place & track orders</li>
                                <li>Order history</li>
                                <li>Manage profile</li>
                            </ul>
                            <a href="register.php?role=customer" class="select-btn">Sign Up as Customer</a>
                        </div>
                    </div>
                </div>

                <!-- Employee -->
                <div class="role-card" data-role="employee" data-index="1">
                    <div class="card-inner">
                        <div class="card-face card-front">
                            <div class="card-icon">📦</div>
                            <h3>Employee</h3>
                            <p class="tagline">Handle daily operations</p>
                            <span class="flip-hint">CLICK TO FLIP →</span>
                        </div>
                        <div class="card-face card-back">
                            <h3>Employee</h3>
                            <p class="description">Process orders, manage inventory, and keep the warehouse running smoothly.</p>
                            <ul class="features">
                                <li>Process orders</li>
                                <li>Update stock levels</li>
                                <li>Print shipping labels</li>
                                <li>Daily task list</li>
                            </ul>
                            <a href="register.php?role=employee" class="select-btn">Sign Up as Employee</a>
                        </div>
                    </div>
                </div>

                <!-- Manager -->
                <div class="role-card" data-role="manager" data-index="2">
                    <div class="card-inner">
                        <div class="card-face card-front">
                            <div class="card-icon">📊</div>
                            <h3>Manager</h3>
                            <p class="tagline">Oversee & optimize</p>
                            <span class="flip-hint">CLICK TO FLIP →</span>
                        </div>
                        <div class="card-face card-back">
                            <h3>Manager</h3>
                            <p class="description">Get full visibility into operations, generate reports, and manage your team.</p>
                            <ul class="features">
                                <li>Analytics dashboard</li>
                                <li>Staff management</li>
                                <li>Inventory reports</li>
                                <li>Approve requests</li>
                            </ul>
                            <a href="register.php?role=manager" class="select-btn">Sign Up as Manager</a>
                        </div>
                    </div>
                </div>

                <!-- Supplier -->
                <div class="role-card" data-role="supplier" data-index="3">
                    <div class="card-inner">
                        <div class="card-face card-front">
                            <div class="card-icon">🚚</div>
                            <h3>Supplier</h3>
                            <p class="tagline">Supply & fulfill</p>
                            <span class="flip-hint">CLICK TO FLIP →</span>
                        </div>
                        <div class="card-face card-back">
                            <h3>Supplier</h3>
                            <p class="description">Manage purchase orders, view demand forecasts, and coordinate shipments.</p>
                            <ul class="features">
                                <li>View purchase orders</li>
                                <li>Update shipment status</li>
                                <li>Product supply list</li>
                                <li>Invoice history</li>
                            </ul>
                            <a href="register.php?role=supplier" class="select-btn">Sign Up as Supplier</a>
                        </div>
                    </div>
                </div>
            </div>

            <button class="nav-arrow next" id="nextBtn" aria-label="Next">&#10095;</button>
        </div>

        <div class="dots" id="dots">
            <div class="dot active" data-index="0"></div>
            <div class="dot" data-index="1"></div>
            <div class="dot" data-index="2"></div>
            <div class="dot" data-index="3"></div>
        </div>
    </div>

     <script>
    const cards = document.querySelectorAll('.role-card');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentIndex = 0;
    const total = cards.length;

    // Compute shortest circular offset
    // e.g. total=4, current=0, i=3 → offset = -1 (wraps to left)
    function getCircularOffset(i) {
        let offset = i - currentIndex;
        const half = total / 2;
        if (offset > half) offset -= total;
        if (offset < -half) offset += total;
        return offset;
    }

    // Position cards in 3D space based on offset
    function getCardTransform(offset) {
    const abs = Math.abs(offset);

    // Hide anything further than 1 step away (only 3 cards visible at a time)
    if (abs > 1) {
        return {
            transform: `translateX(${offset > 0 ? 400 : -400}px) translateZ(-500px) scale(0.6)`,
            opacity: 0,
            zIndex: 0,
            pointerEvents: 'none'
        };
    }

    const translateX = offset * 300;         // spacing between cards
    const translateZ = abs === 0 ? 0 : -180; // side cards pushed back
    const rotateY = offset * -35;            // side cards angled inward
    const scale = abs === 0 ? 1 : 0.82;      // side cards slightly smaller
    const opacity = abs === 0 ? 1 : 0.75;    // side cards slightly faded
    const zIndex = abs === 0 ? 10 : 5;

    return {
        transform: `translateX(${translateX}px) translateZ(${translateZ}px) rotateY(${rotateY}deg) scale(${scale})`,
        opacity: opacity,
        zIndex: zIndex,
        pointerEvents: 'auto'
    };
}

    function updateCarousel() {
        cards.forEach((card, i) => {
            const offset = getCircularOffset(i);
            const style = getCardTransform(offset);

            card.style.display = 'block';
            card.style.transform = style.transform;
            card.style.opacity = style.opacity;
            card.style.zIndex = style.zIndex;
            card.style.pointerEvents = style.pointerEvents;
            card.classList.toggle('active', offset === 0);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentIndex);
        });
    }

    // Flip card on click
    cards.forEach((card, i) => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('a')) return;

            if (i !== currentIndex) {
                // Move to that card (unflip current first)
                cards[currentIndex].classList.remove('flipped');
                currentIndex = i;
                updateCarousel();
            } else {
                card.classList.toggle('flipped');
            }
        });
    });

    // Navigation — no disabled state anymore, always loops
    prevBtn.addEventListener('click', () => {
        cards[currentIndex].classList.remove('flipped');
        currentIndex = (currentIndex - 1 + total) % total;
        updateCarousel();
    });

    nextBtn.addEventListener('click', () => {
        cards[currentIndex].classList.remove('flipped');
        currentIndex = (currentIndex + 1) % total;
        updateCarousel();
    });

    // Dots
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const idx = parseInt(dot.dataset.index);
            if (idx !== currentIndex) {
                cards[currentIndex].classList.remove('flipped');
                currentIndex = idx;
                updateCarousel();
            }
        });
    });

    // Keyboard navigation — no boundaries
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            cards[currentIndex].classList.remove('flipped');
            currentIndex = (currentIndex - 1 + total) % total;
            updateCarousel();
        } else if (e.key === 'ArrowRight') {
            cards[currentIndex].classList.remove('flipped');
            currentIndex = (currentIndex + 1) % total;
            updateCarousel();
        }
    });

    // Init
    updateCarousel();
</script>
</body>
</html>