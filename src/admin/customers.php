<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Xử lý xóa khách hàng
if (isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];
    
    // Kiểm tra xem khách hàng có đơn hàng không
    $check_orders = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
    $check_orders->bind_param("i", $customer_id);
    $check_orders->execute();
    $order_count = $check_orders->get_result()->fetch_assoc()['count'];
    
    // Cho phép xóa khách hàng có đơn hàng với cảnh báo
    if ($order_count > 0) {
        // Xóa tất cả đơn hàng liên quan trước
        $delete_orders = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
        $delete_orders->bind_param("i", $customer_id);
        $delete_orders->execute();
        
        $warning_msg = " (Đã xóa {$order_count} đơn hàng liên quan)";
    } else {
        $warning_msg = "";
    }
    
    // Xóa wishlist trước
    $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
    $delete_wishlist->bind_param("i", $customer_id);
    $delete_wishlist->execute();
    
    // Xóa khách hàng
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $delete_stmt->bind_param("i", $customer_id);
    
    if ($delete_stmt->execute()) {
        $success = "Xóa khách hàng thành công!" . $warning_msg;
    } else {
        $error = "Có lỗi xảy ra khi xóa khách hàng!";
    }
}

// Lấy danh sách khách hàng với ID đều nhau
$sql = "SELECT u.*, COUNT(o.id) as total_orders, SUM(o.total) as total_spent 
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id 
        WHERE u.role = 'customer' 
        GROUP BY u.id 
        ORDER BY u.id ASC";
$customers = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/no-animation.css">
    <style>
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .text-dark {
            font-weight: 600;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,.075);
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản Lý Khách Hàng</h1>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>SĐT</th>
                                        <th>Số đơn</th>
                                        <th>Tổng chi tiêu</th>
                                        <th>Ngày đăng ký</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $row_number = 1;
                                    foreach ($customers as $customer): 
                                    ?>
                                    <tr>
                                        <td><?php echo $row_number++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($customer['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-dark"><?php echo $customer['total_orders']; ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?php echo formatPrice($customer['total_spent'] ?? 0); ?></strong>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $customer['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo $customer['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['username']); ?>', <?php echo $customer['total_orders']; ?>)"
                                                    title="Xóa khách hàng">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận xóa khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa khách hàng <strong id="customerName"></strong>?</p>
                    <div id="orderWarning" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Cảnh báo:</strong> Khách hàng này có <span id="orderCount"></span> đơn hàng. 
                        Tất cả đơn hàng sẽ bị xóa cùng!
                    </div>
                    <p class="text-danger"><small><i class="fas fa-exclamation-circle"></i> Hành động này không thể hoàn tác!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="customer_id" id="customerId">
                        <button type="submit" name="delete_customer" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCustomer(id, username, orderCount) {
            document.getElementById('customerId').value = id;
            document.getElementById('customerName').textContent = username;
            
            const orderWarning = document.getElementById('orderWarning');
            const orderCountSpan = document.getElementById('orderCount');
            
            if (orderCount > 0) {
                orderCountSpan.textContent = orderCount;
                orderWarning.style.display = 'block';
            } else {
                orderWarning.style.display = 'none';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Tự động ẩn alert sau 5 giây
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
