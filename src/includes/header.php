<!-- Clean E-commerce Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top ecommerce-nav" style="padding: 1.5rem 0 !important;">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php" style="font-size: 2.5rem !important;">
            <?php 
            $logo = getSetting($conn, 'site_logo');
            $site_name = getSetting($conn, 'site_name') ?: 'QT Shop';
            if ($logo && file_exists($logo)): 
            ?>
                <img src="<?php echo $logo; ?>" alt="<?php echo htmlspecialchars($site_name); ?>" style="height: 60px !important; width: auto !important; vertical-align: middle !important;">
            <?php else: ?>
                <?php echo htmlspecialchars($site_name); ?>
            <?php endif; ?>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Main Navigation -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        Trang Chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                        Sản Phẩm
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Danh Mục
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a class="dropdown-item" href="products.php?category=<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">
                        Liên Hệ
                    </a>
                </li>
            </ul>
            
            <!-- Right Side Actions -->
            <div class="d-flex align-items-center">
                <!-- Wishlist -->
                <a href="wishlist.php" class="btn btn-outline-light text-dark me-2 position-relative">
                    <i class="fas fa-heart text-danger"></i>
                    <?php if (isLoggedIn()): ?>
                    <?php 
                    $wishlist_count = getWishlistCount($conn, $_SESSION['user_id']);
                    if ($wishlist_count > 0):
                    ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $wishlist_count; ?>
                    </span>
                    <?php endif; ?>
                    <?php endif; ?>
                </a>
                
                <!-- Cart -->
                <a href="cart.php" class="btn btn-outline-light text-dark me-3 position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" id="cart-count">
                        <?php 
                        $user_id = $_SESSION['user_id'] ?? null;
                        $session_id = session_id();
                        $cart_items = getCart($conn, $user_id, $session_id);
                        echo count($cart_items);
                        ?>
                    </span>
                </a>
                
                <?php if (isLoggedIn()): ?>
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="d-md-none">User</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="account.php">
                            Tài Khoản
                        </a></li>
                        <li><a class="dropdown-item" href="orders.php">
                            Đơn Hàng
                        </a></li>
                        <?php if (isAdmin()): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="admin/index.php">
                            Quản Trị
                        </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            Đăng Xuất
                        </a></li>
                    </ul>
                </div>
                <?php else: ?>
                <!-- Login Button -->
                <a href="login.php" class="btn btn-primary">
                    Đăng Nhập
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
