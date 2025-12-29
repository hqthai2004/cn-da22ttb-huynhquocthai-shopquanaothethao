<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = $data['product_id'] ?? null;

    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    
    // Debug log
    error_log("Wishlist toggle - User ID: $user_id, Product ID: $product_id");

    // Kiểm tra xem đã có trong wishlist chưa
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Đã có -> Xóa khỏi wishlist
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        
        if ($stmt->execute()) {
            error_log("Wishlist item removed successfully");
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Đã xóa khỏi yêu thích']);
        } else {
            error_log("Failed to remove wishlist item: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Không thể xóa khỏi yêu thích']);
        }
    } else {
        // Chưa có -> Thêm vào wishlist
        $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        
        if ($stmt->execute()) {
            error_log("Wishlist item added successfully");
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Đã thêm vào yêu thích']);
        } else {
            error_log("Failed to add wishlist item: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Không thể thêm vào yêu thích']);
        }
    }
} catch (Exception $e) {
    error_log("Wishlist toggle error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?>
