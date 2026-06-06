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
3. Chạy script tự động khởi tạo database và nạp dữ liệu mẫu (seeding) từ thư mục gốc của dự án:

   ```powershell
   php import_db.php
   ```

   *Lưu ý: Nếu bị kẹt hoặc lỗi kết nối, vui lòng Restart Apache trên XAMPP và thử lại.*

### 2. Khởi chạy Backend API Server

Mở terminal/cmd tại thư mục gốc dự án và chạy:

```powershell
cd backend/public
php -S localhost:8000
```

API lúc này sẽ hoạt động tại địa chỉ: `http://localhost:8000/api`

### 3. Truy cập giao diện người dùng (Frontend)

Mở trình duyệt và truy cập theo đường dẫn XAMPP mặc định:

```text
http://localhost/Project-Web-Programming/index.php
```

Hệ thống sẽ tự động chuyển hướng bạn đến trang chủ của ứng dụng.

### 4. Tài khoản kiểm thử có sẵn (Seed Accounts)

Sử dụng các tài khoản sau để đăng nhập và kiểm tra chức năng:

- **Người bán (Seller):** `seller1` / mật khẩu: `seller123`
- **Người mua (Buyer):** `buyer1` / mật khẩu: `buyer123`
- **Quản trị viên (Admin):** `admin` / mật khẩu: `admin123`
- Hoặc bạn có thể tự đăng ký tài khoản mới trực tiếp trên giao diện.

### 5. Kiểm thử tự động (Optional)

Chạy script kiểm thử tích hợp API để kiểm tra kết nối:

```powershell
php tests/api-test.php
```
