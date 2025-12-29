<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    die('Unauthorized');
}

$type = $_GET['type'] ?? 'daily';

// Set headers cho Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Bao_Cao_Doanh_Thu_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Bắt đầu output Excel
echo "\xEF\xBB\xBF"; // UTF-8 BOM

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        .header { font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .total { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>BÁO CÁO DOANH THU - QT SHOP</h2>
        <p>Ngày xuất: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Loại báo cáo: <?php echo $type === 'daily' ? 'Hàng Ngày (30 ngày gần nhất)' : 'Hàng Tháng (12 tháng gần nhất)'; ?></p>
    </div>

    <?php
    // Thống kê tổng quan
    $total_revenue = $conn->query("SELECT SUM(total) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
    $total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
    $total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
    $total_customers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch_assoc()['total'];
    ?>

    <h3>THỐNG KÊ TỔNG QUAN</h3>
    <table>
        <tr>
            <th>Chỉ số</th>
            <th>Giá trị</th>
        </tr>
        <tr>
            <td>Tổng Doanh Thu</td>
            <td><?php echo number_format($total_revenue, 0, ',', '.'); ?> đ</td>
        </tr>
        <tr>
            <td>Tổng Đơn Hàng</td>
            <td><?php echo $total_orders; ?></td>
        </tr>
        <tr>
            <td>Tổng Sản Phẩm</td>
            <td><?php echo $total_products; ?></td>
        </tr>
        <tr>
            <td>Tổng Khách Hàng</td>
            <td><?php echo $total_customers; ?></td>
        </tr>
    </table>

    <br><br>

    <?php if ($type === 'daily'): ?>
    <!-- Doanh thu theo ngày -->
    <?php
    $daily_revenue = $conn->query("
        SELECT DATE(created_at) as date,
               DATE_FORMAT(created_at, '%d/%m/%Y') as date_label,
               SUM(total) as revenue,
               COUNT(*) as orders
        FROM orders 
        WHERE status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    $total_daily_revenue = array_sum(array_column($daily_revenue, 'revenue'));
    $total_daily_orders = array_sum(array_column($daily_revenue, 'orders'));
    ?>
    
    <h3>DOANH THU THEO NGÀY (30 NGÀY GẦN NHẤT)</h3>
    <table>
        <tr>
            <th>STT</th>
            <th>Ngày</th>
            <th>Số Đơn Hàng</th>
            <th>Doanh Thu (VNĐ)</th>
        </tr>
        <?php foreach ($daily_revenue as $index => $day): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo $day['date_label']; ?></td>
            <td><?php echo $day['orders']; ?></td>
            <td><?php echo number_format($day['revenue'], 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total">
            <td colspan="2">TỔNG CỘNG</td>
            <td><?php echo $total_daily_orders; ?></td>
            <td><?php echo number_format($total_daily_revenue, 0, ',', '.'); ?></td>
        </tr>
    </table>

    <?php else: ?>
    <!-- Doanh thu theo tháng -->
    <?php
    $monthly_revenue = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
               DATE_FORMAT(created_at, '%m/%Y') as month_label,
               SUM(total) as revenue,
               COUNT(*) as orders
        FROM orders 
        WHERE status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    $total_monthly_revenue = array_sum(array_column($monthly_revenue, 'revenue'));
    $total_monthly_orders = array_sum(array_column($monthly_revenue, 'orders'));
    ?>
    
    <h3>DOANH THU THEO THÁNG (12 THÁNG GẦN NHẤT)</h3>
    <table>
        <tr>
            <th>STT</th>
            <th>Tháng</th>
            <th>Số Đơn Hàng</th>
            <th>Doanh Thu (VNĐ)</th>
        </tr>
        <?php foreach ($monthly_revenue as $index => $month): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo $month['month_label']; ?></td>
            <td><?php echo $month['orders']; ?></td>
            <td><?php echo number_format($month['revenue'], 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total">
            <td colspan="2">TỔNG CỘNG</td>
            <td><?php echo $total_monthly_orders; ?></td>
            <td><?php echo number_format($total_monthly_revenue, 0, ',', '.'); ?></td>
        </tr>
    </table>
    <?php endif; ?>

    <br><br>

    <!-- Top sản phẩm bán chạy -->
    <?php
    $top_products = $conn->query("
        SELECT p.name, 
               SUM(oi.quantity) as total_sold, 
               SUM(oi.subtotal) as revenue
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <h3>TOP 10 SẢN PHẨM BÁN CHẠY</h3>
    <table>
        <tr>
            <th>Hạng</th>
            <th>Tên Sản Phẩm</th>
            <th>Số Lượng Đã Bán</th>
            <th>Doanh Thu (VNĐ)</th>
        </tr>
        <?php foreach ($top_products as $index => $product): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo $product['total_sold']; ?></td>
            <td><?php echo number_format($product['revenue'], 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br><br>

    <!-- Doanh thu theo danh mục -->
    <?php
    $category_revenue = $conn->query("
        SELECT c.name, 
               SUM(oi.subtotal) as revenue,
               COUNT(DISTINCT o.id) as orders
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY c.id
        ORDER BY revenue DESC
    ")->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <h3>DOANH THU THEO DANH MỤC</h3>
    <table>
        <tr>
            <th>STT</th>
            <th>Danh Mục</th>
            <th>Số Đơn Hàng</th>
            <th>Doanh Thu (VNĐ)</th>
        </tr>
        <?php foreach ($category_revenue as $index => $cat): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo htmlspecialchars($cat['name']); ?></td>
            <td><?php echo $cat['orders']; ?></td>
            <td><?php echo number_format($cat['revenue'], 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br><br>
    <p style="text-align: center; color: #666;">
        <i>--- HẾT ---</i><br>
        Báo cáo được tạo tự động bởi hệ thống QT Shop
    </p>
</body>
</html>
