<?php
// Cấu hình kết nối database MySQL
$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'shopquanao';

try {
    // Thử kết nối với port 3306 trước
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    
    // Nếu lỗi, thử port khác
    if ($conn->connect_error) {
        $port = ($port == 3307) ? 3306 : 3307;
        $conn = new mysqli($host, $user, $pass, $dbname, $port);
    }
    
    if ($conn->connect_error) {
        throw new Exception('Kết nối thất bại: ' . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    die('Lỗi database: ' . $e->getMessage() . '<br><a href="create-mysql-database.php">Thiết lập lại database</a>');
}
?>