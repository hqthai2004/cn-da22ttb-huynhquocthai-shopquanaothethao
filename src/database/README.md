# Hướng Dẫn Sử Dụng Database

## Cách Import Database

1. Mở phpMyAdmin
2. Tạo database mới:
   ```sql
   CREATE DATABASE shopquanao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Chọn database `shopquanao`
4. Click tab "Import"
5. Chọn file `full-database.sql`
6. Click "Go" để import

## Thông Tin Đăng Nhập Admin

- **Username:** `admin`
- **Email:** `admin@qtshop.com`
- **Password:** `admin123`

## Nếu Không Đăng Nhập Được

### Cách 1: Chạy script reset password
1. Mở trình duyệt
2. Truy cập: `http://localhost/your-project/reset-admin-password.php`
3. Mật khẩu sẽ được reset về `admin123`
4. **XÓA file `reset-admin-password.php` sau khi reset thành công!**

### Cách 2: Update trực tiếp trong phpMyAdmin
1. Mở phpMyAdmin
2. Chọn database `shopquanao`
3. Chọn bảng `users`
4. Tìm user có `username = 'admin'`
5. Click "Edit"
6. Thay đổi field `password` thành:
   ```
   $2y$10$e0MYzXyjpJS7Pd0RVvHwHeFtXPRnuJQVED1yMvjXpF6eKxqKzJHOi
   ```
7. Click "Go" để lưu

### Cách 3: Chạy SQL query
```sql
UPDATE users 
SET password = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFtXPRnuJQVED1yMvjXpF6eKxqKzJHOi' 
WHERE username = 'admin';
```

## Thống Kê Database

### Sản phẩm (100 sản phẩm)
- **Áo thể thao:** 25 sản phẩm (ID 1-25)
- **Quần thể thao:** 25 sản phẩm (ID 26-50)
- **Phụ kiện:** 25 sản phẩm (ID 51-75)
- **Giày thể thao:** 25 sản phẩm (ID 76-100)

### Danh mục (4 danh mục)
1. Áo thể thao
2. Quần thể thao
3. Phụ kiện
4. Giày thể thao

### Người dùng (1 admin)
- Admin có quyền quản lý toàn bộ hệ thống

## Lưu Ý Bảo Mật

⚠️ **QUAN TRỌNG:**
- Đổi mật khẩu admin ngay sau khi cài đặt xong
- Xóa file `reset-admin-password.php` sau khi sử dụng
- Xóa file `generate-password.php` (file này chỉ dùng để test)
- Không để lộ thông tin đăng nhập ra ngoài

## Cấu Trúc Bảng

### users
- Quản lý tài khoản người dùng (admin & customer)

### categories
- Quản lý danh mục sản phẩm

### products
- Quản lý sản phẩm

### cart
- Giỏ hàng (hỗ trợ cả user đã đăng nhập và chưa đăng nhập)

### wishlist
- Danh sách yêu thích

### orders
- Đơn hàng

### order_items
- Chi tiết đơn hàng

### contacts
- Tin nhắn liên hệ từ khách hàng

### settings
- Cấu hình website
