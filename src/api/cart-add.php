<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$quantity = $data['quantity'] ?? 1;
$size = $data['size'] ?? null;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// Kiểm tra tồn kho
$product = getProductById($conn, $product_id);
if (!$product || $product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không đủ số lượng']);
    exit;
}

// Thêm vào giỏ hàng
if (addToCart($conn, $user_id, $session_id, $product_id, $quantity, $size)) {
    // Đếm số lượng sản phẩm trong giỏ hàng
    if ($user_id) {
        $cart_count_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_sql);
        $cart_count_stmt->bind_param("i", $user_id);
    } else {
        $cart_count_sql = "SELECT SUM(quantity) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
        $cart_count_stmt = $conn->prepare($cart_count_sql);
        $cart_count_stmt->bind_param("s", $session_id);
    }
    $cart_count_stmt->execute();
    $cart_count = $cart_count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm vào giỏ hàng',
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
