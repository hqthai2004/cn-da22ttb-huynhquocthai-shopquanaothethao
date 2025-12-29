<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="fas fa-box"></i> Sản Phẩm
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-tags"></i> Danh Mục
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Đơn Hàng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                    <i class="fas fa-users"></i> Khách Hàng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contacts.php' ? 'active' : ''; ?>" href="contacts.php">
                    <i class="fas fa-envelope"></i> Tin Nhắn
                    <?php
                    // Hiển thị số tin nhắn mới
                    $check_table = $conn->query("SHOW TABLES LIKE 'contacts'");
                    if ($check_table && $check_table->num_rows > 0) {
                        $check_column = $conn->query("SHOW COLUMNS FROM contacts LIKE 'status'");
                        if ($check_column && $check_column->num_rows > 0) {
                            $new_contacts = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE status = 'new'")->fetch_assoc()['c'] ?? 0;
                            if ($new_contacts > 0) {
                                echo '<span class="badge bg-danger ms-2">' . $new_contacts . '</span>';
                            }
                        }
                    }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Báo Cáo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog"></i> Cài Đặt
                </a>
            </li>
        </ul>
    </div>
</nav>
