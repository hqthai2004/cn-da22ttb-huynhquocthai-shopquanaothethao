<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$categories = getCategories($conn);

// Lấy thông tin đơn hàng (chỉ của user hiện tại hoặc admin)
if (isAdmin()) {
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
} else {
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Lấy chi tiết sản phẩm trong đơn hàng
$sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng #<?php echo $order['order_number']; ?> - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/no-animation.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-invoice"></i> Chi Tiết Đơn Hàng</h2>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
        
        <div class="row">
            <!-- Thông tin đơn hàng -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Đơn Hàng #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $order['status'] === 'completed' ? 'success' : 
                                            ($order['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ORDER_STATUS[$order['status']]; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Thanh toán:</strong> <?php echo PAYMENT_METHODS[$order['payment_method']]; ?></p>
                                <p><strong>Trạng thái TT:</strong> 
                                    <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo $order['status'] === 'completed' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">Sản Phẩm</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2 bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px; border-radius: 4px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                                <div>
                                                    <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                    <?php if (!empty($item['size'])): ?>
                                                    <br><small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><strong><?php echo formatPrice($item['subtotal']); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Thông tin giao hàng & thanh toán -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-user"></i> Thông Tin Khách Hàng</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Họ tên:</strong><br><?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><strong>Email:</strong><br><?php echo htmlspecialchars($order['email']); ?></p>
                        <p><strong>Số điện thoại:</strong><br><?php echo htmlspecialchars($order['phone']); ?></p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Địa Chỉ Giao Hàng</h6>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-money-bill"></i> Tổng Đơn Hàng</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <strong><?php echo formatPrice($order['subtotal']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <strong><?php echo formatPrice($order['shipping_fee']); ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-danger fs-5"><?php echo formatPrice($order['total']); ?></strong>
                        </div>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-comment"></i> Ghi Chú</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
</body>
</html>
