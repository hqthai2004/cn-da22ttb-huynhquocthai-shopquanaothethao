<?php
// Cấu hình chung
define('SITE_NAME', 'Cửa Hàng Quần Áo Thể Thao');
define('SITE_URL', 'http://localhost/shopquanao');
define('ADMIN_EMAIL', 'admin@sportswear.com');

// Cấu hình upload
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Cấu hình phân trang
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);

// Trạng thái đơn hàng
define('ORDER_STATUS', [
    'pending' => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'shipping' => 'Đang giao hàng',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
]);

// Phương thức thanh toán
define('PAYMENT_METHODS', [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'qr' => 'Quét mã QR'
]);

// Cấu hình Google OAuth
define('GOOGLE_CLIENT_ID', '1050707843658-h1u1dq7ot970oe42thvch0qb4coo06vp.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
?>
