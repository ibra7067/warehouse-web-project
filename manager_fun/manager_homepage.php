<?php

header("Cache-Control: no-store, must-revalidate");
header("Expires: 0");
require __DIR__ . '\..\include\config.php'; 
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /warehouse_project/extra_fun/login.php");
    exit();
}
?>
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <style>
     <?php require __DIR__ . '\styles\MH.css'; ?> 
    </style>
</head>
<body>
    <!-- Simplified Header -->
    <header>
        <a href="/warehouse_project/manager_fun/manager_homepage.php" class="logo-link">
            <img src="/warehouse_project/img/storage.png" alt="WMS Logo" class="header-logo">
            <span class="logo-text">WMS</span>
        </a>
        <div class="header-buttons">
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="/warehouse_project/extra_fun/logout.php" class="btn primary">Logout</a>
        </div>
    </header>

  
     
    <div class="main-content">
        <div class="feat-list">
            <a href="order_list.php" class="feat-item3">
                <div class="feat-img">
                    <img src="/warehouse_project/img/managment.jpg" alt="Order Page" class="img-resp">
                </div>
                <h3><span class="bubble-text">Order managment</span></h3>
                <p></p>
            </a>
            
            <a href="manager_inventory.php" class="feat-item3">
                <div class="feat-img">
                    <img src="/warehouse_project/img/istock.jpg" alt="Track Order" class="img-resp">
                </div>
                <h3><span class="bubble-text">storage level</span></h3>
                <p></p>
            </a>
            
            <a href="report.php" class="feat-item3">
                <div class="feat-img">
                    <img src="/warehouse_project/img/managments.jpg" alt="Track Order" class="img-resp">
                </div>
                <h3><span class="bubble-text">report</span></h3>
                <p></p>
            </a>
            
            
        </div>
    </div>

    
     

    
    
    <div id="loadingScreen">
        <div class="text-center">
            <img src="/warehouse_project/img/gif3.webp" class="loading-gif mb-3" alt="Loading">
            <div class="loading-bar-container progress">
                <div id="loadingBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: 0%"></div>
            </div>
            <div class="loading-text">Loading ...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
      
        document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const loadingScreen = document.getElementById('loadingScreen');
    const loadingBar = document.getElementById('loadingBar');
    if (!loadingScreen || !loadingBar) return;

    // Settings - adjust these to change speed/behavior
    const settings = {
        speed: 4,           // Progress increase per step (higher = faster)
        steps: 100,         // Milliseconds between updates (lower = faster)
        minLoad: 90,        // Pause progress at this percentage
        minTime: 1000,      // Minimum loading time (milliseconds)
        timeout: 5000       // Maximum loading time (milliseconds)
    };

    let progress = 0;
    let isLoading = false;
    let loadTimer;

    // Update loading bar progress
    function updateLoad() {
        progress += settings.speed + Math.random() * 2;
        loadingBar.style.width = `${Math.min(progress, settings.min)}%`;
        
        if (progress < settings.min) {
            loadTimer = setTimeout(updateLoad, settings.steps);
        }
    }

    // Complete loading sequence
    function finishLoad() {
        clearTimeout(loadTimer);
        isLoading = false;
        loadingBar.style.width = '100%';
        setTimeout(() => {
            loadingScreen.style.opacity = '0';
            setTimeout(() => loadingScreen.style.display = 'none', 300);
        }, 300);
    }

    // Start loading screen
    function startLoad() {
        isLoading = true;
        progress = 0;
        loadingScreen.style.display = 'flex';
        loadingScreen.style.opacity = '1';
        loadingBar.style.width = '0%';
        updateLoad();
        setTimeout(finishLoad, settings.timeout);
    }

    // Initial page load
    startLoad();
    window.addEventListener('load', finishLoad);

    // Handle link clicks
    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', function(e) {
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
 
    </script>
</body>
</html>