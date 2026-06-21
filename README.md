# Auction Web Project

Cấu trúc khởi tạo cho đồ án web đấu giá realtime sử dụng PHP + MySQL.

## Folder chính

- `backend/`: xử lý backend PHP, API, realtime, service, repository.
- `frontend/`: giao diện, CSS, JavaScript, pages, components, ảnh upload.
- `database/`: schema MySQL, migrations, seeders, procedures, triggers, transaction demo.
- `docs/`: tài liệu thiết kế database, API, realtime, xử lý đồng thời, hướng dẫn setup.
- `tests/`: file test chức năng, API, transaction.

## Hướng dẫn Setup & Khởi chạy dự án

### 1. Cấu hình cơ sở dữ liệu (MySQL)

1. Đảm bảo bạn đã bật **Apache** và **MySQL** trên **XAMPP Control Panel**.
2. Kiểm tra thông tin kết nối (host, username, password) trong file [backend/src/config/Database.php](file:///d:/xampp/htdocs/Project-Web-Programming/backend/src/config/Database.php). Mặc định là:
   - Host: `localhost`
   - Database name: `c2c_used_marketplace`
   - Username: `root`
   - Password: `""` (để trống)
3. Thực hiện nạp cấu trúc (schema) và dữ liệu mẫu (seed) theo một trong hai cách dưới đây:

   **Cách A: Sử dụng Script tự động (Khuyên dùng - Nhanh nhất)**
   Mở terminal tại thư mục gốc của dự án và chạy lệnh:

   ```powershell
   php import_db.php
   ```

   _Lưu ý: Nếu bị kẹt hoặc lỗi kết nối, vui lòng Restart Apache trên XAMPP và chạy lại lệnh._

   **Cách B: Nhập thủ công qua phpMyAdmin**
   - Truy cập [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
   - Tạo mới CSDL có tên là `c2c_used_marketplace`.
   - Chọn CSDL vừa tạo, nhấp vào tab **Import** (Nhập).
   - Chọn tệp [database/001_schema.sql](file:///d:/xampp/htdocs/Project-Web-Programming/database/001_schema.sql) rồi nhấn **Go** (Thực hiện) để tạo cấu trúc bảng.
   - Nhấp lại vào tab **Import** (Nhập), chọn tệp [database/002_seed.sql](file:///d:/xampp/htdocs/Project-Web-Programming/database/002_seed.sql) rồi nhấn **Go** (Thực hiện) để nạp dữ liệu mẫu.

### 2. Truy cập ứng dụng qua XAMPP

Sau khi cài đặt xong CSDL, dự án sẽ được phục vụ toàn bộ (cả Frontend và Backend API) thông qua máy chủ Apache của XAMPP.

Mở trình duyệt web của bạn và truy cập trực tiếp theo đường dẫn:

```text
http://localhost/Project-Web-Programming/index.php
```

Hệ thống sẽ tự động khởi chạy và chuyển hướng bạn đến giao diện trang chủ của ứng dụng. Bạn không cần phải mở server PHP CLI (`php -S`) riêng biệt.

### 4. Tài khoản kiểm thử có sẵn (Seed Accounts)

Sử dụng các tài khoản sau (nạp từ `seed.sql` mới) để đăng nhập và kiểm tra chức năng. Mật khẩu mặc định của tất cả tài khoản mẫu là **`123456`**:

- **Quản trị viên (Admin):**
  - Tên đăng nhập: `admin`
- **Người bán (Sellers):**
  - Tên đăng nhập: `seller_a`, `seller_b`, `seller_c`, `seller_d`
- **Người mua (Buyers):**
  - Tên đăng nhập: `buyer_a`, `buyer_b`, `buyer_c`, `buyer_d`
  - _(Tài khoản `buyer_e` ở trạng thái bị khóa để thử nghiệm)_

---

### 5. Các tính năng mới được tích hợp

1. **Khôi phục mật khẩu (Forgot Password):**
   - Cho phép khôi phục qua mã OTP mô phỏng lưu trong Session.
   - Giao diện đẹp mắt tích hợp tại `/frontend/pages/auth/forgot-password.php`.
2. **AJAX Upload ảnh người bán:**
   - Cho phép tải ảnh trực tiếp lên thư mục `/backend/uploads/products/` qua API `/api/products/upload`.
   - Giao diện Đăng tin mới có khu vực xem trước (Preview) ảnh mượt mà.
3. **Giỏ hàng & Đặt hàng tuần tự (C2C):**
   - Hỗ trợ chọn phương thức thanh toán trực tiếp.
   - Đồng bộ cơ chế thanh toán C2C tuần tự (loop API) cho từng mặt hàng trong giỏ hàng.

---

### 6. Kiểm thử tự động (Optional)

Chạy script kiểm thử tích hợp API để kiểm tra kết nối:

```powershell
php tests/api-test.php
```
