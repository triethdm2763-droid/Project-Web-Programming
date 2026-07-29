# Hướng dẫn test Postman – Thành viên 2 (Authentication)

## 1. Phạm vi đã làm

Bộ test bám trực tiếp vào các endpoint trong project:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`

Các case được bao phủ: **TC-AUTH-01, TC-AUTH-02, TC-AUTH-03, TC-AUTH-04, TC-JWT-01, TC-JWT-02, TC-OTP-01, TC-OTP-02**.

## 2. Chuẩn bị project

1. Đặt project tại `C:\xampp\htdocs\Project-Web-Programming`.
2. Bật **Apache** và **MySQL** trong XAMPP.
3. Import `database/001_schema.sql`, sau đó `database/002_seed.sql`.
4. Kiểm tra API tại:

```text
http://localhost/Project-Web-Programming/backend/public/index.php/api/auth/me
```

Khi chưa đăng nhập, API phải trả HTTP `401`.

## 3. Import và chạy Postman

1. Import `ThanhVien2_Authentication.postman_collection.json`.
2. Import `ChoThanhLy_Local.postman_environment.json`.
3. Chọn environment **Chợ Thanh Lý - Local XAMPP**.
4. Mở Collection Runner và chạy folder **Authentication** theo đúng thứ tự.
5. Bật lưu cookie của Postman; session PHP và OTP phụ thuộc cookie `PHPSESSID`.

Collection tự sinh `test_username` và `test_email` mới ở request đầu tiên, nên có thể chạy nhiều lần.

## 4. Bảng kết quả mong đợi

| Test case | Kỳ vọng chính |
|---|---|
| TC-AUTH-01A | Register hợp lệ trả `201`, có `user_id` |
| TC-AUTH-01B | Password dưới 8 ký tự trả `400` |
| TC-AUTH-02A/B | Trùng username hoặc email trả `409` và đúng trường lỗi |
| TC-AUTH-03A | Login thành công trả `200`, JWT 3 phần, có `exp` |
| TC-AUTH-03B | `/auth/me` đọc được session vừa tạo |
| TC-AUTH-04A | Sai mật khẩu trả `401` |
| TC-AUTH-04B | Bỏ trống định danh trả `400` |
| TC-AUTH-04C | Tài khoản `buyer_e` bị khóa trả `403` |
| TC-JWT-01 | Bearer token hợp lệ được `/auth/me` chấp nhận |
| TC-JWT-02A | Token bị sửa chữ ký trả `401` |
| TC-JWT-02B | Token hết hạn trả `401` |
| TC-OTP-01 | OTP đúng trong 5 phút đổi mật khẩu thành công |
| TC-OTP-02A | OTP sai trả `400` |
| TC-OTP-02B | OTP sau 300 giây trả `400`, báo hết hạn |

## 5. Xác minh BCrypt

Postman chỉ thấy HTTP response, trong khi API cố ý không trả trường `Password`. Vì vậy TC-AUTH-01 về **BCrypt trước khi lưu** phải xác minh thêm trong MySQL:

1. Chạy request `TC-AUTH-01A`.
2. Mở Postman Environment, copy giá trị `test_username`.
3. Dán vào file `TC_AUTH_01_Verify_BCrypt.sql`.
4. Chạy SQL; kết quả đúng phải có `hash_length = 60`, tiền tố `$2y$`, và không bằng plaintext `Postman@123`.

## 6. Test OTP hết hạn 300 giây

Không nên bắt Collection Runner chờ hơn 5 phút. Thực hiện thủ công:

1. Chạy `[MANUAL] TC-OTP-02B.1 - Tạo OTP để kiểm tra hết hạn`.
2. Chờ **hơn 300 giây**.
3. Đặt biến environment `run_expiry_test` thành `true`.
4. Chạy `[MANUAL] TC-OTP-02B.2 - Từ chối OTP sau 300 giây`.
5. Kỳ vọng HTTP `400` và thông báo chứa `hết hạn`.
6. Đặt lại `run_expiry_test=false` sau khi test.

## 7. Lưu ý phát hiện từ project

- URL ổn định nhất cho XAMPP là `.../backend/public/index.php/api/...`; một số file frontend dùng URL có `index.php`, một số dùng rewrite không có `index.php`.
- Login nhận field `username`, nhưng service cho phép giá trị là username hoặc email. Vì vậy case “email rỗng” được biểu diễn bằng `username: ""`.
- README ghi mật khẩu seed là `123456`; comment trong một số file seed ghi `280606`, nhưng BCrypt hash thực tế khớp **`123456`**.
- OTP đang được trả trực tiếp trong response vì project mô phỏng local. Không nên áp dụng cách này ở production.
- JWT secret đang hard-code trong `JWT.php`; đây là rủi ro bảo mật nếu triển khai thật.

## 8. Giới hạn kiểm chứng trong gói này

Mã PHP đã được kiểm tra syntax và các assertion đã được đối chiếu với controller/service/repository. Gói chưa được chạy end-to-end trong môi trường tạo file vì không có Apache, MySQL và `pdo_mysql`; hãy chạy Collection Runner trên máy XAMPP của nhóm để lấy ảnh kết quả Pass/Fail thực tế.
