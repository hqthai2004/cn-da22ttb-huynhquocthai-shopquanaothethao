<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Thống kê
$stats = [];

// Tổng doanh thu
$sql = "SELECT SUM(total) as total_revenue FROM orders WHERE status = 'completed'";
$result = $conn->query($sql);
$stats['revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

// Tổng đơn hàng
$sql = "SELECT COUNT(*) as total_orders FROM orders";
$result = $conn->query($sql);
$stats['orders'] = $result->fetch_assoc()['total_orders'];

// Tổng sản phẩm
$sql = "SELECT COUNT(*) as total_products FROM products";
$result = $conn->query($sql);
$stats['products'] = $result->fetch_assoc()['total_products'];

// Tổng khách hàng
$sql = "SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'";
$result = $conn->query($sql);
$stats['customers'] = $result->fetch_assoc()['total_customers'];

// Đơn hàng gần đây
$sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10";
$recent_orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Doanh thu theo tháng (12 tháng gần nhất)
$sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
               DATE_FORMAT(created_at, '%m/%Y') as month_label,
               SUM(total) as revenue 
        FROM orders 
        WHERE status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month 
        ORDER BY month";
$revenue_data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị - QT Shop</title>
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
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Doanh Thu</p>
                                        <h3><?php echo formatPrice($stats['revenue']); ?></h3>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Đơn Hàng</p>
                                        <h3><?php echo $stats['orders']; ?></h3>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Sản Phẩm</p>
                                        <h3><?php echo $stats['products']; ?></h3>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-box fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Khách Hàng</p>
                                        <h3><?php echo $stats['customers']; ?></h3>
                                    </div>
                                    <div class="text-danger">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Revenue Chart -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Doanh Thu 12 Tháng Gần Nhất</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Đơn Hàng Gần Đây</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Mã ĐH</th>
                                                <th>Khách Hàng</th>
                                                <th>Tổng Tiền</th>
                                                <th>Trạng Thái</th>
                                                <th>Ngày Đặt</th>
                                                <th>Thao Tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                                <td><?php echo formatPrice($order['total']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $order['status'] === 'completed' ? 'success' : 
                                                            ($order['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ORDER_STATUS[$order['status']] ?? $order['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode($revenue_data); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => 'Tháng ' + d.month_label),
                datasets: [{
                    label: 'Doanh Thu (VNĐ)',
                    data: revenueData.map(d => d.revenue),
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
