<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$order_id = $_GET['id'] ?? 0;
$message = '';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Cập nhật trạng thái thành công!</div>';
    } else {
        $message = '<div class="alert alert-danger">Lỗi: ' . $conn->error . '</div>';
    }
}

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Lấy chi tiết sản phẩm
$sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng #<?php echo $order['order_number']; ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/no-animation.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Chi Tiết Đơn Hàng #<?php echo $order['order_number']; ?></h1>
                    <a href="orders.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <?php echo $message; ?>
                
                <div class="row">
                    <!-- Thông tin đơn hàng -->
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Thông Tin Đơn Hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Mã đơn hàng:</strong> <?php echo $order['order_number']; ?></p>
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
                            </div>
                        </div>
                        
                        <!-- Thông tin khách hàng -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Thông Tin Khách Hàng</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                <?php if ($order['notes']): ?>
                                <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Sản phẩm -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Sản Phẩm</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Hình</th>
                                                <th>Sản phẩm</th>
                                                <th>Đơn giá</th>
                                                <th>Số lượng</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px; border-radius: 4px;">
                                                        <i class="fas fa-box text-muted"></i>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                    <?php if (!empty($item['size'])): ?>
                                                    <br><small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatPrice($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo formatPrice($item['subtotal']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Tạm tính:</strong></td>
                                                <td><strong><?php echo formatPrice($order['subtotal']); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                                <td><strong><?php echo formatPrice($order['shipping_fee']); ?></strong></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                                                <td><strong><?php echo formatPrice($order['total']); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cập nhật trạng thái -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Cập Nhật Trạng Thái</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái đơn hàng</label>
                                        <select class="form-select" name="status" required>
                                            <?php foreach (ORDER_STATUS as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                                        <i class="fas fa-save"></i> Cập Nhật
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success" onclick="printOrder()">
                                        <i class="fas fa-print"></i> In đơn hàng
                                    </button>
                                    <?php if ($order['status'] !== 'cancelled'): ?>
                                    <button class="btn btn-danger" onclick="cancelOrder()">
                                        <i class="fas fa-times"></i> Hủy đơn hàng
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lịch sử -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>Lịch Sử</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Đơn hàng được tạo</span>
                                        <small class="text-muted d-block"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></small>
                                    </div>
                                    <div class="timeline-item">
                                        <i class="fas fa-info-circle text-info"></i>
                                        <span>Trạng thái hiện tại: <?php echo ORDER_STATUS[$order['status']]; ?></span>
                                        <small class="text-muted d-block">Cập nhật lần cuối</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printOrder() {
            window.print();
        }
        
        function cancelOrder() {
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                document.querySelector('select[name="status"]').value = 'cancelled';
                document.querySelector('form').submit();
            }
        }
    </script>
    
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item i {
            position: absolute;
            left: -30px;
            top: 0;
        }
        @media print {
            .sidebar, .btn, .card-header { display: none !important; }
        }
    </style>
</body>
</html>
