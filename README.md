# Chợ Thanh Lý Marketplace

Nền tảng thương mại điện tử C2C giúp trao đổi và thanh lý đồ cũ dành cho cộng đồng — nhanh chóng, tin cậy và tiết kiệm sử dụng PHP + MySQL + TailwindCSS.

## Sơ đồ cấu trúc thư mục (Directory Tree)

```text
Project-Web-Programming/
├── backend/
│   ├── public/                 # Entry point cho API (index.php)
│   ├── realtime/               # Xử lý các logic thời gian thực
│   ├── src/
│   │   ├── config/             # Cấu hình Database, JWT, v.v.
│   │   ├── controllers/        # Điều hướng và xử lý request
│   │   ├── core/               # Khung lõi (Router, Request, Response)
│   │   ├── middlewares/        # Bộ lọc kiểm tra quyền truy cập (AuthMiddleware)
│   │   ├── repositories/       # Truy vấn cơ sở dữ liệu trực tiếp
│   │   ├── services/           # Logic nghiệp vụ chính (Business logic)
│   │   ├── utils/              # Các hàm bổ trợ (JWT, Hash, v.v.)
│   │   └── validators/         # Ràng buộc và kiểm tra dữ liệu đầu vào
│   ├── storage/                # Lưu trữ file log, file tạm
│   └── uploads/                # Ảnh tải lên (sản phẩm, avatar)
├── database/
│   ├── 001_schema.sql          # Cấu trúc cơ sở dữ liệu
│   └── 002_seed.sql            # Dữ liệu mẫu (Seed data)
├── docs/                       # Tài liệu thiết kế & Hướng dẫn
├── frontend/
│   ├── assets/                 # CSS, JS, hình ảnh giao diện
│   │   ├── css/                # Định dạng style chung
│   │   └── js/                 # Logic JavaScript phía client (products.js, cart.js...)
│   ├── components/             # Các thành phần giao diện dùng chung (navbar, footer, session...)
│   └── pages/                  # Các trang nghiệp vụ chính
│       ├── admin/              # Giao diện Quản trị viên
│       ├── auth/               # Đăng ký, đăng nhập, khôi phục mật khẩu
│       ├── cart/               # Giỏ hàng
│       ├── home/               # Trang chủ
│       ├── payment/            # Thanh toán hóa đơn và lịch sử giao dịch
│       ├── products/           # Danh sách và chi tiết sản phẩm
│       ├── seller/             # Đăng tin thanh lý, thống kê cửa hàng
│       └── user/               # Thông tin tài khoản người dùng
├── tests/                      # Kịch bản kiểm thử API tự động
├── import_db.php               # Script tự động khởi tạo cơ sở dữ liệu nhanh
└── README.md                   # Tài liệu hướng dẫn dự án
```

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
   - Cho phép khôi phục mật khẩu bảo mật qua mã OTP mô phỏng lưu trong Session.
   - Giao diện đẹp mắt tích hợp tại `/frontend/pages/auth/forgot-password.php`.
2. **AJAX Upload ảnh người bán:**
   - Cho phép tải ảnh trực tiếp lên thư mục `/backend/uploads/products/` qua API `/api/products/upload`.
   - Giao diện Đăng tin mới có khu vực xem trước (Preview) ảnh mượt mà.
3. **Giỏ hàng & Đặt hàng tuần tự (C2C):**
   - Hỗ trợ chọn phương thức thanh toán trực tiếp.
   - Đồng bộ cơ chế thanh toán C2C tuần tự (loop API) cho từng mặt hàng trong giỏ hàng.
4. **Hệ thống Tìm kiếm gợi ý trực tiếp (Live Search Suggestions):**
   - Tích hợp khung gợi ý sản phẩm mượt mà tại Navbar với cơ chế chống rung (Debounce 300ms) tránh quá tải server.
   - Hỗ trợ phím di chuyển (Mũi tên Lên / Xuống) để tô sáng kết quả và nhấn `Enter` để truy cập trực tiếp.
5. **Giao diện quản trị phong cách gương kính mờ (Glassmorphic Admin Dashboard):**
   - Đồng bộ toàn diện các trang admin (`dashboard.php`, `products.php`, `users.php`, `orders.php`, `wallets.php`, `reports.php`) sang giao diện gương mờ cao cấp với các hiệu ứng hover, shadow chân thực.
   - Hiển thị các thống kê số liệu thực (Doanh thu, Người dùng, Đơn hàng, Đang chờ duyệt) từ CSDL. Tích hợp biểu đồ cột doanh thu 7 ngày gần nhất bằng Chart.js v4.
6. **Nhập trực tiếp số lượng trong giỏ hàng:**
   - Cho phép gõ nhập số lượng bất kỳ trong giỏ hàng thay vì nhấn click liên tục, tự động ràng buộc giá trị nhập với lượng tồn kho thực tế ở CSDL.
7. **Đề xuất thông minh & Bộ lọc nâng cao (Smart Proximity Recommendations & Filters):**
   - Bộ lọc theo **Khu vực** (tỉnh thành) và **Tình trạng** (mới/cũ) ở thanh bên trang danh mục.
   - Đề xuất sản phẩm tương tự cùng danh mục được sắp xếp thông minh theo độ gần gũi về giá (cùng phân khúc giá) hiển thị tại chân trang chi tiết sản phẩm.
8. **Tối ưu hóa hiển thị số lượng & Giao diện Responsive sản phẩm:**
   - Tối ưu hóa nhãn số lượng (`Độc bản`, `Còn X`) siêu nhỏ gọn (`text-[9px]` đến `text-[10px]`), loại bỏ hoàn toàn lỗi tràn khung và vỡ layout trên mọi màn hình từ Desktop, iPad tới Mobile.
   - Thiết kế lại danh mục lọc (`category.php`): Tự động xoay thành thanh cuộn ngang mượt mà trên Mobile/Tablet để tối ưu diện tích dọc, và hỗ trợ tự động xuống dòng trên Desktop để tránh tràn thanh sidebar.
   - Tích hợp cơ chế Bust Cache thủ công qua tham số phiên bản script (`v=20260626-4`) để trình duyệt cập nhật giao diện mới lập tức.

---

### 6. Kiểm thử tự động (Optional)

Chạy script kiểm thử tích hợp API để kiểm tra kết nối:

```powershell
php tests/api-test.php
```
