# 🎯 BỘ TÀI LIỆU TASK JIRA CHUẨN — PHÂN HỆ MOVIE & DISCOVERY (MOV)
## DỰ ÁN: NETFIX2 STREAMING PLATFORM

> **Tài liệu hướng dẫn & Nội dung chi tiết các Task Jira (Jira Work Breakdown Structure)**  
> **Dự án:** Netfix2 — Website Xem Phim Trực Tuyến  
> **Module phụ trách:** Movie & Discovery (MOV)  
> **QA Lead / Senior QA Engineer:** Col255 QA Team  
> **Format:** Chuẩn Jira Markdown / Rich Text (hỗ trợ copy-paste trực tiếp lên Jira hoặc import file CSV đính kèm).  

---

## 1. CÂY PHÂN RÃ CÔNG VIỆC TRÊN JIRA (Jira WBS)

```text
[EPIC] MOV-E01: Kiểm thử API & Chức năng Phân hệ Movie & Discovery (MOV) Platform
 ├── [STORY] MOV-S01: Kiểm thử API Khám phá Trang chủ & Lọc Danh mục Phim (/index.php, /movies.php)
 │     ├── [Sub-task] MOV-S01-1: Thiết kế Test Cases cho Discovery Homepage & Catalog Filter
 │     ├── [Sub-task] MOV-S01-2: Xây dựng & Tự động hóa Postman Collection cho Discovery & Filter
 │     └── [Sub-task] MOV-S01-3: Thực thi kiểm thử, đo lường SLA phản hồi và log bug phát sinh
 ├── [STORY] MOV-S02: Kiểm thử API Tìm kiếm Phim, Data Chaining & An toàn Bảo mật (/search.php)
 │     ├── [Sub-task] MOV-S02-1: Thiết kế Test Cases tìm kiếm (Chính xác, Hoa/thường, Khoảng trắng, XSS, SQLi)
 │     ├── [Sub-task] MOV-S02-2: Xây dựng Postman Script trích xuất động activeMovieId & Fuzzing bảo mật
 │     └── [Sub-task] MOV-S02-3: Thực thi automated regression, xác thực cơ chế sanitize XSS/SQLi
 ├── [STORY] MOV-S03: Kiểm thử Metadata Chi tiết Phim, Quản lý Tập & Phân quyền VIP Streaming (/watch.php)
 │     ├── [Sub-task] MOV-S03-1: Thiết kế Ma trận Decision Table phân quyền tài khoản xem phim VIP
 │     ├── [Sub-task] MOV-S03-2: Xây dựng kịch bản Postman kiểm thử phát video, chuyển tập và Gatekeeper VIP
 │     └── [Sub-task] MOV-S03-3: Thực thi kiểm thử xâm nhập bypass link video và nghiệm thu chất lượng
 ├── [STORY] MOV-S04: Kiểm thử RESTful API Ghi nhận Bộ đếm Lượt xem Phim (/api/view_counter.php)
 │     ├── [Sub-task] MOV-S04-1: Thiết kế kịch bản kiểm thử API Contract, SLA và Edge cases
 │     ├── [Sub-task] MOV-S04-2: Viết Postman assertions kiểm tra JSON Schema, Content-Type và cập nhật CSDL
 │     └── [Sub-task] MOV-S04-3: Thực thi kiểm thử tải/spam lượt xem và xác thực tính toàn vẹn dữ liệu
 └── [STORY] MOV-S05: Kiểm thử Phân quyền Quản trị & CRUD Nội dung Phim Admin (/add_movie.php, /manage_movies.php)
       ├── [Sub-task] MOV-S05-1: Thiết kế kịch bản kiểm tra RBAC (Role-based access control) thêm/sửa/xóa phim
       ├── [Sub-task] MOV-S05-2: Viết Postman test script mô phỏng upload multipart form-data phim và tập phim
       └── [Sub-task] MOV-S05-3: Thực thi kiểm thử bảo mật phân quyền User thường không được thao tác trang Admin
```

---

## 2. NỘI DUNG CHI TIẾT TỪNG ISSUE TRÊN JIRA (Copy-paste Ready)

---

### 🌟 EPIC: MOV-E01
* **Issue Type:** `Epic`
* **Epic Name:** `Movie & Discovery API Testing`
* **Summary:** `[EPIC] Kiểm thử toàn diện API & Chức năng Phân hệ Movie & Discovery (MOV)`
* **Component:** `Movie & Discovery`
* **Priority:** `High`
* **Labels:** `qa`, `api-testing`, `MOV`, `epic`, `automation`
* **Description:**
```markdown
h2. 🎯 Mục tiêu Epic (Epic Objective)
Thực hiện kiểm thử toàn diện (End-to-End API Testing & Functional Validation) cho toàn bộ phân hệ Khám phá & Quản lý Phim (Movie & Discovery - MOV) của nền tảng Netfix2. Đảm bảo trải nghiệm duyệt phim mượt mà, bộ lọc danh mục và tìm kiếm chính xác, bảo mật chặt chẽ luồng phát phim bản quyền/VIP và kiểm soát tính toàn vẹn dữ liệu lượt xem.

h2. 📌 Phạm vi kiểm thử (Scope)
* Phân hệ Khám phá người xem: `/index.php`, `/movies.php`, `/search.php`, `/watch.php`.
* Phân hệ API RESTful dịch vụ: `/api/view_counter.php`.
* Phân hệ Quản trị nội dung (Admin): `/add_movie.php`, `/manage_movies.php`, `/edit_movie.php`.

h2. 📊 Mục tiêu chất lượng (Quality Goals)
* 100% Test Case được đặc tả và tự động hóa qua Postman Collection / Newman CLI.
* Thời gian phản hồi (Response Time SLA) < 1500ms đối với các trang duyệt danh mục và < 500ms đối với REST API JSON.
* 0 Defect nghiêm trọng (Blocker/Critical) liên quan đến SQL Injection, XSS hoặc Bypass VIP Streaming chưa được xử lý.
```

---

### 📖 STORY 1: MOV-S01
* **Issue Type:** `Story`
* **Epic Link:** `MOV-E01`
* **Summary:** `[MOV-S01] Kiểm thử API Khám phá Trang chủ & Lọc Danh mục Phim (/index.php, /movies.php)`
* **Story Points:** `3`
* **Priority:** `High`
* **Component:** `Movie & Discovery`
* **Labels:** `qa`, `api-testing`, `MOV`, `postman`, `discovery`
* **Description:**
```markdown
h2. 🎯 Mục tiêu (Objective)
Kiểm thử chức năng khám phá nội dung trên trang chủ (`/index.php`) và bộ lọc danh mục (`/movies.php`), đảm bảo các khối nội dung phim nổi bật (Top slider), phân loại thể loại và quốc gia hiển thị chính xác theo dữ liệu CSDL.

h2. 📌 Phạm vi (Scope)
* Endpoints:
  - `GET /index.php`
  - `GET /movies.php`
  - `GET /movies.php?genre={genre}&country={country}`
* Query Parameters: `genre`, `sub_genre`, `country`, `year`

h2. ✅ Tiêu chí chấp nhận (Acceptance Criteria)
* [ ] HTTP Status Code trả về luôn là `200 OK`.
* [ ] Thời gian phản hồi trang chủ < 1500ms dưới điều kiện mạng thông thường.
* [ ] Trang chủ chứa đầy đủ khối `#top-movies-slide`, khối Phim mới, Phim bộ và Hoạt hình.
* [ ] Khi lọc `genre=Cartoon` & `country=Japan`, danh sách trả về chỉ chứa phim hoạt hình Nhật Bản (ví dụ Doraemon, Pokemon).
* [ ] Khi lọc với thể loại không tồn tại (`genre=Unknown123`), hệ thống xử lý êm dịu, hiển thị danh sách rỗng, không văng lỗi PHP Fatal Error / MySQL syntax.

h2. 📦 Dữ liệu kiểm thử (Test Data)
* Hợp lệ: `genre=Cartoon`, `country=Japan`, `country=USA`
* Giá trị biên/không tồn tại: `genre=NonExistent123`, `country=Atlantis`

h2. 🏁 Định nghĩa hoàn thành (Definition of Done - DoD)
* [ ] Test cases MOV_TC01 - MOV_TC04 được viết đầy đủ vào tài liệu đặc tả.
* [ ] Đã xây dựng và verify thành công Postman Scripts trong folder `01. Discovery & Catalog`.
* [ ] Toàn bộ assertions pass 100% trên môi trường local.
* [ ] Link tài liệu đính kèm: [TEST_CASES_MOV.md](file:///c:/Users/DAT/OneDrive%20-%20ut.edu.vn/Documents/Desktop/New%20folder/docs/TEST_CASES_MOV.md)
```

#### Các Sub-tasks thuộc Story MOV-S01:
1. **MOV-S01-1**: `Thiết kế Test Cases cho Discovery Homepage & Catalog Filter (MOV_TC01 - MOV_TC04)` *(Estimate: 2h | Assignee: QA)*
2. **MOV-S01-2**: `Xây dựng & Tự động hóa Postman Collection cho Discovery & Filter` *(Estimate: 3h | Assignee: QA)*
3. **MOV-S01-3**: `Thực thi kiểm thử, đo lường SLA phản hồi và log bug phát sinh` *(Estimate: 2h | Assignee: QA)*

---

### 📖 STORY 2: MOV-S02
* **Issue Type:** `Story`
* **Epic Link:** `MOV-E01`
* **Summary:** `[MOV-S02] Kiểm thử API Tìm kiếm Phim, Data Chaining & An toàn Bảo mật (/search.php)`
* **Story Points:** `5`
* **Priority:** `High`
* **Component:** `Movie & Discovery`
* **Labels:** `qa`, `api-testing`, `MOV`, `postman`, `security`, `search`
* **Description:**
```markdown
h2. 🎯 Mục tiêu (Objective)
Kiểm thử toàn diện chức năng tìm kiếm phim (`/search.php`), khả năng xử lý từ khóa, trích xuất dữ liệu động (Data Chaining cho các bước test kế tiếp) và đánh giá độ an toàn trước các cuộc tấn công Reflected XSS và SQL Injection.

h2. 📌 Phạm vi (Scope)
* Endpoint: `GET /search.php`
* Parameters: `query` (từ khóa tìm kiếm), `genre`, `country`
* Kỹ thuật: Phân vùng tương đương, Boundary Testing, Fuzzing Input

h2. ✅ Tiêu chí chấp nhận (Acceptance Criteria)
* [ ] HTTP Status Code trả về `200 OK`.
* [ ] Tìm kiếm chính xác với từ khóa hợp lệ (`query=Doraemon`).
* [ ] Tìm kiếm không phân biệt chữ hoa, chữ thường (`query=POKEMON` trả về cùng kết quả với `pokemon`).
* [ ] Với từ khóa khoảng trắng (`query=%20%20`), giao diện hiển thị thông báo yêu cầu nhập từ khóa, không bị lỗi backend.
* [ ] **Bảo mật XSS (P0):** Truyền payload `<script>alert('XSS')</script>`, response HTML phải được encode (ví dụ `&lt;script&gt;`) hoặc bóc tách, tuyệt đối không xuất hiện thẻ thực thi script thô.
* [ ] **Bảo mật SQLi (P0):** Truyền payload `Doraemon' OR '1'='1`, hệ thống áp dụng Prepared Statements an toàn, không lộ lỗi MySQL syntax và không dump toàn bộ database.
* [ ] **Data Chaining:** Kịch bản Postman tự động phân tích HTML kết quả và lưu ID phim tìm được vào biến môi trường `activeMovieId`.

h2. 📦 Dữ liệu kiểm thử (Test Data)
* Phim hợp lệ: `query=Doraemon`, `query=POKEMON`
* Ký tự biên/khoảng trắng: `query=  `
* Payload bảo mật: `query=<script>alert('XSS')</script>`, `query=Doraemon' OR '1'='1`

h2. 🏁 Định nghĩa hoàn thành (Definition of Done - DoD)
* [ ] Hoàn thành Test Cases MOV_TC05 - MOV_TC09.
* [ ] Test scripts Postman chạy đạt 100% assertions không có lỗi cú pháp.
* [ ] Nếu phát hiện lỗi XSS hoặc SQLi, tạo ngay Jira Bug liên kết mức độ Critical/High.
```

#### Các Sub-tasks thuộc Story MOV-S02:
1. **MOV-S02-1**: `Thiết kế Test Cases tìm kiếm (Chính xác, Hoa/thường, Khoảng trắng, XSS, SQLi)` *(Estimate: 3h | Assignee: QA)*
2. **MOV-S02-2**: `Xây dựng Postman Script trích xuất động activeMovieId & Fuzzing bảo mật` *(Estimate: 4h | Assignee: QA)*
3. **MOV-S02-3**: `Thực thi automated regression, xác thực cơ chế sanitize XSS/SQLi` *(Estimate: 2h | Assignee: QA)*

---

### 📖 STORY 3: MOV-S03
* **Issue Type:** `Story`
* **Epic Link:** `MOV-E01`
* **Summary:** `[MOV-S03] Kiểm thử Metadata Chi tiết Phim, Quản lý Tập & Phân quyền VIP Streaming (/watch.php)`
* **Story Points:** `5`
* **Priority:** `Critical`
* **Component:** `Movie & Discovery`
* **Labels:** `qa`, `api-testing`, `MOV`, `postman`, `streaming`, `vip-gatekeeper`
* **Description:**
```markdown
h2. 🎯 Mục tiêu (Objective)
Kiểm thử trang chi tiết phát phim (`/watch.php`), hiển thị thông tin phim/tập phim, chuyển đổi tập cho Phim bộ (`Series`) và kiểm soát chặt chẽ cơ chế phân quyền (Gatekeeper) đối với các phim yêu cầu tài khoản VIP (`is_vip = 1`).

h2. 📌 Phạm vi (Scope)
* Endpoint: `GET /watch.php`
* Parameters: `id` (Movie ID), `ep` (Tập phim)
* Cơ chế xác thực: Session Cookie (`PHPSESSID`)

h2. ✅ Tiêu chí chấp nhận (Acceptance Criteria)
* [ ] Phim miễn phí (`is_vip = 0`): Khách vãng lai và người dùng thông thường đều truy cập và hiển thị player phát video thành công.
* [ ] Phim bộ (`genre = Series`): Hiển thị đầy đủ danh sách tập phim (`episodes`) và tham số `&ep={number}` chuyển đúng tập phát.
* [ ] ID phim không tồn tại (`id=999999`): Xử lý báo lỗi thân thiện hoặc redirect, không văng exception CSDL.
* [ ] ID chứa ký tự lạ (`id=abc'OR'1'='1`): Hệ thống ép kiểu số `(int)` an toàn, không bị tấn công SQL Injection.
* [ ] **Phân quyền Phim VIP (P0):**
  - Khách chưa đăng nhập: Trình phát video bị ẩn/khóa, hiển thị thông báo yêu cầu đăng nhập.
  - Người dùng thường (chưa mua VIP): Hiển thị cảnh báo yêu cầu mua gói VIP hoặc điều hướng sang `/pricing.php`.
  - Người dùng VIP còn hạn: Trình phát video mở khóa và phát luồng bình thường.

h2. 📦 Dữ liệu kiểm thử (Test Data)
* Phim Free: `id=52` (Kẻ cắp mặt trăng), `id=55` (Doraemon)
* Phim Series / VIP: `id=56` (Vương Chiêu Quân)
* Phim biên / Lỗi: `id=999999`, `id=invalid_id_injection`
* Tài khoản test: `tuan@gmail.com / 123456` (User thường), `admin@gmail.com / admin123` (Admin/VIP)

h2. 🏁 Định nghĩa hoàn thành (Definition of Done - DoD)
* [ ] Hoàn thành Test Cases MOV_TC10 - MOV_TC15.
* [ ] Kiểm tra xác nhận không có lỗ hổng rò rỉ URL file video `.mp4` trong mã nguồn HTML khi chưa đủ quyền VIP.
* [ ] Toàn bộ kịch bản Postman trong folder `03. Movie Detail & Streaming` chạy pass.
```

#### Các Sub-tasks thuộc Story MOV-S03:
1. **MOV-S03-1**: `Thiết kế Ma trận Decision Table phân quyền tài khoản xem phim VIP` *(Estimate: 3h | Assignee: QA)*
2. **MOV-S03-2**: `Xây dựng kịch bản Postman kiểm thử phát video, chuyển tập và Gatekeeper VIP` *(Estimate: 4h | Assignee: QA)*
3. **MOV-S03-3**: `Thực thi kiểm thử xâm nhập bypass link video và nghiệm thu chất lượng` *(Estimate: 3h | Assignee: QA)*

---

### 📖 STORY 4: MOV-S04
* **Issue Type:** `Story`
* **Epic Link:** `MOV-E01`
* **Summary:** `[MOV-S04] Kiểm thử RESTful API Ghi nhận Bộ đếm Lượt xem Phim (/api/view_counter.php)`
* **Story Points:** `2`
* **Priority:** `High`
* **Component:** `Movie & Discovery`
* **Labels:** `qa`, `api-testing`, `MOV`, `postman`, `rest-api`, `metrics`
* **Description:**
```markdown
h2. 🎯 Mục tiêu (Objective)
Kiểm thử dịch vụ API bất đồng bộ tăng lượt xem phim (`/api/view_counter.php`), đảm bảo tuân thủ chuẩn RESTful API, định dạng JSON Contract, SLA thời gian phản hồi cao và độ ổn định khi nhận dữ liệu biên.

h2. 📌 Phạm vi (Scope)
* Endpoint: `GET /api/view_counter.php`
* Parameters: `id` (Movie ID)
* Header: `Accept: application/json`

h2. ✅ Tiêu chí chấp nhận (Acceptance Criteria)
* [ ] HTTP Status Code trả về `200 OK`.
* [ ] Thời gian phản hồi (Response Time SLA) < 500ms.
* [ ] Header `Content-Type` chuẩn xác: `application/json; charset=utf-8`.
* [ ] Response Body tuân thủ đúng Schema:
  ```json
  {
    "success": true,
    "movie_id": 55
  }
  ```
* [ ] Giá trị `movie_id` trả về khớp chính xác với ID được gửi lên.
* [ ] Trường `view_count` trong bảng `movies` của database `netfixvn` được cộng thêm đúng 1 đơn vị.
* [ ] Với ID không hợp lệ (`id=0` hoặc số âm): Trả về JSON thông báo lỗi an toàn, không cập nhật database, không gây lỗi 500.

h2. 📦 Dữ liệu kiểm thử (Test Data)
* ID hợp lệ: `id=52`, `id=55`
* ID biên: `id=0`, `id=-10`, `id=text`

h2. 🏁 Định nghĩa hoàn thành (Definition of Done - DoD)
* [ ] Hoàn thành Test Cases MOV_TC16, MOV_TC17.
* [ ] Postman script có đầy đủ assertion kiểm tra JSON schema và SLA.
* [ ] Đã xác thực dữ liệu tăng thực tế trong bảng MySQL `movies`.
```

#### Các Sub-tasks thuộc Story MOV-S04:
1. **MOV-S04-1**: `Thiết kế kịch bản kiểm thử API Contract, SLA và Edge cases` *(Estimate: 1.5h | Assignee: QA)*
2. **MOV-S04-2**: `Viết Postman assertions kiểm tra JSON Schema, Content-Type và CSDL` *(Estimate: 2h | Assignee: QA)*
3. **MOV-S04-3**: `Thực thi kiểm thử tải/spam lượt xem và xác thực tính toàn vẹn dữ liệu` *(Estimate: 1.5h | Assignee: QA)*

---

### 📖 STORY 5: MOV-S05
* **Issue Type:** `Story`
* **Epic Link:** `MOV-E01`
* **Summary:** `[MOV-S05] Kiểm thử Phân quyền Quản trị & CRUD Nội dung Phim Admin (/add_movie.php, /manage_movies.php)`
* **Story Points:** `3`
* **Priority:** `Medium`
* **Component:** `Movie & Discovery`
* **Labels:** `qa`, `api-testing`, `MOV`, `admin`, `rbac`
* **Description:**
```markdown
h2. 🎯 Mục tiêu (Objective)
Kiểm thử bảo mật kiểm soát truy cập dựa trên vai trò (Role-Based Access Control - RBAC) đối với các trang quản trị phim của Admin, ngăn chặn người dùng thông thường hoặc khách truy cập can thiệp vào kho phim.

h2. 📌 Phạm vi (Scope)
* Endpoints:
  - `POST /add_movie.php` (Thêm phim)
  - `GET /manage_movies.php` (Danh sách & Xóa phim)
  - `POST /edit_movie.php?id={id}` (Chỉnh sửa phim)

h2. ✅ Tiêu chí chấp nhận (Acceptance Criteria)
* [ ] **Bảo mật phân quyền (P0):** Khách chưa đăng nhập hoặc User thường (`is_admin = 0`) gửi request tới các trang này sẽ bị từ chối truy cập (Redirect về `index.php` hoặc báo lỗi quyền hạn), không cho phép tạo/sửa/xóa phim.
* [ ] **Admin hợp lệ (`is_admin = 1`):** Thực hiện thêm phim thành công, CSDL ghi nhận đầy đủ các thông tin: tiêu đề, thể loại, quốc gia, thời lượng, cờ VIP, đường dẫn video và danh sách tập phim.
* [ ] Khi xóa phim từ `manage_movies.php`, CSDL thực thi cascade xóa các bản ghi liên quan trong bảng `episodes` và `comments` (ON DELETE CASCADE hoạt động đúng).

h2. 🏁 Định nghĩa hoàn thành (Definition of Done - DoD)
* [ ] Hoàn thành Test Case MOV_TC18.
* [ ] Bộ test Postman bao gồm kịch bản Negative Auth test (gửi request với Session User thường).
```

#### Các Sub-tasks thuộc Story MOV-S05:
1. **MOV-S05-1**: `Thiết kế kịch bản kiểm tra RBAC thêm/sửa/xóa phim` *(Estimate: 2h | Assignee: QA)*
2. **MOV-S05-2**: `Viết Postman test script mô phỏng upload multipart form-data phim` *(Estimate: 2.5h | Assignee: QA)*
3. **MOV-S05-3**: `Thực thi kiểm thử bảo mật phân quyền User thường không được vào Admin` *(Estimate: 1.5h | Assignee: QA)*

---

## 3. QUY TRÌNH COMMIT & BÁO CÁO TIẾN ĐỘ CHUẨN QA

### 3.1. Quy ước Commit Message (Conventional Commits gắn Jira Key)

Cú pháp bắt buộc: `<type>(<Jira-Key>): <mô tả ngắn bằng tiếng Anh/Việt>`

* **Thêm mới test script:**  
  `test(MOV-S02): add postman test scripts for movie search and xss fuzzing`
* **Sửa đổi assertion / data:**  
  `fix(MOV-S04): add application/json content-type header validation for view_counter`
* **Cập nhật tài liệu / matrix:**  
  `docs(MOV-S01): update test case matrix for discovery catalog filter`
* **Tái cấu trúc / Refactor kịch bản:**  
  `refactor(MOV-S03): optimize dynamic data chaining for activeMovieId in streaming player`

---

### 3.2. Quy trình chuyển trạng thái trên Jira Board (Workflow)

```text
[To Do] ──► [In Progress] ──► [In Review] ──► [Done]
  │               ▲                │
  │               └─── [Reopen] ───┘
```

1. **To Do $\rightarrow$ In Progress:**
   - Kéo task khi bắt đầu viết test case hoặc code script.
   - Bắt đầu tính giờ Log Work (Time Tracking).
2. **In Progress $\rightarrow$ In Review:**
   - Hoàn thành viết kịch bản, đã chạy test thử đạt yêu cầu.
   - Đính kèm file test collection / export báo cáo Newman vào task.
   - Gán Assignee cho QA Lead để Review kịch bản.
3. **In Review $\rightarrow$ Done:**
   - QA Lead duyệt tiêu chuẩn nghiệm thu (Acceptance Criteria & DoD đạt 100%).
   - Merge code test vào nhánh `main` hoặc `week2`.

---

### 3.3. Template Comment cập nhật tiến độ hàng ngày (Daily Worklog)

Dán mẫu này vào tab **Comments** của Story/Task mỗi ngày:

```text
📅 [DAILY STATUS REPORT - 25/10/2026]
👤 Assignee: Senior QA Engineer | Task: MOV-S02 (Search API Testing)

1. ✅ Việc đã hoàn thành hôm nay:
   - Viết và hoàn thiện 5/5 Test Cases (MOV_TC05 - MOV_TC09).
   - Tự động hóa thành công kịch bản Data Chaining trích xuất activeMovieId.
   - Xác thực an toàn trước cuộc tấn công Reflected XSS và SQL Injection.

2. ⏳ Việc đang thực hiện:
   - Viết assertion kiểm tra định dạng phân trang kết quả tìm kiếm.

3. ⚠️ Khó khăn / Vướng mắc (Blockers):
   - Không có blocker. Môi trường XAMPP local hoạt động ổn định.

4. 🎯 Kế hoạch ngày mai:
   - Chuyển sang thực hiện Story MOV-S03 (Phân quyền VIP Streaming).
   - Chạy kiểm thử hồi quy Newman CLI.
```

---

### 3.4. Template Báo cáo tuần (Weekly Status Report) gửi QA Lead / PM

```text
📊 [WEEKLY QA STATUS REPORT - WEEK 2]
🔹 Phân hệ: Movie & Discovery (MOV) — Hệ thống Netfix2
🔹 Người lập: Senior QA Engineer | Ngày: 25/10/2026

1. 📌 Tổng quan tiến độ (Progress Overview):
   - Tổng Story Points đã cam kết: 18 SP
   - Đã hoàn thành (Done): 15 SP (83.3%)
   - Đang thực hiện (In Progress): 3 SP (16.7%)

2. 🧪 Chỉ số thực thi kiểm thử (Testing Metrics):
   - Tổng số Test Cases: 18 TCs
   - Đã thực thi (Executed): 18 TCs (100%)
   - Pass: 18 / 18 (100% sau khi Dev bổ sung Header Content-Type cho view_counter)
   - Fail: 0
   - Automated Coverage: 100% (qua Postman Collection & Newman CLI)
   - Thời gian chạy toàn bộ Suite: 1.77 giây

3. 🐛 Thống kê lỗi (Defect Summary):
   - Đã phát hiện: 1 Bug Minor (Thiếu header application/json tại view_counter.php) -> Đã fix & verify.
   - Bug tồn đọng (Open Bugs): 0

4. 🚀 Kế hoạch tuần tới:
   - Nghiệm thu dứt điểm Story MOV-S05 (Admin CRUD).
   - Tích hợp Newman Collection Runner vào GitHub Actions Workflow (.github/workflows/ci-cd-pipeline.yml).
```
