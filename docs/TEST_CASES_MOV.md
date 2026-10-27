# 📋 BẢNG TỔNG HỢP TEST CASE KIỂM THỬ API & CHỨC NĂNG
## NHÓM CHỨC NĂNG: MOVIE & DISCOVERY (MOV) — HỆ THỐNG NETFIX2

> **Tài liệu kiểm thử phần mềm (Software Test Case Specification)**  
> **Dự án:** Netfix2 — Website Xem Phim Trực Tuyến  
> **Module phụ trách:** Movie & Discovery (MOV)  
> **Vai trò:** Senior QA Engineer  
> **Môi trường thử nghiệm:** Local XAMPP (`http://localhost/netfix2`), PHP 8.5+, MySQL 8.0+  
> **Công cụ thực thi tự động:** Postman / Newman CLI / CodeceptJS  

---

## 1. THÔNG TIN QUẢN TRỊ TÀI LIỆU (Document Control)

| Thuộc tính | Chi tiết |
| :--- | :--- |
| **Mã tài liệu** | `TC-SPEC-NETFIX2-MOV-v1.0` |
| **Phiên bản** | `1.0.0` |
| **Ngày tạo** | 25/10/2026 |
| **Trạng thái** | Approved / Ready for Execution |
| **Tác giả** | Senior QA Engineer |
| **Hệ thống mục tiêu** | Netfix2 Streaming Web Application & RESTful API |

---

## 2. PHẠM VI & KỸ THUẬT ÁP DỤNG (Scope & Test Techniques)

### 2.1. Danh sách Endpoints trong phạm vi
1. `GET /index.php`: Trang chủ khám phá (Trending, Top Movies Slider, Phim mới, Phim bộ, Phim lẻ, Hoạt hình).
2. `GET /movies.php`: Danh mục và bộ lọc phim theo thể loại (`genre`), thể loại chi tiết (`sub_genre`), quốc gia (`country`), năm (`year`).
3. `GET /search.php`: Tìm kiếm phim theo từ khóa (`q`), thể loại, quốc gia.
4. `GET /watch.php`: Trang chi tiết phim, danh sách tập phim (`episodes`), kiểm tra phân quyền tài khoản VIP (`is_vip`).
5. `GET /api/view_counter.php`: API RESTful tự động cập nhật lượt xem phim (`view_count`).
6. `POST /add_movie.php` & `GET /manage_movies.php`: Quản trị nội dung phim (Admin Role).

### 2.2. Kỹ thuật thiết kế kiểm thử áp dụng
* **Equivalence Partitioning (EP - Phân vùng tương đương):** Phân chia tập dữ liệu đầu vào (từ khóa hợp lệ, từ khóa không tồn tại, thể loại hợp lệ/không hợp lệ, ID phim tồn tại/không tồn tại).
* **Boundary Value Analysis (BVA - Phân tích giá trị biên):** Kiểm thử tham số ID số nguyên biên (`id=0`, `id=-1`, `id=1`, `id=2147483647`).
* **Error Guessing (Đoán lỗi):** Kiểm thử chuỗi rỗng (`q=`), chuỗi khoảng trắng (`q=%20%20`), ID dạng text (`id=abc`).
* **Security & Fuzzing Input:** Kiểm thử chống tấn công Reflected XSS (`<script>alert(1)</script>`) và SQL Injection (`' OR '1'='1`).
* **Decision Table Testing:** Ma trận phân quyền xem phim VIP giữa loại phim (`Free` vs `VIP`) và loại tài khoản (`Guest`, `User thường`, `User VIP`).

---

## 3. MA TRẬN TEST CASE CHI TIẾT (Detailed Test Case Matrix)

### 📌 Phân rã kiểm thử Task KAN-22: Thiết kế Test Cases cho Discovery Homepage & Catalog Filter
* **Jira Task Key:** `KAN-22`
* **Parent Story:** `KAN-21` (`[KAN-21] Kiểm thử API Khám phá Trang chủ & Lọc Danh mục Phim`)
* **Phạm vi kiểm thử:** `GET /index.php`, `GET /movies.php`
* **Kỹ thuật áp dụng:**
  - **Equivalence Partitioning (EP):** 
    - Thể loại hợp lệ: `Cartoon`, `Series`, `Movie`, `Cinema`
    - Thể loại không tồn tại: `UnknownGenre123`, `NonExistent`
    - Quốc gia hợp lệ: `Japan`, `USA`, `China`, `Korea`, `Vietnam`
    - Quốc gia không tồn tại: `Atlantis`
  - **Boundary Value Analysis (BVA):** Số lượng item hiển thị trong Slider (`limit=10`), danh mục phim mới (`limit=7`).
  - **SLA Constraint:** Thời gian phản hồi < 1500ms cho toàn bộ view HTML.
* **Ma trận truy vết (Traceability Matrix):**
  - `KAN-22-TC01` $\rightarrow$ `MOV_TC01`: Khám phá trang chủ, Top slider & các khối nội dung.
  - `KAN-22-TC02` $\rightarrow$ `MOV_TC02`: Lấy danh sách phim mặc định trên `/movies.php`.
  - `KAN-22-TC03` $\rightarrow$ `MOV_TC03`: Lọc danh mục phim theo Thể loại và Quốc gia.
  - `KAN-22-TC04` $\rightarrow$ `MOV_TC04`: Xử lý êm dịu khi lọc danh mục không có dữ liệu (Negative).


### 📌 Phân rã kiểm thử Task KAN-26: Thiết kế Test Cases tìm kiếm (Chính xác, Hoa/thường, Khoảng trắng, XSS, SQLi)
* **Jira Task Key:** `KAN-26`
* **Parent Story:** `KAN-25` (`[KAN-25] Kiểm thử API Tìm kiếm Phim, Data Chaining & An toàn Bảo mật (/search.php)`)
* **Phạm vi kiểm thử:** `GET /search.php?query={keyword}`
* **Kỹ thuật áp dụng:**
  - **Equivalence Partitioning (EP):** Từ khóa hợp lệ (`Doraemon`), từ khóa không khớp (`KhongTonTai9999`), chuỗi rỗng (`%20%20`).
  - **Case-Insensitive Analysis:** Từ khóa chữ hoa (`POKEMON`), chữ thường (`pokemon`), hỗn hợp (`PoKeMoN`).
  - **Security Fuzzing & Injection Testing:**
    - Reflected XSS: `<script>alert("XSS")</script>`, `<img src=x onerror=alert(1)>`
    - SQL Injection: `' OR '1'='1`, `'; DROP TABLE movies; --`, `admin'--`
  - **Data Chaining:** Trích xuất động ID phim (`activeMovieId`) từ kết quả tìm kiếm để chuyển tiếp sang luồng xem phim (`/watch.php?id={id}`).
* **Ma trận truy vết (Traceability Matrix):**
  - `KAN-26-TC01` $\rightarrow$ `MOV_TC05`: Tìm kiếm phim với từ khóa chính xác & kiểm tra link xem phim.
  - `KAN-26-TC02` $\rightarrow$ `MOV_TC06`: Tìm kiếm không phân biệt chữ hoa, chữ thường (Case-Insensitive).
  - `KAN-26-TC03` $\rightarrow$ `MOV_TC07`: Xử lý êm dịu khi tìm kiếm với khoảng trắng hoặc từ khóa không tồn tại.
  - `KAN-26-TC04` $\rightarrow$ `MOV_TC08`: Kiểm tra phòng chống tấn công Cross-Site Scripting (Reflected XSS).
  - `KAN-26-TC05` $\rightarrow$ `MOV_TC09`: Kiểm tra phòng chống tấn công SQL Injection trên ô tìm kiếm.


### 📌 Phân rã kiểm thử Task KAN-34: Thiết kế kịch bản kiểm thử API Contract, SLA và Edge cases
* **Jira Task Key:** `KAN-34`
* **Parent Story:** `KAN-33` (`[KAN-33] Kiểm thử RESTful API Ghi nhận Bộ đếm Lượt xem Phim (/api/view_counter.php)`)
* **Phạm vi kiểm thử:** `GET /api/view_counter.php?id={movie_id}`
* **Kỹ thuật áp dụng:**
  - **API Contract Testing:** Xác thực chuẩn RESTful response header `Content-Type: application/json; charset=utf-8`, mã trạng thái HTTP 200, và cấu trúc JSON Schema `{"success": boolean, "movie_id": integer}`.
  - **Performance / SLA Testing:** Giới hạn thời gian phản hồi (Response time) < 200ms đối với thao tác ghi nhận cập nhật bộ đếm (Atomic DB increment).
  - **Equivalence Partitioning & Boundary Value Analysis (Edge Cases):**
    - ID hợp lệ: `id = 52`, `id = 55` (Tồn tại trong CSDL $\rightarrow$ Success).
    - ID biên & Âm: `id = 0`, `id = -1` (Không hợp lệ $\rightarrow$ Trả về `{"success": false, "error": "ID không hợp lệ"}`).
    - ID kiểu dữ liệu sai (Type Coercion): `id = invalid_text`, `id = "'; DROP TABLE movies; --"` (Ép kiểu về 0, xử lý an toàn).
* **Ma trận truy vết (Traceability Matrix):**
  - `KAN-34-TC01` $\rightarrow$ `MOV_TC16`: Kiểm tra API Contract, JSON Schema và tăng bộ đếm `view_count`.
  - `KAN-34-TC02` $\rightarrow$ `MOV_TC17`: Kiểm tra xử lý biên với ID = 0, ID số âm và ID chứa ký tự lạ.


### 📌 Phân rã kiểm thử Task KAN-38: Thiết kế kịch bản kiểm tra RBAC thêm/sửa/xóa phim
* **Jira Task Key:** `KAN-38`
* **Parent Story:** `KAN-37` (`[KAN-37] Kiểm thử Phân quyền Quản trị & CRUD Nội dung Phim Admin (/add_movie.php, /manage_movies.php)`)
* **Phạm vi kiểm thử:** `GET/POST /add_movie.php`, `GET/POST /manage_movies.php`
* **Kỹ thuật áp dụng:**
  - **Role-Based Access Control (RBAC) Testing:**
    - Khách vãng lai (`Guest` - Không có session): Bị chặn 100%, redirect 302 về `admin_login.php`.
    - Người dùng thông thường (`User` - Có session nhưng `is_admin = 0`): Bị từ chối truy cập quyền quản trị, redirect về `admin_login.php`.
    - Quản trị viên (`Admin` - Có session và `is_admin = 1`): Được cấp quyền truy cập giao diện biểu mẫu thêm phim và danh sách quản lý phim.
  - **Multipart Form-Data Contract:** Mô phỏng payload thêm phim gồm các trường text (`title`, `description`, `genre`, `country`, `is_vip`) và file nhị phân (`thumbnail`, `video_file`).
  - **Deletion & Mutation Safety:** Kiểm tra tham số `delete_id` với Prepared Statement chống SQLi.
* **Ma trận phân quyền chi tiết (RBAC Matrix):**

| Role / Actor | GET /add_movie.php | POST /add_movie.php (Add) | GET /manage_movies.php | POST /manage_movies.php (Delete) |
| :--- | :---: | :---: | :---: | :---: |
| **Guest (Unauthenticated)** | ❌ 302 Redirect | ❌ 302 Redirect | ❌ 302 Redirect | ❌ 302 Redirect |
| **Normal User (is_admin = 0)** | ❌ 302 Redirect | ❌ 302 Redirect | ❌ 302 Redirect | ❌ 302 Redirect |
| **Admin (is_admin = 1)** | ✅ 200 OK | ✅ 200 OK (Thêm mới) | ✅ 200 OK | ✅ 200 OK (Xóa phim) |

| Test Case ID | Jira Task | Module / Endpoint | Tên Test Case (Title) | Điều kiện tiên quyết (Preconditions) | Các bước thực hiện (Steps) | Dữ liệu kiểm thử (Test Data) | Kết quả mong đợi (Expected Result) | Mức ưu tiên (Priority) | Loại test (Type) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **MOV_TC01** | `KAN-22` | `/index.php` | Khám phá trang chủ thành công, hiển thị đầy đủ danh mục phim | Web server Apache và MySQL đang chạy. | 1. Gửi request `GET /index.php`<br>2. Kiểm tra status code và thời gian phản hồi.<br>3. Kiểm tra các khối nội dung HTML. | Headers: `Accept: text/html` | - HTTP Status: `200 OK`<br>- Response time < 1500ms<br>- Chứa slider Top Movies (`#top-movies-slide`), danh mục Phim Mới, Series, Hoạt Hình<br>- Không chứa lỗi PHP Notice/Warning/Fatal. | **P1 (High)** | Positive |
| **MOV_TC02** | `KAN-22` | `/movies.php` | Lấy toàn bộ danh sách phim mặc định | CSDL `netfixvn` có dữ liệu bảng `movies`. | 1. Gửi request `GET /movies.php`<br>2. Kiểm tra danh sách hiển thị. | Headers: `Accept: text/html` | - HTTP Status: `200 OK`<br>- Hiển thị danh sách thẻ phim (`.movie-item`)<br>- Tên phim, ảnh poster thumbnail hiển thị đầy đủ. | **P1 (High)** | Positive |
| **MOV_TC03** | `KAN-22` | `/movies.php` | Lọc phim hợp lệ theo Thể loại và Quốc gia | Trong CSDL có phim thỏa mãn điều kiện. | 1. Gửi request `GET /movies.php?genre=Cartoon&country=Japan`<br>2. Kiểm tra danh sách phim trả về. | Query: `genre=Cartoon`, `country=Japan` | - HTTP Status: `200 OK`<br>- Danh sách trả về chỉ chứa phim hoạt hình Nhật Bản (ví dụ: *Doraemon*, *Pokemon*)<br>- Không lẫn phim thể loại khác. | **P1 (High)** | Positive |
| **MOV_TC04** | `KAN-22` | `/movies.php` | Lọc phim với thể loại không tồn tại | Web server đang chạy. | 1. Gửi request `GET /movies.php?genre=UnknownGenre123`<br>2. Kiểm tra phản hồi giao diện. | Query: `genre=UnknownGenre123` | - HTTP Status: `200 OK`<br>- Giao diện hiển thị thông báo "Không có phim trong danh mục này." (`.no-content`)<br>- Không crash ứng dụng, không lỗi cú pháp SQL. | **P2 (Medium)** | Negative |
| **MOV_TC05** | `KAN-26` | `/search.php` | Tìm kiếm phim với từ khóa chính xác | Phim "Doraemon" tồn tại trong CSDL. | 1. Gửi request `GET /search.php?query=Doraemon`<br>2. Kiểm tra kết quả tìm kiếm. | Query: `query=Doraemon` | - HTTP Status: `200 OK`<br>- Response chứa ít nhất 1 kết quả có tiêu đề chứa từ khóa "Doraemon"<br>- Link điều hướng trỏ đúng về `/watch.php?id={id}`. | **P1 (High)** | Positive |
| **MOV_TC06** | `KAN-26` | `/search.php` | Tìm kiếm không phân biệt chữ hoa, chữ thường | Phim "pokemon" tồn tại trong CSDL. | 1. Gửi request `GET /search.php?query=POKEMON`<br>2. So sánh kết quả với khi tìm `pokemon`. | Query: `query=POKEMON` | - HTTP Status: `200 OK`<br>- Trả về kết quả chính xác phim *Pokemon* (Case-insensitive matching hoạt động đúng). | **P2 (Medium)** | Positive |
| **MOV_TC07** | `KAN-26` | `/search.php` | Tìm kiếm với từ khóa là chuỗi rỗng hoặc khoảng trắng | Web server đang chạy. | 1. Gửi request `GET /search.php?query=%20%20`<br>2. Kiểm tra kết quả. | Query: `query=  ` | - HTTP Status: `200 OK`<br>- Không gây lỗi database<br>- Hiển thị toàn bộ danh sách hoặc thông báo yêu cầu nhập từ khóa tìm kiếm. | **P2 (Medium)** | Edge |
| **MOV_TC08** | `KAN-26` | `/search.php` | Kiểm tra phòng chống tấn công Cross-Site Scripting (Reflected XSS) | Web server đang chạy. | 1. Gửi request `GET /search.php?query=<script>alert('XSS')</script>`<br>2. Kiểm tra mã nguồn HTML trả về. | Query: `query=<script>alert('XSS')</script>` | - HTTP Status: `200 OK`<br>- Chuỗi payload được encode an toàn thành `&lt;script&gt;` hoặc loại bỏ thẻ độc hại<br>- Script không bị thực thi trên trình duyệt. | **P0 (Critical)** | Security |
| **MOV_TC09** | `KAN-26` | `/search.php` | Kiểm tra phòng chống tấn công SQL Injection trên ô tìm kiếm | Web server đang chạy. | 1. Gửi request `GET /search.php?query=Doraemon' OR '1'='1`<br>2. Kiểm tra phản hồi. | Query: `query=Doraemon' OR '1'='1` | - HTTP Status: `200 OK`<br>- Truy vấn được tham số hóa an toàn qua Prepared Statement<br>- Không lộ lỗi MySQL syntax, không dump toàn bộ database. | **P0 (Critical)** | Security |
| **MOV_TC10** | `KAN-30` | `/watch.php` | Xem chi tiết và phát luồng phim miễn phí thành công | Phim ID 52 có `is_vip = 0`. | 1. Gửi request `GET /watch.php?id=52`<br>2. Kiểm tra metadata phim và thẻ phát video. | Query: `id=52` | - HTTP Status: `200 OK`<br>- Hiển thị tên phim, mô tả, diễn viên, thời lượng<br>- Hiển thị thẻ `<video>` chứa đường dẫn `video_path` phát phim. | **P0 (Critical)** | Positive |
| **MOV_TC11** | `KAN-30` | `/watch.php` | Xem chi tiết phim bộ (Series) có danh sách các tập phim | Phim ID 56 (Vương Chiêu Quân) là Series có tập trong `episodes`. | 1. Gửi request `GET /watch.php?id=56`<br>2. Kiểm tra danh sách tập phim. | Query: `id=56` | - HTTP Status: `200 OK`<br>- Hiển thị danh sách các tập phim (Tập 1, Tập 2...)<br>- Có thể chọn tham số `&ep=1` để đổi tập phát. | **P1 (High)** | Positive |
| **MOV_TC12** | `KAN-30` | `/watch.php` | Xem phim với ID không tồn tại trong hệ thống | Không có phim nào mang ID `999999`. | 1. Gửi request `GET /watch.php?id=999999`<br>2. Kiểm tra phản hồi. | Query: `id=999999` | - HTTP Status: `200 OK`<br>- Hiển thị thông báo "Phim không tồn tại" hoặc điều hướng về trang chủ<br>- Không lộ exception trace CSDL. | **P1 (High)** | Negative |
| **MOV_TC13** | `KAN-30` | `/watch.php` | Xem phim với ID chứa ký tự chữ hoặc payload SQLi | Web server đang chạy. | 1. Gửi request `GET /watch.php?id=invalid_id_injection`<br>2. Kiểm tra phản hồi. | Query: `id=invalid_id_injection` | - HTTP Status: `200 OK`<br>- Hàm `(int)$_GET['id']` ép kiểu về `0`<br>- Hệ thống thông báo ID không hợp lệ, tuyệt đối an toàn. | **P0 (Critical)** | Security |
| **MOV_TC14** | `KAN-30` | `/watch.php` | Chặn truy cập xem phim VIP đối với khách vãng lai (Guest) | Phim ID 56 có thiết lập `is_vip = 1`. | 1. Không gửi Session đăng nhập.<br>2. Gửi request `GET /watch.php?id=56`.<br>3. Kiểm tra trình phát video. | Query: `id=56`, No Session Cookie | - Trình phát video bị ẩn/khóa<br>- Hiển thị cảnh báo yêu cầu nâng cấp tài khoản VIP hoặc link sang `pricing.php`. | **P0 (Critical)** | Security / Auth |
| **MOV_TC15** | `KAN-30` | `/watch.php` | Cho phép xem phim VIP khi đã đăng nhập tài khoản VIP hợp lệ | Tài khoản User có `is_vip = 1` và chưa hết hạn. | 1. Đăng nhập tài khoản VIP lấy Session Cookie.<br>2. Gửi request `GET /watch.php?id=56` kèm Cookie.<br>3. Kiểm tra quyền phát. | Cookie: `PHPSESSID={vip_user}` | - HTTP Status: `200 OK`<br>- Video player mở khóa, cho phép phát luồng video VIP. | **P0 (Critical)** | Positive (Auth) |
| **MOV_TC16** | `KAN-34` | `/api/view_counter.php` | Gọi RESTful API tăng lượt xem phim thành công | Phim ID 52 tồn tại trong CSDL. | 1. Gửi request `GET /api/view_counter.php?id=52`<br>2. Kiểm tra JSON response và bảng `movies`. | Query: `id=52`, Headers: `Accept: application/json` | - HTTP Status: `200 OK`<br>- Header `Content-Type: application/json`<br>- Response Body đúng contract: `{"success": true, "movie_id": 52}`<br>- CSDL trường `view_count` tăng thêm 1. | **P1 (High)** | Positive (API Contract) |
| **MOV_TC17** | `KAN-34` | `/api/view_counter.php` | Gọi API tăng lượt xem với ID bằng 0 hoặc số âm | Web server đang chạy. | 1. Gửi request `GET /api/view_counter.php?id=0`<br>2. Kiểm tra phản hồi. | Query: `id=0` | - HTTP Status: `200 OK`<br>- Không cập nhật CSDL, không crash service. | **P2 (Medium)** | Edge |
| **MOV_TC18** | `KAN-38` | `/add_movie.php` | Chặn tài khoản người dùng thường truy cập chức năng thêm phim | Tài khoản đăng nhập là User thường (`is_admin = 0`). | 1. Đăng nhập User thường.<br>2. Gửi request `POST /add_movie.php` kèm form dữ liệu phim. | Session: User thường | - Bị từ chối truy cập (Redirect về `index.php` hoặc báo lỗi không đủ quyền hạn). | **P1 (High)** | Security / Auth |

---

## 4. THIẾT KẾ MA TRẬN DECISION TABLE PHÂN QUYỀN TÀI KHOẢN XEM PHIM VIP (KAN-30)

* **Jira Task Key:** `KAN-30`
* **Mục tiêu:** Thiết lập ma trận quyết định kiểm thử bao phủ toàn bộ các tổ hợp logic phân quyền Gatekeeper giữa người dùng (Khách vãng lai, Người dùng thông thường, Thành viên VIP, VIP hết hạn) và phân loại nội dung phim (Miễn phí vs VIP Streaming).

### 4.1. Bảng Ma trận Quyết định Đầy đủ (Comprehensive Decision Table)

| Điều kiện / Trạng thái (Conditions) | Rule 1 (Free - Guest) | Rule 2 (Free - Normal User) | Rule 3 (VIP - Guest) | Rule 4 (VIP - Normal User) | Rule 5 (VIP - VIP Active) | Rule 6 (VIP - Expired VIP) |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Thuộc tính nội dung (`movie.is_vip` / `ep.is_vip`)** | 0 (Free) | 0 (Free) | 1 (VIP) | 1 (VIP) | 1 (VIP) | 1 (VIP) |
| **Xác thực phiên (`is_authenticated`)** | False | True | False | True | True | True |
| **Phân hạng tài khoản (`user.is_vip`)** | N/A | 0 (Normal) | N/A | 0 (Normal) | 1 (VIP) | 1 (VIP) |
| **Thời hạn gói VIP (`vip_expired_at >= NOW()`)** | N/A | N/A | N/A | N/A | True (Còn hạn) | False (Hết hạn) |
| **HÀNH ĐỘNG HỆ THỐNG (ACTIONS)** | | | | | | |
| **Trình phát Video (Play Video)** | ✅ Cho phép | ✅ Cho phép | ❌ Chặn | ❌ Chặn | ✅ Cho phép | ❌ Chặn |
| **Hiển thị Huy hiệu VIP (`.badge-vip`)** | Không | Không | Có | Có | Có | Có |
| **Khóa tập phim (`.locked`) & Icon Khóa (🔒)** | Không | Không | Có | Có | Không | Có |
| **Điều hướng nâng cấp gói (`data-href="pricing.php"`)** | Không | Không | Có (Đăng nhập/Gói) | Có (Nâng cấp VIP) | Không | Có (Gia hạn gói) |
| **Truy vết Test Case ID** | `MOV_TC10` | `MOV_TC10` | `MOV_TC14` | `MOV_TC14` | `MOV_TC15` | `MOV_TC14` |

---

## 5. HƯỚNG DẪN THỰC THI KIỂM THỬ TỰ ĐỘNG (Automation Guidelines)

### 5.1. Thực thi với Postman App
1. Khởi động XAMPP (Apache + MySQL) và kiểm tra website hoạt động tại `http://localhost/netfix2`.
2. Mở Postman, nhấn **Import**, chọn file `docs/Netfix2_MOV_API_Automation.postman_collection.json`.
3. Kiểm tra biến `baseUrl` mặc định là `http://localhost/netfix2`.
4. Nhấn chuột phải vào thư mục **02. Movie & Discovery (MOV)** -> Chọn **Run folder** -> Nhấn **Run Netfix2 - MOV Automation**.

### 5.2. Thực thi tự động qua dòng lệnh (Newman CLI)
Dùng cho kiểm thử hồi quy tự động (CI/CD Pipeline):
```bash
# Cài đặt Newman nếu chưa có
npm install -g newman newman-reporter-htmlextra

# Chạy toàn bộ collection và xuất báo cáo HTML
newman run "docs/Netfix2_MOV_API_Automation.postman_collection.json" \
  --env-var "baseUrl=http://localhost/netfix2" \
  --reporters cli,htmlextra \
  --reporter-htmlextra-export "docs/newman_report_mov.html"
```
