<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Xử lý xóa tin nhắn
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM contacts WHERE id = $id");
    
    // Reset AUTO_INCREMENT để ID tiếp theo bắt đầu từ 1 nếu không còn tin nhắn
    $count_result = $conn->query("SELECT COUNT(*) as total FROM contacts");
    $total_messages = $count_result->fetch_assoc()['total'];
    
    if ($total_messages == 0) {
        // Nếu không còn tin nhắn nào, reset AUTO_INCREMENT về 1
        $conn->query("ALTER TABLE contacts AUTO_INCREMENT = 1");
    } else {
        // Nếu còn tin nhắn, reset AUTO_INCREMENT về ID lớn nhất + 1
        $max_result = $conn->query("SELECT MAX(id) as max_id FROM contacts");
        $max_id = $max_result->fetch_assoc()['max_id'];
        $next_id = $max_id + 1;
        $conn->query("ALTER TABLE contacts AUTO_INCREMENT = $next_id");
    }
    
    header('Location: contacts.php?msg=deleted');
    exit;
}

// Xử lý xóa tất cả tin nhắn
if (isset($_GET['delete_all'])) {
    $conn->query("DELETE FROM contacts");
    $conn->query("ALTER TABLE contacts AUTO_INCREMENT = 1");
    header('Location: contacts.php?msg=deleted_all');
    exit;
}

// Xử lý đánh dấu đã đọc
if (isset($_GET['mark_read'])) {
    $id = $_GET['mark_read'];
    $conn->query("UPDATE contacts SET status = 'read' WHERE id = $id");
    header('Location: contacts.php?msg=marked');
    exit;
}

// Lấy danh sách tin nhắn
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM contacts WHERE 1=1";

if ($filter === 'new') {
    $sql .= " AND status = 'new'";
} elseif ($filter === 'read') {
    $sql .= " AND status = 'read'";
}

if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%')";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);

// Đếm số lượng
$total = $conn->query("SELECT COUNT(*) as c FROM contacts")->fetch_assoc()['c'];
$new_count = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE status = 'new'")->fetch_assoc()['c'];
$read_count = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE status = 'read'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tin Nhắn - Admin</title>
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
                    <h1 class="h2">Quản Lý Tin Nhắn</h1>
                    <?php if ($total > 0): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="?delete_all=1" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa TẤT CẢ tin nhắn? Hành động này không thể hoàn tác!')">
                                <i class="fas fa-trash-alt"></i> Xóa tất cả tin nhắn
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php if ($_GET['msg'] === 'deleted'): ?>
                        <div class="alert alert-success">Đã xóa tin nhắn thành công!</div>
                    <?php elseif ($_GET['msg'] === 'deleted_all'): ?>
                        <div class="alert alert-success">Đã xóa tất cả tin nhắn và reset ID về 1!</div>
                    <?php elseif ($_GET['msg'] === 'marked'): ?>
                        <div class="alert alert-success">Đã đánh dấu đã đọc!</div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Thống kê -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Tổng Tin Nhắn</h5>
                                <h2><?php echo $total; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Tin Nhắn Mới</h5>
                                <h2><?php echo $new_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Đã Đọc</h5>
                                <h2><?php echo $read_count; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bộ lọc -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <select name="filter" class="form-select" onchange="this.form.submit()">
                                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <option value="new" <?php echo $filter === 'new' ? 'selected' : ''; ?>>Tin nhắn mới</option>
                                    <option value="read" <?php echo $filter === 'read' ? 'selected' : ''; ?>>Đã đọc</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, email, chủ đề..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Danh sách tin nhắn -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Trạng thái</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Số ĐT</th>
                                        <th>Chủ đề</th>
                                        <th>Thời gian</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr class="<?php echo $row['status'] === 'new' ? 'table-warning' : ''; ?>">
                                                <td><?php echo $row['id']; ?></td>
                                                <td>
                                                    <?php if ($row['status'] === 'new'): ?>
                                                        <span class="badge bg-warning">Mới</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Đã đọc</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary view-message-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewModal<?php echo $row['id']; ?>"
                                                            data-message-id="<?php echo $row['id']; ?>"
                                                            data-message-status="<?php echo $row['status']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    
                                                    <!-- Modal xem chi tiết -->
                                                    <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Chi Tiết Tin Nhắn #<?php echo $row['id']; ?></h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <strong>Họ tên:</strong> <?php echo htmlspecialchars($row['name']); ?>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <strong>Số điện thoại:</strong> <?php echo htmlspecialchars($row['phone'] ?? '-'); ?>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <strong>Chủ đề:</strong> <?php echo htmlspecialchars($row['subject']); ?>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <strong>Nội dung:</strong>
                                                                        <div class="border p-3 mt-2 bg-light">
                                                                            <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo urlencode($row['email']); ?>&su=<?php echo urlencode('Thư phản hồi từ QT Shop - ' . $row['subject']); ?>" 
                                                                       target="_blank" 
                                                                       class="btn btn-primary">
                                                                        <i class="fas fa-reply"></i> Trả lời qua Gmail
                                                                    </a>
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Không có tin nhắn nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tự động đánh dấu đã đọc khi xem tin nhắn
        document.querySelectorAll('.view-message-btn').forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-message-id');
                const messageStatus = this.getAttribute('data-message-status');
                
                // Nếu tin nhắn chưa đọc, gửi request đánh dấu đã đọc
                if (messageStatus === 'new') {
                    fetch('?mark_read=' + messageId)
                        .then(() => {
                            // Cập nhật giao diện ngay lập tức không reload
                            const row = this.closest('tr');
                            const badge = row.querySelector('.badge');
                            
                            // Đổi badge từ "Mới" sang "Đã đọc"
                            if (badge) {
                                badge.classList.remove('bg-warning');
                                badge.classList.add('bg-success');
                                badge.textContent = 'Đã đọc';
                            }
                            
                            // Bỏ highlight màu vàng
                            row.classList.remove('table-warning');
                            
                            // Cập nhật data-message-status để không gọi lại
                            this.setAttribute('data-message-status', 'read');
                            
                            // Cập nhật số đếm tin nhắn mới trong sidebar (nếu có)
                            const newCountBadge = document.querySelector('.sidebar .badge.bg-danger');
                            if (newCountBadge) {
                                const currentCount = parseInt(newCountBadge.textContent);
                                if (currentCount > 1) {
                                    newCountBadge.textContent = currentCount - 1;
                                } else {
                                    newCountBadge.remove();
                                }
                            }
                        });
                }
            });
        });
    </script>
</body>
</html>
