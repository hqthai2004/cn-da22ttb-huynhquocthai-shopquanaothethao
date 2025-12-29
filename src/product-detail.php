<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header('Location: products.php');
    exit;
}

$product = getProductById($conn, $product_id);
if (!$product) {
    header('Location: products.php');
    exit;
}

$categories = getCategories($conn);

// Kiểm tra sản phẩm có trong wishlist không
$in_wishlist = false;
if (isLoggedIn()) {
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $in_wishlist = $stmt->get_result()->num_rows > 0;
}

// Lấy danh sách sizes
$sizes = !empty($product['sizes']) ? explode(',', $product['sizes']) : [];

// Sản phẩm liên quan
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
        LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php">Sản phẩm</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-6">
                <?php 
                $image_path = $product['image'] ?? 'uploads/products/default.jpg';
                if (!file_exists($image_path)) {
                    $image_url = 'https://via.placeholder.com/600x600/667eea/ffffff?text=' . urlencode($product['name']);
                } else {
                    $image_url = htmlspecialchars($image_path);
                }
                ?>
                <img src="<?php echo $image_url; ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; max-height: 600px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/600x600/667eea/ffffff?text=No+Image'">
            </div>
            <div class="col-md-6">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="text-muted">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                </p>
                
                <div class="mb-3">
                    <?php if ($product['sale_price']): ?>
                        <h3 class="text-danger"><?php echo formatPrice($product['sale_price']); ?></h3>
                        <p class="text-decoration-line-through text-muted"><?php echo formatPrice($product['price']); ?></p>
                    <?php else: ?>
                        <h3><?php echo formatPrice($product['price']); ?></h3>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <p><strong>Tình trạng:</strong> 
                        <?php if ($product['stock'] > 0): ?>
                            <span class="text-success">Còn hàng (<?php echo $product['stock']; ?>)</span>
                        <?php else: ?>
                            <span class="text-danger">Hết hàng</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="mb-4">
                    <h5>Mô tả sản phẩm</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <?php if (!empty($sizes)): ?>
                <div class="mb-4">
                    <h5>Chọn Kích Cỡ</h5>
                    <div class="btn-group" role="group" id="sizeGroup">
                        <?php foreach ($sizes as $size): ?>
                        <input type="radio" class="btn-check" name="size" id="size<?php echo trim($size); ?>" value="<?php echo trim($size); ?>" autocomplete="off">
                        <label class="btn btn-outline-primary" for="size<?php echo trim($size); ?>"><?php echo trim($size); ?></label>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-danger d-none" id="sizeError">Vui lòng chọn kích cỡ</small>
                </div>
                <?php endif; ?>
                
                <?php if ($product['stock'] > 0): ?>
                <!-- Quantity và Add to Cart -->
                <div class="d-flex gap-2 mb-3">
                    <input type="number" class="form-control" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" style="max-width: 100px;">
                    <button class="btn btn-primary btn-lg flex-fill" onclick="addToCartDetail()">
                        <i class="fas fa-cart-plus"></i> Thêm Vào Giỏ
                    </button>
                </div>
                
                <!-- Action Buttons Row -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-success btn-lg w-100" onclick="buyNow()">
                                <i class="fas fa-bolt"></i> Mua Ngay
                            </button>
                        <?php else: ?>
                            <a href="login.php?message=Vui lòng đăng nhập để mua hàng" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-bolt"></i> Mua Ngay
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline-danger btn-lg w-100" id="wishlistBtn" onclick="toggleWishlist()" data-in-wishlist="<?php echo $in_wishlist ? '1' : '0'; ?>">
                            <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span id="wishlistText"><?php echo $in_wishlist ? 'Đã Yêu Thích' : 'Yêu Thích'; ?></span>
                        </button>
                        <?php else: ?>
                        <a href="login.php?message=Vui lòng đăng nhập để lưu sản phẩm yêu thích" class="btn btn-outline-danger btn-lg w-100">
                            <i class="far fa-heart"></i> Yêu Thích
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- Out of Stock Layout -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-times"></i> Hết Hàng
                        </button>
                    </div>
                    <div class="col-6">
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline-danger btn-lg w-100" id="wishlistBtn" onclick="toggleWishlist()" data-in-wishlist="<?php echo $in_wishlist ? '1' : '0'; ?>">
                            <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span id="wishlistText"><?php echo $in_wishlist ? 'Đã Yêu Thích' : 'Yêu Thích'; ?></span>
                        </button>
                        <?php else: ?>
                        <a href="login.php?message=Vui lòng đăng nhập để lưu sản phẩm yêu thích" class="btn btn-outline-danger btn-lg w-100">
                            <i class="far fa-heart"></i> Yêu Thích
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($related_products)): ?>
        <div class="mt-5">
            <h3 class="mb-4">Sản Phẩm Liên Quan</h3>
            <div class="row g-4">
                <?php foreach ($related_products as $product): ?>
                <div class="col-md-3">
                    <?php include 'includes/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
    <script>
        function checkSize() {
            <?php if (!empty($sizes)): ?>
            const sizeSelected = document.querySelector('input[name="size"]:checked');
            const sizeError = document.getElementById('sizeError');
            
            if (!sizeSelected) {
                sizeError.classList.remove('d-none');
                return false;
            }
            sizeError.classList.add('d-none');
            return true;
            <?php else: ?>
            return true;
            <?php endif; ?>
        }
        
        function addToCartDetail() {
            if (!checkSize()) return;
            
            const quantity = document.getElementById('quantity').value;
            const sizeSelected = document.querySelector('input[name="size"]:checked');
            const size = sizeSelected ? sizeSelected.value : null;
            
            fetch('api/cart-add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    product_id: <?php echo $product_id; ?>, 
                    quantity: parseInt(quantity),
                    size: size
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Cart add detail response:', data); // Debug
                if (data.success) {
                    // Cập nhật số lượng giỏ hàng từ response
                    const cartCountElement = document.getElementById('cart-count');
                    console.log('Cart count element:', cartCountElement); // Debug
                    console.log('New cart count:', data.cart_count); // Debug
                    
                    if (cartCountElement && data.cart_count !== undefined) {
                        cartCountElement.textContent = data.cart_count;
                        // Thêm animation
                        cartCountElement.classList.add('animate-bounce');
                        setTimeout(() => {
                            cartCountElement.classList.remove('animate-bounce');
                        }, 500);
                    }
                    showNotification('✅ Đã thêm vào giỏ hàng!', 'success');
                } else {
                    showNotification('❌ ' + (data.message || 'Có lỗi xảy ra'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Có lỗi xảy ra', 'error');
            });
        }
        
        function buyNow() {
            if (!checkSize()) return;
            
            const quantity = document.getElementById('quantity').value;
            const sizeSelected = document.querySelector('input[name="size"]:checked');
            const size = sizeSelected ? sizeSelected.value : null;
            
            fetch('api/cart-add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    product_id: <?php echo $product_id; ?>, 
                    quantity: parseInt(quantity),
                    size: size
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'checkout.php';
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra', 'error');
                }
            });
        }
        
        function toggleWishlist() {
            const btn = document.getElementById('wishlistBtn');
            const icon = btn.querySelector('i');
            const text = document.getElementById('wishlistText');
            
            fetch('api/wishlist-toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    product_id: <?php echo $product_id; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        text.textContent = 'Đã Yêu Thích';
                        btn.dataset.inWishlist = '1';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        text.textContent = 'Yêu Thích';
                        btn.dataset.inWishlist = '0';
                    }
                    showNotification(data.message, 'success');
                    // Cập nhật số lượng wishlist
                    updateWishlistBadge();
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra', 'error');
                }
            });
        }
    </script>
</body>
</html>
