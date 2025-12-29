<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Lấy danh sách sản phẩm nổi bật
$featured_products = getFeaturedProducts($conn, 8);
$categories = getCategories($conn);

// Lấy tất cả sản phẩm cho trang chủ
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active' 
        ORDER BY p.created_at DESC 
        LIMIT 100";
$all_products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Lấy banner và thông tin site
$banner = getSetting($conn, 'site_banner');
$site_name = getSetting($conn, 'site_name') ?: 'QT Shop';
$site_description = getSetting($conn, 'site_description') ?: 'Bộ sưu tập quần áo thể thao từ Nike, Adidas, Puma';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Quần Áo Thể Thao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Banner -->
    <?php if ($banner && file_exists($banner)): ?>
    <section class="hero-banner position-relative" style="min-height: 400px; max-height: 600px; overflow: hidden;">
        <img src="<?php echo htmlspecialchars($banner); ?>" alt="Banner" class="w-100" style="object-fit: cover; min-height: 400px; max-height: 600px;">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(to right, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-8">
                        <div class="text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInLeft"><?php echo htmlspecialchars($site_name); ?></h1>
                            <p class="lead mb-4 animate__animated animate__fadeInLeft animate__delay-1s"><?php echo htmlspecialchars($site_description); ?></p>
                            <a href="products.php" class="btn btn-primary btn-lg px-5 shadow animate__animated animate__fadeInUp animate__delay-2s">
                                <i class="fas fa-shopping-bag"></i> Mua Sắm Ngay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="hero-section" style="background: #f8f9fa; padding: 80px 0;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-dark">
                    <h1 class="display-3 fw-bold mb-3"><?php echo htmlspecialchars($site_name); ?></h1>
                    <p class="lead mb-4"><?php echo htmlspecialchars($site_description); ?></p>
                    <a href="products.php" class="btn btn-dark btn-lg px-5">
                        <i class="fas fa-shopping-bag"></i> Mua Sắm Ngay
                    </a>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-running" style="font-size: 200px; color: rgba(0,0,0,0.1);"></i>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Sản Phẩm Nổi Bật</h2>
                <p class="text-muted">Những sản phẩm được yêu thích nhất</p>
            </div>
            <div class="row g-4">
                <?php foreach ($featured_products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <?php include 'includes/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- New Products -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Sản Phẩm Mới Nhất</h2>
                    <p class="text-muted">Cập nhật liên tục</p>
                </div>
                <a href="products.php" class="btn btn-primary">
                    Xem Tất Cả <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="row g-4">
                <?php 
                $new_products = array_slice($all_products, 0, 12);
                foreach ($new_products as $product): 
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <?php include 'includes/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                        <h5>Giao Hàng Nhanh</h5>
                        <p class="text-muted small">Giao hàng toàn quốc</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h5>Thanh Toán An Toàn</h5>
                        <p class="text-muted small">Bảo mật thông tin</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fas fa-undo fa-3x text-warning mb-3"></i>
                        <h5>Đổi Trả Dễ Dàng</h5>
                        <p class="text-muted small">Trong vòng 7 ngày</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fas fa-headset fa-3x text-info mb-3"></i>
                        <h5>Hỗ Trợ 24/7</h5>
                        <p class="text-muted small">Luôn sẵn sàng hỗ trợ</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
</body>
</html>
