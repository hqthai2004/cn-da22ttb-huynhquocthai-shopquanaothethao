<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getCategories($conn);
$message = '';

// Lấy thông tin liên hệ
$contact_phone = getSetting($conn, 'contact_phone');
$contact_email = getSetting($conn, 'contact_email');
$contact_address = getSetting($conn, 'contact_address');

// Xử lý form liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'];
    $content = $_POST['message'];
    
    // Kiểm tra và thêm cột phone nếu chưa có
    $check_column = $conn->query("SHOW COLUMNS FROM contacts LIKE 'phone'");
    if ($check_column->num_rows == 0) {
        $conn->query("ALTER TABLE contacts ADD COLUMN phone VARCHAR(20) AFTER email");
    }
    
    $sql = "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $content);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.</div>';
    } else {
        $message = '<div class="alert alert-danger">Có lỗi xảy ra. Vui lòng thử lại.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Liên Hệ Với Chúng Tôi</h1>
            <p class="text-muted">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
        </div>
        
        <?php echo $message; ?>
        
        <div class="row justify-content-center">
            <!-- Form liên hệ -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Gửi Tin Nhắn</h5>
                        
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Chủ đề <span class="text-danger">*</span></label>
                                    <select class="form-select" name="subject" required>
                                        <option value="">Chọn chủ đề</option>
                                        <option value="Hỏi về sản phẩm">Hỏi về sản phẩm</option>
                                        <option value="Hỏi về đơn hàng">Hỏi về đơn hàng</option>
                                        <option value="Góp ý">Góp ý</option>
                                        <option value="Khiếu nại">Khiếu nại</option>
                                        <option value="Khác">Khác</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="message" rows="6" required></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-0">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.6!2d106.3441!3d9.9351!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a017ac20f2b2d5%3A0x1b0e0e0e0e0e0e0e!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBUcsOgIFZpbmg%3D!5e0!3m2!1svi!2s!4v1234567890123" 
                        width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
</body>
</html>
