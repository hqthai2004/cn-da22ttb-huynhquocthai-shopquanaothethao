<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';

// Cập nhật setting
function updateSetting($conn, $key, $value) {
    $sql = "INSERT INTO settings (setting_key, setting_value, setting_type) VALUES (?, ?, 'text')
            ON DUPLICATE KEY UPDATE setting_value = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $key, $value, $value);
    return $stmt->execute();
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý upload logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $upload_dir = '../uploads/settings/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $file_name = 'logo_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
            updateSetting($conn, 'site_logo', 'uploads/settings/' . $file_name);
            $message .= '<div class="alert alert-success">Cập nhật logo thành công!</div>';
        }
    }
    
    // Xử lý upload banner
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
        $upload_dir = '../uploads/settings/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $file_name = 'banner_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['banner']['tmp_name'], $upload_path)) {
            updateSetting($conn, 'site_banner', 'uploads/settings/' . $file_name);
            $message .= '<div class="alert alert-success">Cập nhật banner thành công!</div>';
        }
    }
    
    // Cập nhật các thông tin khác
    if (isset($_POST['site_name'])) {
        updateSetting($conn, 'site_name', $_POST['site_name']);
        updateSetting($conn, 'site_description', $_POST['site_description']);
        updateSetting($conn, 'contact_phone', $_POST['contact_phone']);
        updateSetting($conn, 'contact_email', $_POST['contact_email']);
        updateSetting($conn, 'contact_address', $_POST['contact_address']);
        
        // Footer settings
        updateSetting($conn, 'footer_about', $_POST['footer_about']);
        updateSetting($conn, 'footer_facebook', $_POST['footer_facebook']);
        updateSetting($conn, 'footer_instagram', $_POST['footer_instagram']);
        updateSetting($conn, 'footer_youtube', $_POST['footer_youtube']);
        
        $message .= '<div class="alert alert-success">Cập nhật thông tin thành công!</div>';
    }
}

// Lấy tất cả settings
$logo = getSetting($conn, 'site_logo');
$banner = getSetting($conn, 'site_banner');
$site_name = getSetting($conn, 'site_name');
$site_description = getSetting($conn, 'site_description');
$contact_phone = getSetting($conn, 'contact_phone');
$contact_email = getSetting($conn, 'contact_email');
$contact_address = getSetting($conn, 'contact_address');
$footer_about = getSetting($conn, 'footer_about');
$footer_facebook = getSetting($conn, 'footer_facebook');
$footer_instagram = getSetting($conn, 'footer_instagram');
$footer_youtube = getSetting($conn, 'footer_youtube');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài Đặt Website - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/no-animation.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-cog"></i> Cài Đặt Website</h1>
                </div>
                
                <?php echo $message; ?>
                
                <div class="row">
                    <!-- Logo & Banner -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-image"></i> Logo & Banner</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <!-- Logo -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Logo Website</label>
                                        <div class="mb-2">
                                            <?php if ($logo && file_exists('../' . $logo)): ?>
                                            <img src="../<?php echo $logo; ?>" class="img-thumbnail" style="max-height: 100px;">
                                            <?php else: ?>
                                            <div class="alert alert-info">Chưa có logo</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" class="form-control" name="logo" accept="image/*">
                                        <small class="text-muted">Kích thước đề xuất: 200x80px</small>
                                    </div>
                                    
                                    <!-- Banner -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Banner Trang Chủ</label>
                                        <div class="mb-2">
                                            <?php if ($banner && file_exists('../' . $banner)): ?>
                                            <img src="../<?php echo $banner; ?>" class="img-thumbnail" style="max-width: 100%;">
                                            <?php else: ?>
                                            <div class="alert alert-info">Chưa có banner</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" class="form-control" name="banner" accept="image/*">
                                        <small class="text-muted">Kích thước đề xuất: 1920x600px</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập Nhật Hình Ảnh
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin website -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-info-circle"></i> Thông Tin Website</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Tên Website</label>
                                        <input type="text" class="form-control" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Mô Tả</label>
                                        <textarea class="form-control" name="site_description" rows="3"><?php echo htmlspecialchars($site_description); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Số Điện Thoại</label>
                                        <input type="text" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Địa Chỉ</label>
                                        <textarea class="form-control" name="contact_address" rows="2"><?php echo htmlspecialchars($contact_address); ?></textarea>
                                    </div>
                                    
                                    <hr class="my-4">
                                    <h6 class="mb-3">Thông Tin Footer</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Giới Thiệu Footer</label>
                                        <textarea class="form-control" name="footer_about" rows="2"><?php echo htmlspecialchars($footer_about); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Link Facebook</label>
                                        <input type="url" class="form-control" name="footer_facebook" value="<?php echo htmlspecialchars($footer_facebook); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Link Instagram</label>
                                        <input type="url" class="form-control" name="footer_instagram" value="<?php echo htmlspecialchars($footer_instagram); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Link YouTube</label>
                                        <input type="url" class="form-control" name="footer_youtube" value="<?php echo htmlspecialchars($footer_youtube); ?>">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập Nhật Thông Tin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-eye"></i> Xem Trước</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Logo:</h6>
                                <?php if ($logo && file_exists('../' . $logo)): ?>
                                <img src="../<?php echo $logo; ?>" style="max-height: 80px;">
                                <?php else: ?>
                                <p class="text-muted">Chưa có logo</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Thông tin liên hệ:</h6>
                                <p><i class="fas fa-phone"></i> <?php echo $contact_phone; ?></p>
                                <p><i class="fas fa-envelope"></i> <?php echo $contact_email; ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo $contact_address; ?></p>
                            </div>
                        </div>
                        <hr>
                        <h6>Banner:</h6>
                        <?php if ($banner && file_exists('../' . $banner)): ?>
                        <img src="../<?php echo $banner; ?>" class="img-fluid" style="max-width: 100%;">
                        <?php else: ?>
                        <p class="text-muted">Chưa có banner</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
