<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Lấy tab hiện tại (mặc định là ngày)
$tab = $_GET['tab'] ?? 'daily';

// Thống kê tổng quan
$stats = [];
$stats['revenue'] = $conn->query("SELECT SUM(total) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$stats['orders'] = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$stats['products'] = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$stats['customers'] = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch_assoc()['total'];

// Doanh thu theo ngày (30 ngày gần nhất)
$daily_revenue = $conn->query("
    SELECT DATE(created_at) as date,
           DATE_FORMAT(created_at, '%d/%m') as date_label,
           SUM(total) as revenue,
           COUNT(*) as orders
    FROM orders 
    WHERE status = 'completed' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
")->fetch_all(MYSQLI_ASSOC);

// Doanh thu theo tháng (12 tháng gần nhất)
$monthly_revenue = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
           DATE_FORMAT(created_at, '%m/%Y') as month_label,
           SUM(total) as revenue,
           COUNT(*) as orders
    FROM orders 
    WHERE status = 'completed' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month
")->fetch_all(MYSQLI_ASSOC);

// Top sản phẩm bán chạy
$top_products = $conn->query("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Thống Kê - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/no-animation.css">
    <style>
        @media print {
            /* Ẩn sidebar, header, nút và tabs khi in */
            .sidebar, 
            nav.navbar,
            .btn-toolbar,
            .nav-tabs,
            canvas {
                display: none !important;
            }
            
            /* Mở rộng nội dung chính */
            main.col-md-10 {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 15px !important;
            }
            
            /* Header PDF */
            main::before {
                content: "BÁO CÁO DOANH THU - QT SHOP";
                display: block;
                font-size: 20px;
                font-weight: bold;
                text-align: center;
                margin-bottom: 5px;
                padding-bottom: 5px;
                border-bottom: 2px solid #333;
            }
            
            /* Ngày xuất */
            .border-bottom::after {
                content: "Ngày xuất: <?php echo date('d/m/Y H:i:s'); ?>";
                display: block;
                font-size: 11px;
                text-align: right;
                margin-top: 5px;
                color: #666;
            }
            
            /* Thống kê tổng quan - hiển thị ngang */
            .row:first-of-type {
                display: flex !important;
                flex-wrap: nowrap !important;
                gap: 10px;
                margin-bottom: 15px !important;
            }
            
            .row:first-of-type .col-md-3 {
                flex: 1 !important;
                max-width: 25% !important;
            }
            
            .row:first-of-type .card {
                margin-bottom: 0 !important;
                padding: 10px !important;
            }
            
            .row:first-of-type .card-body {
                padding: 5px !important;
            }
            
            .row:first-of-type h5 {
                font-size: 12px !important;
                margin-bottom: 5px !important;
            }
            
            .row:first-of-type h3 {
                font-size: 16px !important;
                margin: 0 !important;
            }
            
            /* Ẩn card biểu đồ */
            .card:has(canvas) {
                display: none !important;
            }
            
            /* Style cho bảng - thu gọn */
            .card {
                page-break-inside: avoid;
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                margin-bottom: 15px !important;
            }
            
            .card-header {
                background-color: #f0f0f0 !important;
                padding: 8px 10px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .card-header h5 {
                font-size: 14px !important;
                margin: 0 !important;
            }
            
            .card-body {
                padding: 10px !important;
            }
            
            /* Bảng thu gọn */
            table {
                font-size: 11px !important;
                margin: 0 !important;
            }
            
            th, td {
                padding: 5px 8px !important;
            }
            
            th {
                background-color: #4CAF50 !important;
                color: white !important;
                font-size: 11px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .badge {
                font-size: 10px !important;
                padding: 2px 6px !important;
            }
            
            /* Giới hạn số dòng hiển thị */
            .table-responsive table tbody tr:nth-child(n+11) {
                display: none !important;
            }
            
            /* Màu sắc */
            .bg-primary, .bg-success, .bg-warning, .bg-info {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Footer */
            body::after {
                content: "--- HẾT ---";
                display: block;
                text-align: center;
                margin-top: 15px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                color: #666;
                font-size: 11px;
            }
            
            /* Ẩn phần dư thừa */
            .mt-4, .mt-3 {
                margin-top: 10px !important;
            }
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
                    <h1 class="h2"><i class="fas fa-chart-line"></i> Báo Cáo Doanh Thu</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Xuất Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Xuất PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Thống kê tổng quan -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Tổng Doanh Thu</h5>
                                <h3><?php echo formatPrice($stats['revenue']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Tổng Đơn Hàng</h5>
                                <h3><?php echo $stats['orders']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Sản Phẩm</h5>
                                <h3><?php echo $stats['products']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Khách Hàng</h5>
                                <h3><?php echo $stats['customers']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?php echo $tab === 'daily' ? 'active' : ''; ?>" 
                           href="?tab=daily">
                            <i class="fas fa-calendar-day"></i> Hàng Ngày
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?php echo $tab === 'monthly' ? 'active' : ''; ?>" 
                           href="?tab=monthly">
                            <i class="fas fa-calendar-alt"></i> Hàng Tháng
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Hàng Ngày -->
                    <?php if ($tab === 'daily'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Doanh Thu 30 Ngày Gần Nhất</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyChart" height="80"></canvas>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>Chi Tiết Theo Ngày</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Số Đơn</th>
                                            <th>Doanh Thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_reverse($daily_revenue) as $day): ?>
                                        <tr>
                                            <td><?php echo $day['date_label']; ?></td>
                                            <td><span class="badge bg-primary"><?php echo $day['orders']; ?></span></td>
                                            <td><strong><?php echo formatPrice($day['revenue']); ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Hàng Tháng -->
                    <?php if ($tab === 'monthly'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Doanh Thu 12 Tháng Gần Nhất</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart" height="80"></canvas>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>Chi Tiết Theo Tháng</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tháng</th>
                                            <th>Số Đơn</th>
                                            <th>Doanh Thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_reverse($monthly_revenue) as $month): ?>
                                        <tr>
                                            <td><?php echo $month['month_label']; ?></td>
                                            <td><span class="badge bg-primary"><?php echo $month['orders']; ?></span></td>
                                            <td><strong><?php echo formatPrice($month['revenue']); ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Top Sản Phẩm -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy"></i> Top 10 Sản Phẩm Bán Chạy</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên Sản Phẩm</th>
                                        <th>Đã Bán</th>
                                        <th>Doanh Thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_products as $index => $product): ?>
                                    <tr>
                                        <td><span class="badge bg-warning"><?php echo $index + 1; ?></span></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><strong><?php echo $product['total_sold']; ?></strong></td>
                                        <td><strong class="text-success"><?php echo formatPrice($product['revenue']); ?></strong></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Xuất Excel
        function exportToExcel() {
            const tab = '<?php echo $tab; ?>';
            window.location.href = 'export-excel.php?type=' + tab;
        }
        
        // Xuất PDF
        function exportToPDF() {
            window.print();
        }

        // Dữ liệu cho biểu đồ
        <?php if ($tab === 'daily'): ?>
        const labels = <?php echo json_encode(array_column($daily_revenue, 'date_label')); ?>;
        const data = <?php echo json_encode(array_column($daily_revenue, 'revenue')); ?>;
        const chartId = 'dailyChart';
        const chartLabel = 'Doanh Thu (VNĐ)';
        <?php else: ?>
        const labels = <?php echo json_encode(array_column($monthly_revenue, 'month_label')); ?>;
        const data = <?php echo json_encode(array_column($monthly_revenue, 'revenue')); ?>;
        const chartId = 'monthlyChart';
        const chartLabel = 'Doanh Thu (VNĐ)';
        <?php endif; ?>

        // Tạo biểu đồ
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: chartLabel,
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + context.parsed.y.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
