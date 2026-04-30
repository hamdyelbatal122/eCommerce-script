<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ECommerce Marketplace - Buy and sell products securely online">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
    
    <!-- Title -->
    <title><?php echo isset($title) ? htmlspecialchars($title) . ' - ' : ''; ?>ECommerce Marketplace</title>
    
    <!-- Bootstrap CSS (v5.3) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons (v6.4) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS - Professional Responsive Styles -->
    <?php $assetBase = rtrim(parse_url(getenv('APP_URL') ?: '', PHP_URL_PATH) ?: '', '/'); ?>
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/public/assets/css/style.css">
    
    <!-- Google Fonts (Optional - for better typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <!-- ============================================================================
         NAVIGATION BAR - Professional Sticky Header
         ============================================================================ -->
    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-shopping-bag me-2" style="color: #2563eb;"></i>
                <span style="color: #2563eb;">E</span>Market
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <!-- Home Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    
                    <!-- Browse Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="/search">
                            <i class="fas fa-search me-1"></i> Browse
                        </a>
                    </li>
                    
                    <!-- Authenticated User Menu -->
                    <?php if (\ECommerce\Core\Authenticator::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/cart">
                                <i class="fas fa-cart-shopping me-1"></i> Cart
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/wishlist">
                                <i class="fas fa-heart me-1"></i> Wishlist
                            </a>
                        </li>
                        <!-- Post Item Button -->
                        <li class="nav-item">
                            <a class="nav-link text-success fw-bold" href="/items/create">
                                <i class="fas fa-plus-circle me-1"></i> Post Item
                            </a>
                        </li>
                        
                        <!-- Dashboard -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="/dashboard" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/dashboard">
                                    <i class="fas fa-home me-2"></i>My Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="/dashboard">
                                    <i class="fas fa-box me-2"></i>My Items
                                </a></li>
                                <li><a class="dropdown-item" href="/dashboard">
                                    <i class="fas fa-comments me-2"></i>My Comments
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/dashboard">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a></li>
                            </ul>
                        </li>
                        
                        <!-- Admin Links (if admin) -->
                        <?php if (\ECommerce\Core\Authenticator::isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold" href="/admin">
                                    <i class="fas fa-shield-alt me-1"></i> Admin Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold" href="/admin/coupons">
                                    <i class="fas fa-ticket me-1"></i> Coupons
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold" href="/admin/analytics/wishlist">
                                    <i class="fas fa-chart-line me-1"></i> Analytics
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Logout Button -->
                        <li class="nav-item">
                            <form method="POST" action="/logout" class="d-inline">
                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                                <button class="nav-link btn btn-link text-muted" type="submit" 
                                        style="border: none; cursor: pointer;">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    
                    <!-- Guest User Menu -->
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-sm text-white ms-2" href="/register">
                                <i class="fas fa-user-plus me-1"></i> Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ============================================================================
         FLASH MESSAGES - Auto-dismissing Alerts
         ============================================================================ -->
    <?php if (isset($flash) && !empty($flash)): ?>
        <div class="container mt-3">
            <?php foreach ($flash as $key => $message): ?>
                <?php 
                    $alertType = ($message['type'] === 'error') ? 'danger' : $message['type'];
                    $icon = [
                        'success' => 'check-circle',
                        'danger' => 'exclamation-circle',
                        'warning' => 'exclamation-triangle',
                        'info' => 'info-circle'
                    ][$alertType] ?? 'info-circle';
                ?>
                <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $icon; ?> me-2"></i>
                    <strong><?php echo ucfirst($alertType); ?>:</strong>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ============================================================================
         MAIN CONTENT AREA
         ============================================================================ -->
    <main class="main-content">
        <div class="container">
            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- ============================================================================
         FOOTER - Professional Footer Section
         ============================================================================ -->
    <footer class="footer bg-dark text-white-50 border-top mt-5 pt-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <!-- About Section -->
                <div class="col-12 col-md-4">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-shopping-bag me-2"></i>ECommerce Market
                    </h5>
                    <p class="small">
                        A modern, professional eCommerce marketplace platform. Buy and sell products securely online with confidence.
                    </p>
                    <div class="mt-3">
                        <a href="#" class="text-white-50 me-3" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-white-50 me-3" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-white-50 me-3" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white-50" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-12 col-md-4">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-link me-2"></i>Quick Links
                    </h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <a href="/" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Home
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/search" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Browse Products
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/about-us" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/login" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Login
                            </a>
                        </li>
                        <li>
                            <a href="/register" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Sign Up
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Support Section -->
                <div class="col-12 col-md-4">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-headset me-2"></i>Support & Info
                    </h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <a href="mailto:support@example.com" class="text-white-50 text-decoration-none">
                                support@example.com
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <span class="text-white-50">+1 (555) 000-0000</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <span class="text-white-50">123 Commerce St, City, State</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Divider -->
            <hr class="border-secondary my-4">
            
            <!-- Footer Bottom -->
            <div class="row align-items-center py-3">
                <div class="col-12 col-md-6 text-center text-md-start small">
                    <p class="mb-0">
                        &copy; 2024 ECommerce Marketplace. All rights reserved.
                    </p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end small">
                    <a href="/privacy-policy" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                    <a href="/terms-of-use" class="text-white-50 text-decoration-none">Terms of Use</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================================================
         SCRIPTS - Bootstrap & Custom JavaScript
         ============================================================================ -->
    
    <!-- Bootstrap Bundle JS (v5.3) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom App Script -->
    <script src="<?php echo $assetBase; ?>/public/assets/js/app.js"></script>
    
    <!-- Auto-dismiss alerts after 5 seconds -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
