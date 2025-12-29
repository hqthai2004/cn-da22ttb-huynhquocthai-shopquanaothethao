<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

$order_number = $_GET['order'] ?? null;
if (!$order_number) {
    header('Location: index.php');
    exit;
}

$sql = "SELECT * FROM orders WHERE order_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_number);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}

$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Hàng Thành Công - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                        </div>
                        <h2 class="text-success mb-3">Đặt Hàng Thành Công!</h2>
                        <p class="lead">Cảm ơn bạn đã đặt hàng tại QT Shop</p>
                        
                        <div class="alert alert-info mt-4">
                            <h5>Mã đơn hàng: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong></h5>
                            <p class="mb-0">Vui lòng lưu lại mã đơn hàng để tra cứu</p>
                        </div>
                        
                        <div class="mt-4">
                            <p><strong>Tổng tiền:</strong> <?php echo formatPrice($order['total']); ?></p>
                            <p><strong>Phương thức thanh toán:</strong> <?php echo PAYMENT_METHODS[$order['payment_method']]; ?></p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary me-2">
                                <i class="fas fa-home"></i> Về Trang Chủ
                            </a>
                            <a href="orders.php" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> Xem Đơn Hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
