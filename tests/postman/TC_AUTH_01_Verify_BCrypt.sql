-- TC-AUTH-01: Xác minh password đã được băm BCrypt trước khi lưu
-- 1) Chạy TC-AUTH-01A trên Postman.
-- 2) Mở Environment và copy giá trị test_username vào bên dưới.

SET @postman_username = '<DAN_TEST_USERNAME_VAO_DAY>';

SELECT
    ID,
    Username,
    Email,
    Password,
    CHAR_LENGTH(Password) AS hash_length,
    CASE
        WHEN Password LIKE '$2y$%' AND CHAR_LENGTH(Password) = 60 THEN 'PASS - BCrypt'
        ELSE 'FAIL - Không đúng định dạng BCrypt'
    END AS bcrypt_result,
    CASE
        WHEN Password <> 'Postman@123' THEN 'PASS - Không lưu plaintext'
        ELSE 'FAIL - Đang lưu plaintext'
    END AS plaintext_result
FROM users
WHERE Username = @postman_username;
