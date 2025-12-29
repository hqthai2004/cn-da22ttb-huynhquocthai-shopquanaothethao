<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php" style="color: white !important;">
            <?php 
            // Lấy logo và tên site từ settings
            $admin_logo = getSetting($conn, 'site_logo');
            $admin_site_name = getSetting($conn, 'site_name') ?: 'QT Shop';
            
            if ($admin_logo && file_exists('../' . $admin_logo)): 
            ?>
                <img src="../<?php echo htmlspecialchars($admin_logo); ?>" alt="Logo" style="max-height: 40px; margin-right: 10px;">
            <?php else: ?>
                <i class="fas fa-running me-2" style="color: white;"></i>
            <?php endif; ?>
            <span style="color: white; font-weight: 600;">Trang Quản Trị Viên</span>
        </a>
        <div class="d-flex align-items-center">
            <a href="../index.php" class="btn btn-outline-light btn-sm me-2" target="_blank">
                <i class="fas fa-external-link-alt"></i> Xem Website
            </a>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Đăng Xuất
            </a>
        </div>
    </div>
</nav>
