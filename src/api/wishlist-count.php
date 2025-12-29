<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Debug log
    error_log("Wishlist count request - User ID: " . ($user_id ?? 'null'));
    
    $count = getWishlistCount($conn, $user_id);
    
    // Debug log
    error_log("Wishlist count result: " . $count);
    
    echo json_encode([
        'success' => true,
        'count' => (int)$count,
        'user_id' => $user_id
    ]);
} catch (Exception $e) {
    error_log("Wishlist count error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'count' => 0,
        'error' => $e->getMessage()
    ]);
}
?>
