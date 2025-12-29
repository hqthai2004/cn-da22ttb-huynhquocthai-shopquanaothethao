<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$return_category = $_GET['return_category'] ?? '';
$return_search = $_GET['return_search'] ?? '';
$return_page = $_GET['return_page'] ?? 1;

if (!$id) {
    header('Location: products.php');
    exit;
}

$product = getProductById($conn, $id);
if (!$product) {
    header('Location: products.php');
    exit;
}

$categories = getCategories($conn);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $sale_price = $_POST['sale_price'] ?: NULL;
    $stock = $_POST['stock'];
    $sizes = $_POST['sizes'] ?: NULL;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'];
    
    // Xử lý upload ảnh
    $image = $product['image']; // Giữ ảnh cũ
    $upload_success = false;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . $id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Xóa ảnh cũ nếu có
                if ($image && file_exists('../' . $image)) {
                    @unlink('../' . $image);
                }
                $image = 'uploads/products/' . $file_name;
                $upload_success = true;
            }
        }
    }
    
    $sql = "UPDATE products SET category_id=?, name=?, description=?, price=?, sale_price=?, 
            stock=?, sizes=?, image=?, is_featured=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issddissisi", $category_id, $name, $description, $price, $sale_price, 
                      $stock, $sizes, $image, $is_featured, $status, $id);
    
    if ($stmt->execute()) {
        // Giữ nguyên trang edit, chỉ hiển thị thông báo thành công
        $msg = 'Cập nhật sản phẩm thành công!';
        if ($upload_success) {
            $msg .= ' Đã upload ảnh mới.';
        }
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $msg . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        // Reload lại thông tin sản phẩm
        $product = getProductById($conn, $id);
    } else {
        $message = '<div class="alert alert-danger">Lỗi: ' . $stmt->error . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Sản Phẩm - Admin</title>
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
                    <h1 class="h2">Chỉnh Sửa Sản Phẩm</h1>
                    <?php
                    // Tạo URL quay lại với các tham số lọc
                    $back_url = 'products.php';
                    $params = [];
                    if ($return_category) $params[] = 'category=' . urlencode($return_category);
                    if ($return_search) $params[] = 'search=' . urlencode($return_search);
                    if ($return_page && $return_page != 1) $params[] = 'page=' . urlencode($return_page);
                    if (!empty($params)) $back_url .= '?' . implode('&', $params);
                    ?>
                    <a href="<?php echo $back_url; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <?php echo $message; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                        <select class="form-select" name="category_id" required>
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo $cat['name']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Mô tả</label>
                                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giá <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giá khuyến mãi</label>
                                            <input type="number" class="form-control" name="sale_price" value="<?php echo $product['sale_price']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tồn kho <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="stock" value="<?php echo $product['stock']; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Size (Kích cỡ)</label>
                                        <input type="text" class="form-control" name="sizes" value="<?php echo htmlspecialchars($product['sizes'] ?? ''); ?>" placeholder="VD: S,M,L,XL hoặc 38,39,40,41,42,43">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> Nhập các size cách nhau bằng dấu phẩy. 
                                            Để trống nếu sản phẩm không có size (phụ kiện).
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Hình ảnh hiện tại</label>
                                        <div class="mb-2">
                                            <?php 
                                            // Thử nhiều đường dẫn có thể
                                            $image_path = $product['image'];
                                            $possible_paths = [
                                                '../' . $image_path,
                                                $image_path,
                                                '../' . str_replace('../', '', $image_path)
                                            ];
                                            
                                            $found_image = null;
                                            foreach ($possible_paths as $path) {
                                                if (file_exists($path)) {
                                                    $found_image = $path;
                                                    break;
                                                }
                                            }
                                            
                                            if ($found_image): 
                                            ?>
                                                <img src="<?php echo $found_image; ?>" class="img-fluid rounded border" style="max-height: 200px; width: 100%; object-fit: cover;" alt="Ảnh sản phẩm">
                                                <p class="text-muted small mt-1"><i class="fas fa-check-circle text-success"></i> <?php echo basename($image_path); ?></p>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-image"></i> Chưa có hình ảnh
                                                    <br><small>Đường dẫn: <?php echo htmlspecialchars($image_path); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <label class="form-label">Thay đổi hình ảnh</label>
                                        <input type="file" class="form-control" name="image" accept="image/*" id="imageInput">
                                        <small class="text-muted">Để trống nếu không muốn thay đổi</small>
                                        <div class="mt-2">
                                            <img id="imagePreview" class="img-fluid rounded border d-none" style="max-height: 200px; width: 100%; object-fit: cover;">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select class="form-select" name="status">
                                            <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                            <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Tạm ẩn</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" 
                                                <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">
                                                Sản phẩm nổi bật
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview ảnh trước khi upload
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
