<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: login.php?message=Vui lòng đăng nhập để xem đơn hàng');
    exit;
}

$categories = getCategories($conn);
$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Hàng Của Tôi - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
    <link rel="stylesheet" href="assets/css/no-animation.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-user-circle"></i> Tài Khoản
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="account.php" class="text-decoration-none">
                                    <i class="fas fa-user"></i> Thông Tin Cá Nhân
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="orders.php" class="text-decoration-none fw-bold text-primary">
                                    <i class="fas fa-shopping-bag"></i> Đơn Hàng Của Tôi
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="cart.php" class="text-decoration-none">
                                    <i class="fas fa-shopping-cart"></i> Giỏ Hàng
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="text-decoration-none text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Orders List -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-shopping-bag"></i> Đơn Hàng Của Tôi</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                                <h5>Bạn chưa có đơn hàng nào</h5>
                                <p class="text-muted">Hãy mua sắm ngay để trải nghiệm dịch vụ của chúng tôi!</p>
                                <a href="products.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart"></i> Mua Sắm Ngay
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã Đơn</th>
                                            <th>Ngày Đặt</th>
                                            <th>Tổng Tiền</th>
                                            <th>Thanh Toán</th>
                                            <th>Trạng Thái</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td><strong class="text-danger"><?php echo formatPrice($order['total']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $order['payment_method'] === 'cod' ? 'warning' : 'info'; ?>">
                                                    <?php echo PAYMENT_METHODS[$order['payment_method']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['status'] === 'completed' ? 'success' : 
                                                        ($order['status'] === 'cancelled' ? 'danger' : 
                                                        ($order['status'] === 'shipping' ? 'primary' : 'warning')); 
                                                ?>">
                                                    <?php echo ORDER_STATUS[$order['status']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Chi Tiết
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
</body>
</html>
