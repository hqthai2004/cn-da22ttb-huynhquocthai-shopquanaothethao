<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getCategories($conn);
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
            // Cập nhật số lượng và size
            $cart_id = $_POST['cart_id'];
            $quantity = max(1, intval($_POST['quantity']));
            $size = $_POST['size'] ?? null;
            
            if ($size) {
                $sql = "UPDATE cart SET quantity = ?, size = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isi", $quantity, $size, $cart_id);
            } else {
                $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $quantity, $cart_id);
            }
            $stmt->execute();
        } elseif ($_POST['action'] === 'remove') {
            // Xóa sản phẩm
            $cart_id = $_POST['cart_id'];
            $sql = "DELETE FROM cart WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
        }
        header('Location: cart.php');
        exit;
    }
}

$cart_items = getCart($conn, $user_id, $session_id);
$total = 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/no-animation.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Giỏ Hàng Của Bạn</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Giỏ hàng của bạn đang trống.
                <a href="products.php" class="alert-link">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): 
                                $item_price = $item['sale_price'] ?? $item['price'];
                                $item_total = $item_price * $item['quantity'];
                                $total += $item_total;
                            ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-md-2">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="text-muted mb-0"><?php echo formatPrice($item_price); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        
                                        <?php 
                                        // Lấy sizes của sản phẩm
                                        $product = getProductById($conn, $item['product_id']);
                                        $sizes = !empty($product['sizes']) ? explode(',', $product['sizes']) : [];
                                        if (!empty($sizes)): 
                                        ?>
                                        <div class="mb-2">
                                            <label class="form-label small">Size:</label>
                                            <select name="size" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <?php foreach ($sizes as $size): 
                                                    $size = trim($size);
                                                ?>
                                                <option value="<?php echo $size; ?>" <?php echo ($item['size'] == $size) ? 'selected' : ''; ?>>
                                                    <?php echo $size; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <label class="form-label small">Số lượng:</label>
                                            <input type="number" name="quantity" class="form-control form-control-sm" 
                                                value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" 
                                                onchange="this.form.submit()">
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-2">
                                    <strong><?php echo formatPrice($item_total); ?></strong>
                                </div>
                                <div class="col-md-1">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tổng Đơn Hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <strong><?php echo formatPrice($total); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <strong>30.000đ</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Tổng cộng:</strong>
                                <strong class="text-danger"><?php echo formatPrice($total + 30000); ?></strong>
                            </div>
                            <?php if (isLoggedIn()): ?>
                                <a href="checkout.php" class="btn btn-primary w-100">Tiến Hành Đặt Hàng</a>
                            <?php else: ?>
                                <a href="login.php?message=Vui lòng đăng nhập để tiếp tục" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập Để Đặt Hàng
                                </a>
                            <?php endif; ?>
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Tiếp Tục Mua Sắm</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
</body>
</html>
