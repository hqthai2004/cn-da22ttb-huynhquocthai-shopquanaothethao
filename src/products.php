<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$categories = getCategories($conn);
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? null;
$min_price = $_GET['min_price'] ?? null;
$max_price = $_GET['max_price'] ?? null;
$page = $_GET['page'] ?? 1;
$limit = 100; // Hiển thị 100 sản phẩm

$products = searchProducts($conn, $search, $category_id, $min_price, $max_price, $page, $limit);

// Đếm tổng số sản phẩm theo bộ lọc
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE p.status = 'active'";
$count_params = [];
$count_types = "";

if ($search) {
    $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_types .= "ss";
}

if ($category_id) {
    $count_sql .= " AND p.category_id = ?";
    $count_params[] = $category_id;
    $count_types .= "i";
}

if ($min_price) {
    $count_sql .= " AND p.price >= ?";
    $count_params[] = $min_price;
    $count_types .= "d";
}

if ($max_price) {
    $count_sql .= " AND p.price <= ?";
    $count_params[] = $max_price;
    $count_types .= "d";
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm - QT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/simple-nav.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Search Bar -->
    <div class="bg-light py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0">
                        <i class="fas fa-shopping-bag text-primary me-2"></i>Sản Phẩm
                    </h2>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="products.php">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm kiếm sản phẩm..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container my-5">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-filter"></i> Bộ Lọc</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="products.php">
                            <!-- Danh mục -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Danh Mục</label>
                                <?php foreach ($categories as $cat): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_id == $cat['id'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label"><?php echo htmlspecialchars($cat['name']); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Giá -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Khoảng Giá</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_price" 
                                            placeholder="Từ" value="<?php echo $min_price; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" 
                                            placeholder="Đến" value="<?php echo $max_price; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Áp Dụng</button>
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Xóa Bộ Lọc</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Sản Phẩm <?php echo $search ? '- Kết quả tìm kiếm: "' . htmlspecialchars($search) . '"' : ''; ?></h3>
                    <span class="text-muted"><?php echo $total_products; ?> sản phẩm</span>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không tìm thấy sản phẩm nào.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                        <div class="col-md-4">
                            <?php include 'includes/product-card.php'; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/wishlist.js"></script>
</body>
</html>
