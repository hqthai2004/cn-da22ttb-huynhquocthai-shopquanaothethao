<?php
// Lấy thông tin footer từ settings
$footer_about = getSetting($conn, 'footer_about') ?: 'Cửa hàng quần áo thể thao chất lượng cao';
$footer_phone = getSetting($conn, 'contact_phone') ?: '0123 456 789';
$footer_email = getSetting($conn, 'contact_email') ?: 'info@sportswear.com';
$footer_address = getSetting($conn, 'contact_address') ?: '123 Đường ABC, TP.HCM';
$footer_facebook = getSetting($conn, 'footer_facebook') ?: '#';
$footer_instagram = getSetting($conn, 'footer_instagram') ?: '#';
$footer_youtube = getSetting($conn, 'footer_youtube') ?: '#';
$site_name = getSetting($conn, 'site_name') ?: 'QT Shop';
?>
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3"><?php echo htmlspecialchars($site_name); ?></h5>
                <p class="text-white-50"><?php echo htmlspecialchars($footer_about); ?></p>
                <div class="social-links mt-3">
                    <a href="<?php echo htmlspecialchars($footer_facebook); ?>" class="text-white me-3" target="_blank">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($footer_instagram); ?>" class="text-white me-3" target="_blank">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($footer_youtube); ?>" class="text-white" target="_blank">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3">Danh Mục</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="products.php?category=1" class="text-white-50 text-decoration-none">Áo Thể Thao</a></li>
                    <li class="mb-2"><a href="products.php?category=2" class="text-white-50 text-decoration-none">Quần Thể Thao</a></li>
                    <li class="mb-2"><a href="products.php?category=3" class="text-white-50 text-decoration-none">Giày Thể Thao</a></li>
                    <li class="mb-2"><a href="products.php?category=4" class="text-white-50 text-decoration-none">Phụ Kiện</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-3">Hỗ Trợ</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none">Liên Hệ</a></li>
                    <li class="mb-2"><a href="orders.php" class="text-white-50 text-decoration-none">Theo Dõi Đơn Hàng</a></li>
                    <li class="mb-2"><a href="cart.php" class="text-white-50 text-decoration-none">Giỏ Hàng</a></li>
                    <li class="mb-2"><a href="register.php" class="text-white-50 text-decoration-none">Đăng Ký</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-3">Liên Hệ</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2">Điện thoại: <?php echo htmlspecialchars($footer_phone); ?></li>
                    <li class="mb-2">Email: <?php echo htmlspecialchars($footer_email); ?></li>
                    <li class="mb-2">Địa chỉ: <?php echo htmlspecialchars($footer_address); ?></li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4 bg-secondary">
        
        <div class="row">
            <div class="col-12 text-center">
                <p class="mb-0 text-white-50">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
