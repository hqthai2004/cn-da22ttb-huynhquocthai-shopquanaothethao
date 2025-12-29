<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php?message=Vui lòng đăng nhập để tiếp tục mua hàng');
    exit;
}

$categories = getCategories($conn);
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();
$cart_items = getCart($conn, $user_id, $session_id);

// Lấy thông tin user để tự động điền form
$user_info = null;
if ($user_id) {
    $sql = "SELECT full_name, email, phone, address FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_info = $stmt->get_result()->fetch_assoc();
}

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['sale_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping_fee = 30000;
$total = $subtotal + $shipping_fee;

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_data = [
        'user_id' => $user_id,
        'customer_name' => $_POST['customer_name'],
        'customer_email' => $_POST['customer_email'],
        'customer_phone' => $_POST['customer_phone'],
        'shipping_address' => $_POST['shipping_address'],
        'payment_method' => $_POST['payment_method'],
        'subtotal' => $subtotal,
        'shipping_fee' => $shipping_fee,
        'total' => $total,
        'notes' => $_POST['notes'] ?? ''
    ];
    
    $order_number = createOrder($conn, $order_data, $cart_items);
    
    if ($order_number) {
        // Xóa giỏ hàng
        if ($user_id) {
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        } else {
            $sql = "DELETE FROM cart WHERE session_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $session_id);
        }
        $stmt->execute();
        
        $_SESSION['success'] = "Đặt hàng thành công! Mã đơn hàng: $order_number";
        header("Location: order-success.php?order=$order_number");
        exit;
    } else {
        $error = "Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/no-animation.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-credit-card"></i> Thanh Toán</h2>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Thông Tin Giao Hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="customer_email" value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="customer_phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="shipping_address" rows="3" required><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5>Phương Thức Thanh Toán</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" value="cod" id="cod" checked>
                                <label class="form-check-label" for="cod">
                                    <i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng (COD)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="qr" id="qr">
                                <label class="form-check-label" for="qr">
                                    <i class="fas fa-qrcode"></i> Quét mã QR
                                </label>
                            </div>
                            <div id="qr-code" class="mt-3" style="display: none;">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <?php
                                        // Thông tin ngân hàng (Thay đổi thông tin này)
                                        $bank_id = "970422"; // Mã ngân hàng MB Bank
                                        $account_no = "0352197204"; // Số tài khoản
                                        $account_name = "HUYNH QUOC THAI"; // Tên chủ tài khoản
                                        $amount = $total;
                                        $description = "DH" . time(); // Mã đơn hàng
                                        
                                        // Tạo URL QR Code sử dụng VietQR API
                                        $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.png?amount={$amount}&addInfo=" . urlencode($description);
                                        ?>
                                        <img src="<?php echo $qr_url; ?>" class="img-fluid" alt="QR Code" style="max-width: 300px; border: 2px solid #ddd; border-radius: 8px;">
                                        <div class="mt-3">
                                            <p class="mb-1"><strong>Ngân hàng:</strong> MB Bank</p>
                                            <p class="mb-1"><strong>Số TK:</strong> <?php echo $account_no; ?></p>
                                            <p class="mb-1"><strong>Chủ TK:</strong> <?php echo $account_name; ?></p>
                                            <p class="mb-1"><strong>Số tiền:</strong> <span class="text-danger fw-bold"><?php echo formatPrice($total); ?></span></p>
                                            <p class="mb-0"><strong>Nội dung:</strong> <code><?php echo $description; ?></code></p>
                                        </div>
                                        <div class="alert alert-warning mt-3 mb-0">
                                            <small><i class="fas fa-info-circle"></i> Vui lòng chuyển khoản đúng nội dung để xác nhận đơn hàng</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h5>Đơn Hàng Của Bạn</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): 
                                $price = $item['sale_price'] ?? $item['price'];
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <div><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></div>
                                    <?php if (!empty($item['size'])): ?>
                                    <small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <span><?php echo formatPrice($price * $item['quantity']); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <strong><?php echo formatPrice($subtotal); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <strong><?php echo formatPrice($shipping_fee); ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Tổng cộng:</strong>
                                <strong class="text-danger fs-5"><?php echo formatPrice($total); ?></strong>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-check"></i> Đặt Hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('qr-code').style.display = 
                    this.value === 'qr' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
