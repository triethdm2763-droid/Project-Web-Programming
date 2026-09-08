# Jira Sprint 3 - Mô tả chi tiết công việc

## Thông tin Sprint

- **Tên Sprint:** Sprint 3 - State Transition, White-box & Coverage
- **Sprint Goal:** Hoàn thành State Transition Testing, Decision Table, Control Flow Testing; chạy PHPUnit, Postman, E2E và thu thập coverage thật từ công cụ.
- **Workflow:** To Do -> In Progress -> In Review -> Done
- **Definition of Done:** Có sản phẩm bàn giao, kết quả chạy thật, bằng chứng, cập nhật Excel Master và được nhóm trưởng review.

## 1. Ngô Hoàng Đắc Tri - QA Lead

### [QA-PLAN] Xác định phạm vi và phân công Sprint 3

**Mục tiêu:** Xác định rõ phạm vi kiểm thử, module, người phụ trách, kỹ thuật kiểm thử và sản phẩm bàn giao của Sprint 3.

**Công việc cần làm:**
- Kiểm tra yêu cầu giảng viên và tài liệu hiện có của nhóm.
- Chốt phạm vi Auth/User, Product/Search/Seller, Cart/Order/Payment và Admin/Notification/Category.
- Lập bảng truy vết Requirement/Function -> Technique -> Jira Task -> Test Case -> Evidence.
- Gán Assignee, Story Point, Priority, Label và Due date cho từng task.
- Ghi rõ các giới hạn chưa có requirement hoặc chưa được code hỗ trợ.

**Sản phẩm bàn giao:** Sprint Goal, bảng phân công 5 thành viên và bảng truy vết ban đầu.

**Tiêu chí nghiệm thu:** Tất cả task có Assignee và Story Point; mỗi module có người phụ trách; không đưa chức năng nhập sai mật khẩu ba lần tự động khóa vào phạm vi khi code chưa hỗ trợ; cả nhóm xác nhận phân công.

### [EXCEL] Bổ sung các sheet State Transition và White-box vào Master

**Mục tiêu:** Mở rộng Excel Master để lưu kết quả State Transition, Control Flow và Code Coverage theo cấu trúc thống nhất.

**Công việc cần làm:**
- Tạo `10_State_Transition_Model`, `11_State_Transition_TestCases` và `12_WhiteBox_Scope`.
- Tạo `13_Control_Flow_Graph`, `14_Control_Flow_TestCases` và `15_Code_Coverage_Result`.
- Thêm Test Case ID, Owner, Expected Result, Actual Result, Pass/Fail, Evidence ID và Jira ID.
- Thêm lựa chọn Pass, Fail, Not Run; kiểm tra công thức tổng hợp và liên kết dữ liệu.

**Sản phẩm bàn giao:** Excel Master có đầy đủ sheet 10-15 và bảng tổng hợp có thể truy vết.

**Tiêu chí nghiệm thu:** Test chưa chạy để `Not Run`; Pass rate và execution coverage tính riêng; code coverage chỉ lấy từ PHPUnit/Xdebug; file không lỗi công thức.

### [COVERAGE-SETUP] Cài Xdebug và cấu hình PHPUnit Coverage

**Mục tiêu:** Cấu hình PHP để PHPUnit đo được statement/line, branch và path coverage khi driver hỗ trợ.

**Công việc cần làm:**
- Kiểm tra PHP CLI bằng `php --ini` và `php -v`.
- Cài hoặc bật Xdebug đúng phiên bản PHP và xác nhận bằng `php -m`.
- Bật `xdebug.mode=coverage`.
- Cấu hình PHPUnit chỉ tính source trong `backend/src`, không tính `vendor`.
- Chạy thử coverage report và xử lý lỗi cấu hình.

**Sản phẩm bàn giao:** Log Xdebug, cấu hình PHPUnit, câu lệnh chạy và báo cáo coverage mẫu.

**Tiêu chí nghiệm thu:** Không còn `No code coverage driver available`; toàn bộ PHPUnit vẫn chạy được; cấu hình không thay đổi nghiệp vụ; câu lệnh được ghi vào tài liệu.

### [COVERAGE-RUN] Chạy toàn bộ PHPUnit và xuất báo cáo Coverage

**Mục tiêu:** Thu thập số liệu coverage thật của backend và lưu bằng chứng kiểm tra lại được.

**Công việc cần làm:**
- Chạy toàn bộ PHPUnit từ thư mục `backend`.
- Xuất `coverage-html`, `coverage.xml` và coverage dạng text.
- Ghi tổng test, assertions, Pass, Fail, Error và thời gian chạy.
- Ghi statement/line và branch/path coverage đúng theo output công cụ.
- Liệt kê file, hàm hoặc nhánh chưa được bao phủ.

**Sản phẩm bàn giao:** Log PHPUnit, HTML/XML coverage và sheet Code Coverage Result đã cập nhật.

**Tiêu chí nghiệm thu:** Số liệu Excel trùng output công cụ; không dùng “68 tests Pass” để kết luận coverage 100%; chỉ số không đo được ghi `Not Available`; mỗi số liệu có Evidence ID.

### [REPORT] Tổng hợp, review và hoàn thiện báo cáo cuối

**Mục tiêu:** Tổng hợp kết quả của nhóm thành báo cáo nhất quán, có truy vết và không che giấu Fail/Not Run.

**Công việc cần làm:**
- Review test case, bảng kỹ thuật, CFG, evidence và kết quả từng thành viên.
- Tổng hợp Formal Test Case, EP, BVA, Decision Table, State Transition và White-box.
- Tổng hợp PHPUnit, Postman, E2E và Code Coverage.
- Ghi defect, validation gap, Not Run và giới hạn môi trường.
- Đối chiếu Jira, Excel Master và báo cáo trước khi nộp.

**Sản phẩm bàn giao:** Báo cáo cuối, Excel Master cuối, thư mục evidence và danh sách defect.

**Tiêu chí nghiệm thu:** Mỗi kết quả truy vết được đến Test Case ID và Evidence ID; công thức coverage có tử số/mẫu số; không sửa Expected Result để test Pass; mọi task đã được review.

## 2. Cao Minh Trí - Auth/User

### [AUTH-DT] Thiết kế Decision Table cho đăng nhập

**Mục tiêu:** Thiết kế bảng quyết định cho các tổ hợp điều kiện đăng nhập dựa trên requirement và source code thật.

**Công việc cần làm:**
- Xác định điều kiện: đủ input, user tồn tại, password đúng, status active.
- Xác định action: lỗi validation, 401, 403 hoặc đăng nhập thành công 200.
- Tạo các rule R1...Rn; giải thích khi loại rule không khả thi.
- Tạo ít nhất một Formal Test Case cho mỗi rule được giữ lại.

**Sản phẩm bàn giao:** Decision Table và danh sách Rule ID -> Test Case ID.

**Tiêu chí nghiệm thu:** Có rule hợp lệ và không hợp lệ; mỗi rule có expected action rõ ràng; rule đã chạy có Actual Result/Evidence, chưa chạy ghi Not Run.

### [AUTH-ST] Thiết kế State Transition cho trạng thái tài khoản

**Mục tiêu:** Kiểm thử các chuyển trạng thái tài khoản mà project thực sự hỗ trợ.

**Công việc cần làm:**
- Mô hình hóa `active -> banned` và `banned -> active`.
- Bổ sung transition không hợp lệ như admin tự khóa chính mình nếu nghiệp vụ yêu cầu từ chối.
- Tạo Transition ID, Start State, Event, Expected End State và Valid/Invalid.
- Chạy API/Postman và ghi Actual End State.

**Sản phẩm bàn giao:** Sơ đồ trạng thái, bảng transition, test case và evidence.

**Tiêu chí nghiệm thu:** Không dùng mô hình sai mật khẩu ba lần nếu project chưa hỗ trợ; coverage = transition đã chạy có evidence / tổng transition đã mô hình hóa; transition chưa chạy không được tính.

### [AUTH-CFG] Vẽ Control Flow Graph cho AuthService::login()

**Mục tiêu:** Phân tích cấu trúc bên trong `AuthService::login()` và thiết kế các đường đi độc lập.

**Công việc cần làm:**
- Đọc source, đánh số node xử lý và decision.
- Vẽ CFG có node, edge, điểm bắt đầu và kết thúc.
- Tính V(G) bằng `E - N + 2` và đối chiếu `số decision + 1`.
- Liệt kê path: thiếu input, user không tồn tại, password sai, user bị khóa và thành công.

**Sản phẩm bàn giao:** Ảnh CFG, bảng node/edge và danh sách independent path.

**Tiêu chí nghiệm thu:** Số node/edge trên hình trùng bảng tính; đủ independent path theo V(G); mỗi path liên kết được đến PHPUnit Test Case.

### [AUTH-UNIT] Viết và chạy PHPUnit cho các independent path đăng nhập

**Mục tiêu:** Tạo hoặc bổ sung unit test để thực thi các independent path của `AuthService::login()`.

**Công việc cần làm:**
- Test thiếu username/password -> 400.
- Test user không tồn tại -> 401.
- Test password sai -> 401.
- Test user không active -> 403.
- Test dữ liệu hợp lệ -> 200 và có dữ liệu đăng nhập.

**Sản phẩm bàn giao:** PHPUnit tests, log chạy và bảng Path ID -> PHPUnit Test -> Actual Result.

**Tiêu chí nghiệm thu:** Test độc lập và lặp lại được; không sửa code nghiệp vụ chỉ để Pass; test Fail phải tạo defect hoặc ghi sai lệch requirement/code.

### [AUTH-EVIDENCE] Chạy Postman và E2E Auth, cập nhật bằng chứng

**Mục tiêu:** Xác minh Auth ở mức API và giao diện, sau đó cập nhật Excel Master.

**Công việc cần làm:**
- Chạy register, login, logout và các trường hợp lỗi liên quan.
- Chạy các file E2E Auth bằng CodeceptJS.
- Ghi HTTP status, Actual Result, Pass/Fail và thời gian chạy.
- Lưu screenshot/log và gán Evidence ID.

**Sản phẩm bàn giao:** Postman result, E2E result, screenshot/log và phần Auth trong Excel Master.

**Tiêu chí nghiệm thu:** Dùng environment chung; không lưu password/token nhạy cảm vào repository; mỗi kết quả có Evidence ID và Jira ID.

## 3. Hà Vũ Như Ngọc - Product/Search/Seller

### [PRODUCT-BVA] Rà soát EP/BVA cho sản phẩm

**Mục tiêu:** Xác định lớp tương đương và giá trị biên cho tên, giá, số lượng và dữ liệu tìm kiếm sản phẩm.

**Công việc cần làm:**
- Đối chiếu validation trong source, API và giao diện.
- Xác định valid/invalid partition cho từng field.
- Tạo min-1, min, min+1, max-1, max, max+1 khi có giới hạn thật.
- Ghi `Validation Gap` nếu source hoặc requirement không quy định giới hạn.

**Sản phẩm bàn giao:** Bảng EP, bảng BVA và danh sách Related Test Case ID.

**Tiêu chí nghiệm thu:** Không tự đặt min/max; mỗi partition/boundary có Test Case ID; chỉ test đã chạy có evidence mới được tính coverage.

### [PRODUCT-DT] Thiết kế Decision Table cho tạo/cập nhật sản phẩm

**Mục tiêu:** Bao phủ các tổ hợp seller, input, ảnh và trạng thái sản phẩm khi tạo hoặc cập nhật.

**Công việc cần làm:**
- Xác định điều kiện: đăng nhập, đúng seller, input hợp lệ, ảnh hợp lệ, sản phẩm tồn tại.
- Xác định action: tạo/cập nhật, validation error, unauthorized hoặc not found.
- Tạo rule và liên kết mỗi rule với Formal Test Case.

**Sản phẩm bàn giao:** Decision Table và bảng Rule ID -> Test Case ID.

**Tiêu chí nghiệm thu:** Phân biệt Condition/Action; không tạo rule trùng lặp; mỗi rule có expected HTTP status và behavior rõ ràng.

### [PRODUCT-ST] Thiết kế State Transition cho sản phẩm

**Mục tiêu:** Kiểm thử vòng đời sản phẩm theo các trạng thái thật trong project.

**Công việc cần làm:**
- Thiết kế `pending -> active`, `pending -> rejected` và `active -> sold`.
- Thiết kế transition không hợp lệ khi sản phẩm `sold` bị update hoặc delete.
- Chạy test và ghi Actual End State.

**Sản phẩm bàn giao:** Sơ đồ, bảng transition, test case và evidence.

**Tiêu chí nghiệm thu:** Trạng thái được đối chiếu database/source; mỗi transition có ID và Test Case ID; transition được code chấp nhận sai phải ghi defect.

### [PRODUCT-CFG] Vẽ CFG cho ProductService create/update/delete

**Mục tiêu:** Phân tích các nhánh xử lý quan trọng của ProductService và tạo basis paths.

**Công việc cần làm:**
- Chọn hàm có nhiều decision nhất trong create/update/delete làm CFG chính.
- Đánh số node, edge, decision và tính cyclomatic complexity.
- Liệt kê path invalid input, unauthorized, not found, invalid image/state và success.

**Sản phẩm bàn giao:** CFG, bảng V(G), danh sách path và PHPUnit Test Case liên quan.

**Tiêu chí nghiệm thu:** Ghi rõ file/hàm/commit; đủ path theo V(G); mỗi path có test hoặc ghi `Not Covered`.

### [PRODUCT-TEST] Chạy PHPUnit, Postman và E2E Product

**Mục tiêu:** Thực thi các test Product/Search/Seller và thu thập kết quả thật.

**Công việc cần làm:**
- Chạy PHPUnit cho các paths đã thiết kế.
- Chạy Postman list, detail, create, upload, update và delete.
- Chạy E2E tìm kiếm, lọc và quản lý sản phẩm.
- Cập nhật Actual Result, Pass/Fail và Evidence ID vào Master.

**Sản phẩm bàn giao:** Log PHPUnit, Postman result, E2E result và evidence.

**Tiêu chí nghiệm thu:** Request đúng API route hiện tại; ảnh upload có valid/invalid; Fail/Not Run được giữ nguyên và có ghi chú.

## 4. Minh Triết - Cart/Order/Payment

### [ORDER-DT] Thiết kế Decision Table cho Checkout/Payment

**Mục tiêu:** Thiết kế rule cho các tổ hợp điều kiện khi checkout và chọn phương thức thanh toán.

**Công việc cần làm:**
- Xác định điều kiện: đăng nhập, giỏ hàng hợp lệ, sản phẩm khả dụng, đủ stock, không mua hàng của mình, địa chỉ hợp lệ và payment method.
- Xác định action: tạo đơn, validation error, not found hoặc từ chối giao dịch.
- Liên kết mỗi rule với một Formal Test Case.

**Sản phẩm bàn giao:** Decision Table và bảng Rule ID -> Test Case ID.

**Tiêu chí nghiệm thu:** Bao phủ thành công và các nhóm thất bại; expected status phù hợp API contract; sai lệch requirement/code được ghi defect.

### [ORDER-ST] Thiết kế State Transition cho Order

**Mục tiêu:** Kiểm thử các transition hợp lệ và không hợp lệ của đơn hàng.

**Công việc cần làm:**
- Kiểm thử `pending -> confirmed` và `pending -> cancelled`.
- Kiểm thử `confirmed -> completed` và `confirmed -> cancelled`.
- Kiểm thử từ chối `completed -> cancelled` và `cancelled -> confirmed`.
- Ghi Start State, Action, Expected/Actual End State và Evidence ID.

**Sản phẩm bàn giao:** Sơ đồ trạng thái, bảng transition, test case và evidence.

**Tiêu chí nghiệm thu:** Kiểm tra trạng thái lại trong database/API; mỗi transition có test riêng; backend chấp nhận transition không hợp lệ thì test Fail và tạo Bug Jira.

### [ORDER-BVA] Rà soát BVA cho quantity và shipping address

**Mục tiêu:** Kiểm tra giá trị biên của số lượng mua và độ dài địa chỉ giao hàng theo validation thật.

**Công việc cần làm:**
- Đọc validation trong `OrderService::checkout()`.
- Tạo boundary cho shipping address quanh độ dài tối thiểu.
- Tạo boundary quantity dựa trên min hợp lệ và stock sản phẩm.
- Kiểm tra số âm, 0, 1, bằng stock và vượt stock.

**Sản phẩm bàn giao:** Bảng BVA, test data, Expected Result và Related Test Case ID.

**Tiêu chí nghiệm thu:** Boundary có nguồn requirement/source; mỗi giá trị có test case; không tự đặt quy tắc bắt buộc phone nếu code và requirement chưa thống nhất.

### [ORDER-CFG] Vẽ CFG cho checkout() và updateStatus()

**Mục tiêu:** Phân tích control flow của tạo đơn và cập nhật trạng thái đơn hàng.

**Công việc cần làm:**
- Vẽ CFG `checkout()` gồm validation, product not found, unavailable, insufficient stock, self-purchase và success.
- Phân tích `updateStatus()` gồm login, input, order, seller ownership và update.
- Tính V(G), liệt kê independent path và liên kết PHPUnit test.

**Sản phẩm bàn giao:** CFG, bảng node/edge, V(G), independent paths và test mapping.

**Tiêu chí nghiệm thu:** Không bỏ qua return sớm; mỗi path có expected status/result; phần thiếu kiểm tra transition được ghi defect hoặc risk.

### [ORDER-RETEST] Chạy lại ORDER-TC-03/04/05/07 và lưu evidence

**Mục tiêu:** Hoàn tất các test Order đang Not Run hoặc Fail và xác định nguyên nhân sai lệch.

**Công việc cần làm:**
- Chạy `ORDER-TC-03` và `ORDER-TC-04` đang Not Run.
- Chạy lại `ORDER-TC-05`: expected 400 nhưng kết quả cũ là 404.
- Chạy lại `ORDER-TC-07`: thiếu phone expected 400 nhưng kết quả cũ là 201.
- Đối chiếu requirement, Postman data và source; tạo Bug hoặc Requirement Clarification.

**Sản phẩm bàn giao:** Actual Result mới, evidence, Bug Jira hoặc kết luận requirement clarification.

**Tiêu chí nghiệm thu:** Mỗi case có thời gian chạy và evidence; không sửa Expected Result chỉ để Pass; thay đổi requirement phải ghi lý do và phiên bản.

## 5. Xuân Tuyền - Admin/Notification/Category

### [ADMIN-DT] Thiết kế Authorization Decision Table

**Mục tiêu:** Kiểm thử quyền truy cập Admin theo token, role và tài nguyên được thao tác.

**Công việc cần làm:**
- Xác định điều kiện: có token, token hợp lệ, role admin, tài nguyên tồn tại, action hợp lệ.
- Xác định action/status: 401, 403, 404, validation error hoặc success.
- Tạo rule cho admin và non-admin; liên kết rule với test case.

**Sản phẩm bàn giao:** Authorization Decision Table và Rule ID -> Test Case ID.

**Tiêu chí nghiệm thu:** Bao phủ không token, token sai, user thường và admin; mỗi rule có endpoint/status; không ghi token thật cố định vào file chia sẻ.

### [ADMIN-ST] Thiết kế State Transition cho User/Notification

**Mục tiêu:** Kiểm thử chuyển trạng thái user và notification trên API Admin.

**Công việc cần làm:**
- User: `active -> banned` và `banned -> active`.
- Notification: `unread -> read` và `read -> read`.
- Bổ sung transition invalid dựa trên source hoặc requirement thật.
- Chạy request và ghi Actual End State.

**Sản phẩm bàn giao:** Sơ đồ, bảng transition, test case và evidence.

**Tiêu chí nghiệm thu:** Mỗi transition có Transition ID/Test Case ID/Evidence ID; `read -> read` ghi rõ idempotent hay invalid; không tính transition chưa chạy vào coverage.

### [CATEGORY-EP] Rà soát Equivalence Partitioning cho Category

**Mục tiêu:** Chia lớp dữ liệu hợp lệ và không hợp lệ cho tạo, sửa, xóa và lọc category.

**Công việc cần làm:**
- Xác định partition cho tên rỗng, tên hợp lệ, trùng tên, ID tồn tại và không tồn tại.
- Tạo test data đại diện cho mỗi partition.
- Liên kết mỗi partition với Formal Test Case và API request.

**Sản phẩm bàn giao:** Bảng EP, test data, test case và evidence.

**Tiêu chí nghiệm thu:** Mỗi partition có lý do và dữ liệu cụ thể; bao phủ admin/user thường khi endpoint có phân quyền; kết quả có evidence.

### [ADMIN-CFG] Vẽ CFG cho chức năng cập nhật trạng thái người dùng

**Mục tiêu:** Phân tích luồng điều khiển của hàm Admin cập nhật trạng thái user và thiết kế basis paths.

**Công việc cần làm:**
- Đọc hàm xử lý trong AdminController, service hoặc repository liên quan.
- Đánh số node cho auth, role, input, user not found, self-action và update success.
- Tính cyclomatic complexity và liệt kê independent paths.
- Liên kết mỗi path với PHPUnit test.

**Sản phẩm bàn giao:** CFG, bảng V(G), independent paths và test mapping.

**Tiêu chí nghiệm thu:** Ghi file/tên hàm/commit; CFG trùng source tại commit đó; mỗi path có test hoặc ghi chưa bao phủ.

### [ADMIN-TEST] Chạy PHPUnit, Postman và E2E Admin/Category

**Mục tiêu:** Thực thi đầy đủ test Admin/Notification/Category, đặc biệt E2E Admin đang Not Run.

**Công việc cần làm:**
- Chạy API admin users, products, wallets, reports, notifications và categories trong phạm vi.
- Chạy lại `e2e/admin_test.js` và `e2e/category_filter_test.js`.
- Chạy PHPUnit cho các independent path Admin.
- Cập nhật Excel Master và đính kèm log hoặc screenshot.

**Sản phẩm bàn giao:** PHPUnit/Postman/E2E results, evidence và Excel Master đã cập nhật.

**Tiêu chí nghiệm thu:** `e2e/admin_test.js` có Actual Result, Pass/Fail hoặc lý do Not Run; không đánh Pass chỉ vì giao diện mở được; mỗi test có Expected/Actual/Evidence.

## Trường Jira dùng chung

- **Priority:** High cho coverage setup, control flow và test Fail; Medium cho thiết kế bảng; Low cho việc định dạng hoặc evidence nhỏ.
- **Labels:** `sprint-3`, `state-transition`, `decision-table`, `white-box`, `control-flow`, `phpunit`, `postman`, `e2e`, `coverage`, `report`.
- **Tên evidence:** `EV-[MODULE]-[TYPE]-[NUMBER]`, ví dụ `EV-AUTH-CFG-01`.
- **Liên kết Bug:** Dùng `is caused by` hoặc `relates to` để liên kết test Fail với Bug Jira.
- **Quy tắc review:** Thành viên chuyển task sang `In Review`; chỉ nhóm trưởng chuyển `Done` sau khi kiểm tra file và evidence.
