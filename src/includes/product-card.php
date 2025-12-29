<div class="card product-card h-100">
    <?php 
    $image_path = $product['image'] ?? 'uploads/products/default.jpg';
    // Kiểm tra file có tồn tại không
    if (!file_exists($image_path)) {
        // Dùng placeholder từ dịch vụ online
        $image_url = 'https://via.placeholder.com/400x400/667eea/ffffff?text=' . urlencode($product['name']);
    } else {
        $image_url = htmlspecialchars($image_path);
    }
    ?>
    <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 250px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/400x400/667eea/ffffff?text=No+Image'">
    <?php if ($product['sale_price']): ?>
    <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
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
                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">Xem Chi Tiết</a>
                <?php if (isLoggedIn()): ?>
                    <?php
                    // Kiểm tra sản phẩm có trong wishlist không
                    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $_SESSION['user_id'], $product['id']);
                    $stmt->execute();
                    $in_wishlist = $stmt->get_result()->num_rows > 0;
                    ?>
                    <button class="btn btn-outline-danger btn-sm wishlist-btn" 
                            data-product-id="<?php echo $product['id']; ?>" 
                            data-in-wishlist="<?php echo $in_wishlist ? '1' : '0'; ?>"
                            title="<?php echo $in_wishlist ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích'; ?>">
                        <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart" style="color: <?php echo $in_wishlist ? '#dc3545' : '#6c757d'; ?>"></i>
                    </button>
                <?php else: ?>
                    <a href="login.php?message=Vui lòng đăng nhập để sử dụng tính năng yêu thích" 
                       class="btn btn-outline-danger btn-sm" 
                       title="Đăng nhập để yêu thích">
                        <i class="far fa-heart" style="color: #6c757d;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
