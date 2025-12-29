<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php?message=Vui lòng đăng nhập để xem sản phẩm yêu thích');
    exit;
}

$categories = getCategories($conn);
$user_id = $_SESSION['user_id'];

// Lấy danh sách sản phẩm yêu thích
$sql = "SELECT p.*, c.name as category_name, w.created_at as added_at
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm Yêu Thích - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-heart text-danger"></i> Sản Phẩm Yêu Thích</h2>
        
        <?php if (empty($wishlist_products)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-heart-broken fa-3x mb-3"></i>
            <h4>Chưa có sản phẩm yêu thích</h4>
            <p>Hãy thêm sản phẩm bạn thích vào danh sách này!</p>
            <a href="products.php" class="btn btn-primary">Khám Phá Sản Phẩm</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($wishlist_products as $product): ?>
            <div class="col-md-3">
                <div class="card product-card h-100">
                    <?php 
                    $image_path = $product['image'] ?? 'uploads/products/default.jpg';
                    if (!file_exists($image_path)) {
                        $image_url = 'https://via.placeholder.com/400x400/667eea/ffffff?text=' . urlencode($product['name']);
                    } else {
                        $image_url = htmlspecialchars($image_path);
                    }
                    ?>
                    <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 250px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/400x400/667eea/ffffff?text=No+Image'">
                    <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" onclick="removeFromWishlist(<?php echo $product['id']; ?>, this)" title="Xóa khỏi yêu thích" style="z-index: 10;">
                        <i class="fas fa-times text-danger"></i>
                    </button>
                    <?php if ($product['sale_price']): ?>
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <div class="mt-auto">
                            <div class="price mb-2">
                                <?php if ($product['sale_price']): ?>
                                    <span class="text-danger fw-bold"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <span class="text-decoration-line-through text-muted ms-2"><?php echo formatPrice($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="fw-bold text-dark"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm w-100">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
    <script>
        function removeFromWishlist(productId, button) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                return;
            }
            
            fetch('api/wishlist-toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Xóa card khỏi giao diện
                    const card = button.closest('.col-md-3');
                    card.style.transition = 'opacity 0.3s';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Kiểm tra nếu không còn sản phẩm nào
                        const remainingProducts = document.querySelectorAll('.product-card').length;
                        if (remainingProducts === 0) {
                            location.reload();
                        }
                    }, 300);
                    
                    showNotification(data.message, 'success');
                    updateWishlistBadge();
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra', 'error');
            });
        }
    </script>
</body>
</html>
