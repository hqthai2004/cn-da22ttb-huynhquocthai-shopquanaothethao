<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// Đếm tổng số lượng sản phẩm trong giỏ hàng
if ($user_id) {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
}

$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$count = $result['total'] ?? 0;

echo json_encode(['count' => (int)$count]);
?>
