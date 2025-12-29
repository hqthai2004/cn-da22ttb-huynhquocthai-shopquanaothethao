<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$credential = $data['credential'] ?? null;

if (!$credential) {
    echo json_encode(['success' => false, 'message' => 'Missing credential']);
    exit;
}

// Xác thực Google token
// Cần cài đặt: composer require google/apiclient
try {
    // Giải mã JWT token từ Google
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        throw new Exception('Invalid token format');
    }
    
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
    
    if (!$payload) {
        throw new Exception('Invalid token payload');
    }
    
    $email = $payload['email'];
    $name = $payload['name'];
    $google_id = $payload['sub'];
    
    // Kiểm tra user đã tồn tại chưa
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        // Tạo user mới
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        
        // Tạo username từ email (phần trước @)
        $username = explode('@', $email)[0];
        
        // Kiểm tra username đã tồn tại chưa, nếu có thì thêm số random
        $check_username = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check_username->num_rows > 0) {
            $username = $username . rand(100, 999);
        }
        
        $sql = "INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $password, $name);
        $stmt->execute();
        $user_id = $conn->insert_id;
        
        // Lấy thông tin user mới
        $user = [
            'id' => $user_id,
            'email' => $email,
            'full_name' => $name,
            'role' => 'customer'
        ];
    }
    
    // Đăng nhập
    $user_id = $user['id'];
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    
    // Merge giỏ hàng từ session_id (chưa đăng nhập) sang user_id (đã đăng nhập)
    $session_id = session_id();
    $session_cart_sql = "SELECT * FROM cart WHERE session_id = ? AND user_id IS NULL";
    $session_cart_stmt = $conn->prepare($session_cart_sql);
    $session_cart_stmt->bind_param("s", $session_id);
    $session_cart_stmt->execute();
    $session_cart_items = $session_cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (!empty($session_cart_items)) {
        foreach ($session_cart_items as $item) {
            // Kiểm tra sản phẩm đã có trong giỏ hàng của user chưa
            $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iis", $user_id, $item['product_id'], $item['size']);
            $check_stmt->execute();
            $existing = $check_stmt->get_result()->fetch_assoc();
            
            if ($existing) {
                // Cộng dồn số lượng
                $new_quantity = $existing['quantity'] + $item['quantity'];
                $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_quantity, $existing['id']);
                $update_stmt->execute();
                
                // Xóa item cũ từ session cart
                $delete_sql = "DELETE FROM cart WHERE id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $item['id']);
                $delete_stmt->execute();
            } else {
                // Cập nhật session cart thành user cart
                $update_sql = "UPDATE cart SET user_id = ?, session_id = NULL WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $user_id, $item['id']);
                $update_stmt->execute();
            }
        }
    }
    
    // Kiểm tra redirect
    $redirect = 'index.php';
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
