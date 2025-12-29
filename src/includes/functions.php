<?php
// Hàm lấy danh mục
function getCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Hàm lấy sản phẩm nổi bật
function getFeaturedProducts($conn, $limit = 8) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.is_featured = 1 AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Hàm lấy sản phẩm theo ID
function getProductById($conn, $id) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Hàm tìm kiếm sản phẩm
function searchProducts($conn, $keyword, $category_id = null, $min_price = null, $max_price = null, $page = 1, $limit = 12) {
    $offset = ($page - 1) * $limit;
    $params = [];
    $types = "";
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    if ($keyword) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search = "%$keyword%";
        $params[] = $search;
        $params[] = $search;
        $types .= "ss";
    }
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($min_price) {
        $sql .= " AND p.price >= ?";
        $params[] = $min_price;
        $types .= "d";
    }
    
    if ($max_price) {
        $sql .= " AND p.price <= ?";
        $params[] = $max_price;
        $types .= "d";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Hàm thêm vào giỏ hàng
function addToCart($conn, $user_id, $session_id, $product_id, $quantity = 1, $size = null) {
    // Kiểm tra xem sản phẩm với size này đã có trong giỏ chưa
    if ($user_id) {
        // Đã đăng nhập - kiểm tra theo user_id
        $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iis", $user_id, $product_id, $size);
    } else {
        // Chưa đăng nhập - kiểm tra theo session_id
        $check_sql = "SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND size = ? AND user_id IS NULL";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sis", $session_id, $product_id, $size);
    }
    
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Đã có -> Cập nhật số lượng
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        return $update_stmt->execute();
    } else {
        // Chưa có -> Thêm mới
        $sql = "INSERT INTO cart (user_id, session_id, product_id, quantity, size) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiis", $user_id, $session_id, $product_id, $quantity, $size);
        return $stmt->execute();
    }
}

// Hàm lấy giỏ hàng
function getCart($conn, $user_id, $session_id) {
    $sql = "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE ";
    
    if ($user_id) {
        $sql .= "c.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    } else {
        $sql .= "c.session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $session_id);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Hàm tạo đơn hàng
function createOrder($conn, $order_data, $cart_items) {
    $conn->begin_transaction();
    
    try {
        // Tạo mã đơn hàng
        $order_number = 'ORD' . date('YmdHis') . rand(1000, 9999);
        
        // Thêm đơn hàng - sử dụng tên cột đúng trong database
        $sql = "INSERT INTO orders (user_id, order_number, full_name, email, phone, 
                address, payment_method, subtotal, shipping_fee, total, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssddds", 
            $order_data['user_id'],
            $order_number,
            $order_data['customer_name'],
            $order_data['customer_email'],
            $order_data['customer_phone'],
            $order_data['shipping_address'],
            $order_data['payment_method'],
            $order_data['subtotal'],
            $order_data['shipping_fee'],
            $order_data['total'],
            $order_data['notes']
        );
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Thêm chi tiết đơn hàng - sử dụng tên cột đúng trong database
        $sql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, size, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?? $item['price'];
            $subtotal = $price * $item['quantity'];
            $size = $item['size'] ?? null;
            
            $stmt->bind_param("iisdiss", 
                $order_id,
                $item['product_id'],
                $item['name'],
                $price,
                $item['quantity'],
                $size,
                $subtotal
            );
            $stmt->execute();
            
            // Cập nhật tồn kho
            $update_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stmt->execute();
        }
        
        $conn->commit();
        return $order_number;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Create order error: " . $e->getMessage());
        return false;
    }
}

// Hàm format tiền
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

// Hàm lấy setting
function getSetting($conn, $key) {
    $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['setting_value'] : '';
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm kiểm tra admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Hàm lấy số lượng sản phẩm yêu thích
function getWishlistCount($conn, $user_id) {
    if (!$user_id) return 0;
    
    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}
?>
