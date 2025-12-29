<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$message = $_GET['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Tìm user theo username hoặc email
    $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $user_id = $user['id'];
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // Merge giỏ hàng từ session_id (chưa đăng nhập) sang user_id (đã đăng nhập)
        $session_id = session_id();
        $session_cart_sql = "SELECT * FROM cart WHERE session_id = ? AND user_id IS NULL";
        $session_cart_stmt = $conn->prepare($session_cart_sql);
        $session_cart_stmt->bind_param("s", $session_id);
        $session_cart_stmt->execute();
        $session_cart_items = $session_cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($session_cart_items)) {
            foreach ($session_cart_items as $item) {
                // Kiểm tra sản phẩm đã có trong giỏ hàng của user chưa
                $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iis", $user_id, $item['product_id'], $item['size']);
                $check_stmt->execute();
                $existing = $check_stmt->get_result()->fetch_assoc();
                
                if ($existing) {
                    // Cộng dồn số lượng
                    $new_quantity = $existing['quantity'] + $item['quantity'];
                    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $new_quantity, $existing['id']);
                    $update_stmt->execute();
                    
                    // Xóa item cũ từ session cart
                    $delete_sql = "DELETE FROM cart WHERE id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("i", $item['id']);
                    $delete_stmt->execute();
                } else {
                    // Cập nhật session cart thành user cart
                    $update_sql = "UPDATE cart SET user_id = ?, session_id = NULL WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $user_id, $item['id']);
                    $update_stmt->execute();
                }
            }
        }
        
        // Kiểm tra redirect sau khi đăng nhập
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        }
        
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
    }
}

$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-sign-in-alt"></i> Đăng Nhập</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($message); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập / Email</label>
                                <input type="text" class="form-control" name="username" placeholder="Nhập email của bạn" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="login-password" placeholder="Nhập mật khẩu" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('login-password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                            </button>
                        </form>
                        
                        <div class="position-relative my-4">
                            <hr>
                            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted">Hoặc</span>
                        </div>
                        
                        <button type="button" class="btn btn-outline-danger w-100 mb-3" onclick="loginWithGoogle()">
                            <i class="fab fa-google"></i> Đăng nhập với Google
                        </button>
                        
                        <!-- Nút ẩn để trigger Google Sign-In -->
                        <div id="googleLoginBtn" style="display: none;"></div>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        // Toggle hiển thị mật khẩu
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function loginWithGoogle() {
            // Client ID từ Google Cloud Console
            const CLIENT_ID = '<?php echo GOOGLE_CLIENT_ID; ?>';
            
            // Kiểm tra đã cấu hình chưa
            if (CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID') {
                alert('⚠️ Chưa cấu hình Google Client ID!\n\n' +
                      'Vui lòng:\n' +
                      '1. Tạo Google OAuth Client ID tại:\n' +
                      '   https://console.cloud.google.com/\n\n' +
                      '2. Mở file login.php\n' +
                      '3. Tìm dòng: const CLIENT_ID = ...\n' +
                      '4. Thay YOUR_GOOGLE_CLIENT_ID bằng Client ID của bạn\n\n' +
                      'Xem chi tiết trong file GOOGLE-LOGIN-SETUP.md');
                return;
            }
            
            // Khởi tạo Google Sign-In
            google.accounts.id.initialize({
                client_id: CLIENT_ID,
                callback: handleGoogleLogin,
                auto_select: false,
                cancel_on_tap_outside: false,
                prompt_parent_id: 'googleLoginBtn'
            });
            
            // Render nút Google Sign-In với tùy chọn chọn tài khoản
            google.accounts.id.renderButton(
                document.getElementById('googleLoginBtn'),
                { 
                    theme: 'outline',
                    size: 'large',
                    width: 250,
                    text: 'continue_with',
                    shape: 'rectangular'
                }
            );
            
            // Trigger click vào nút ẩn
            setTimeout(() => {
                document.getElementById('googleLoginBtn').querySelector('div[role="button"]').click();
            }, 100);
        }
        
        function handleGoogleLogin(response) {
            // Gửi token đến server để xác thực
            fetch('api/google-login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    credential: response.credential
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || 'index.php';
                } else {
                    alert('Đăng nhập thất bại: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
