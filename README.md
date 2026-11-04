# 🎬 Netfix2 — Hệ Thống Xem Phim Trực Tuyến & Dự Án Kiểm Thử Phần Mềm (QA Testing)

Netfix2 là nền tảng xem phim trực tuyến viết bằng **PHP & MySQL/MariaDB**, phục vụ cho khách vãng lai, người dùng thành viên (Thường / VIP) và Quản trị viên (Admin CMS). Repository này bao gồm toàn bộ mã nguồn ứng dụng (`netfix2/`), cơ sở dữ liệu mẫu (`database/netfixvn.sql`), tài liệu đặc tả & thiết kế kịch bản kiểm thử (`docs/`), bộ tự động hóa API **Postman** (`Postman/`), bộ kiểm thử giao diện E2E **CodeceptJS + Playwright** (`tests/`) và đường ống **CI/CD GitHub Actions** tích hợp **SonarQube, JetBrains Qodana & Docker Hub**.

> ⚠️ Các trang `.php` cần chạy qua web server có hỗ trợ PHP (XAMPP hoặc Docker). Không mở trực tiếp file PHP bằng trình duyệt hoặc VS Code.

🔗 **Liên kết Quản lý Dự án & Báo cáo:**
- 📋 **Jira Project Board (QA Testing 002):** [https://lochnt0228.atlassian.net/jira/software/projects/KAN/boards/2](https://lochnt0228.atlassian.net/jira/software/projects/KAN/boards/2)
- 📑 **Danh sách Jira Tasks (JQL):** [Xem toàn bộ danh sách công việc trên Jira](https://lochnt0228.atlassian.net/jira/software/projects/KAN/list?jql=project+%3D+KAN+ORDER+BY+%22cf%5B10019%5D%22+ASC)

---

## 🛠️ Công Nghệ & Công Cụ Kiểm Thử Sử Dụng

- 🐘 **Backend & Database:** PHP 8.x (`mysqli`), MySQL / MariaDB (`utf8mb4_unicode_ci`)
- 🌐 **Frontend:** HTML5, CSS3, JavaScript
- <img src="https://cdn.simpleicons.org/xampp/F37623" alt="XAMPP" width="18"> **XAMPP / Docker:** Khởi chạy môi trường Web Server Apache & MySQL cục bộ hoặc container hóa
- <img src="https://cdn.simpleicons.org/postman/FF6C37" alt="Postman" width="18"> **Postman & Newman:** Kiểm thử tự động API & HTTP Endpoints (Black-Box, EP, BVA, Security)
- 🎭 **CodeceptJS + Playwright (Chromium):** Kiểm thử động End-to-End (E2E UI & Functional Testing)
- 🐍 **Python Automated Test Runner (`urllib` + `csv`):** Đọc kịch bản từ CSV thiết kế, tự động bắn request và xuất báo cáo kết quả thực thi CSV
- 🔍 **SonarQube Cloud & JetBrains Qodana:** Kiểm thử tĩnh (Static White-Box Analysis) quét lỗi cú pháp, lỗ hổng bảo mật OWASP và Code Smells
- ⚙️ **GitHub Actions CI/CD & Docker Hub:** Tự động hóa quy trình Kiểm thử Tĩnh $\rightarrow$ Kiểm thử Động $\rightarrow$ Đóng gói Docker Image

---

## ✨ Chức Năng Chính & Phạm Vi Kiểm Thử Hệ Thống

Hệ thống bao gồm **5 nhóm chức năng cốt lõi**, được kiểm thử chéo và phối hợp thực hiện bởi cả nhóm thông qua các kỹ thuật **Phân vùng tương đương (Equivalence Partitioning - EP)**, **Phân tích giá trị biên (Boundary Value Analysis - BVA)**, **Bảng quyết định (Decision Table / RBAC)** và **Kiểm thử bảo mật (SQL Injection, XSS)**:

- 👤 **Xác thực & Tài khoản (`AUTH`):** Đăng ký, đăng nhập người dùng/quản trị viên, đăng xuất, quản lý hồ sơ cá nhân, đổi mật khẩu và phân quyền truy cập (`/register.php`, `/login.php`, `/admin_login.php`, `/logout.php`, `/profile.php`, `/edit_profile.php`, `/change_password.php`).
- 🍿 **Khám phá, Tìm kiếm & Xem phim (`MOV`):** Duyệt trang chủ, lọc danh sách phim theo thể loại/quốc gia, tìm kiếm từ khóa và phát video theo tập (`/index.php`, `/movies.php`, `/search.php`, `/watch.php`).
- 💬 **Tương tác & Trải nghiệm người dùng (`INT`):** API cập nhật bộ đếm lượt xem, gửi bình luận phim, kiểm duyệt bình luận và theo dõi lịch sử xem phim (`/api/view_counter.php`, `/watch.php`, `/comments.php`, `/watch_history.php`).
- 💳 **Gói VIP & Thanh toán (`VIP`):** Hiển thị bảng giá gói VIP, thanh toán qua cổng MoMo hoặc chuyển khoản ngân hàng, xử lý callback kết quả giao dịch và theo dõi đơn hàng (`/pricing.php`, `/checkout.php`, `/process_payment.php`, `/payment_return.php`, `/orders.php`).
- 🛡️ **Quản trị Hệ thống CMS (`ADM`):** Thêm/sửa/xóa phim, quản lý danh sách phim, duyệt đơn hàng VIP, quản lý khách hàng và thống kê doanh thu (`/add_movie.php`, `/manage_movies.php`, `/orders.php`, `/customers.php`, `/statistics.php`).

---

## 📂 Cấu Trúc Thư Mục Dự Án

```text
QA-netfilxfake/
├── .github/workflows/
│   └── ci-cd-pipeline.yml            # Pipeline CI/CD: SonarQube + Qodana + CodeceptJS + Docker Hub
├── database/
│   └── netfixvn.sql                  # File SQL khởi tạo CSDL và dữ liệu mẫu chuẩn
├── docs/                             # Tài liệu SRS, Thiết kế Test Case, Python Runner & Báo cáo CSV
│   ├── srs.docx                      # Tài liệu Đặc tả Yêu cầu Phần mềm (SRS - IEEE 830)
│   ├── BANG_TONG_HOP_TEST_CASE_CHINH.xlsx
│   ├── TEST_CASES_TONG_HOP_CHINH.md
│   ├── design_testcase_master.csv    # File CSV tổng hợp thiết kế 76 Test Cases (5 phân hệ)
│   ├── test_runner_master.py         # Script Python chạy tự động toàn bộ 76 Test Cases
│   ├── report_run_testcase_master.csv# Báo cáo kết quả thực thi tổng hợp 76/76 PASS
│   ├── design_testcase_{auth,mov,int,vip,adm}.csv
│   ├── test_runner_{auth,mov,int,vip,adm}.py
│   └── report_run_testcase_{auth,mov,int,vip,adm}.csv
├── netfix2/                          # Mã nguồn chính của ứng dụng web PHP Netfix2
├── Postman/                          # Bộ sưu tập Postman Automation & Environment
│   ├── Netfix2_MASTER_ALL_5_MODULES_Automation.postman_collection.json  # File Postman Tổng hợp 5 phân hệ
│   ├── Netfix2_AUTH_Security_Automation.postman_collection.json
│   ├── movie_mov_collection.json
│   ├── netfix2_Interactions & Engagement_automatic.postman.collection.json
│   ├── Netfix2_VIP_Packages_Payment_Automation.postman_collection.json
│   ├── admin_collection.json
│   └── Netfix2 - Local Environment.json
├── tests/                            # Bộ kịch bản kiểm thử tự động E2E CodeceptJS + Playwright
│   ├── master_all_modules_test.js    # File E2E tổng hợp toàn bộ 5 phân hệ (@AUTH, @MOV, @INT, @VIP, @ADM)
│   ├── login_test.js                 # E2E Phân hệ 1: Authentication & Security (24 TCs)
│   ├── movies_search_test.js         # E2E Phân hệ 2: Movies & Search
│   ├── search_security_test.js       # E2E Bảo mật Tìm kiếm (KAN-25)
│   ├── streaming_vip_test.js         # E2E Xem phim & Phân quyền VIP (KAN-29)
│   ├── view_counter_test.js          # E2E API Bộ đếm lượt xem (KAN-33)
│   ├── Interactions & Engagement.js  # E2E Phân hệ 3: Interactions & Engagement (15 TCs)
│   ├── vip_test.js                   # E2E Phân hệ 4: VIP Packages & Payment (11 TCs)
│   ├── admin_management_test.js      # E2E Phân hệ 5: Admin Management CMS (KAN-47..51)
│   └── admin_movies_rbac_test.js     # E2E Phân quyền Quản trị Admin (KAN-37)
├── codecept.conf.js                  # Cấu hình CodeceptJS + Playwright
├── Dockerfile                        # Cấu hình đóng gói Docker Image cho ứng dụng PHP
├── docker-compose.yml                # Khởi chạy nhanh bằng Docker Compose
├── sonar-project.properties          # Cấu hình quét mã tĩnh SonarQube
└── qodana.yaml                       # Cấu hình quét mã tĩnh JetBrains Qodana
```

---

## 🟧 Hướng Dẫn Cài Đặt Và Chạy Ứng Dụng Với XAMPP

1. Cài đặt **XAMPP**, mở **XAMPP Control Panel**, nhấn **Start** ở `Apache` và `MySQL`.
2. Đảm bảo thư mục `netfix2` được đặt tại `C:\xampp\htdocs\netfix2` (hoặc trỏ DocumentRoot/Symbolic Link vào thư mục dự án).
3. Mở [phpMyAdmin](http://localhost/phpmyadmin), tạo cơ sở dữ liệu tên `netfixvn` (Collation: `utf8mb4_unicode_ci`).
4. Chọn database `netfixvn` $\rightarrow$ mở tab **Import** $\rightarrow$ chọn file `database/netfixvn.sql` và nhấn **Import**.
5. Kiểm tra cấu hình kết nối CSDL trong `netfix2/config.php` (hoặc `netfix2/db.php`), mặc định:
   ```php
   $host = getenv('DB_HOST') ?: 'localhost';
   $dbname = getenv('DB_NAME') ?: 'netfixvn';
   $username = getenv('DB_USER') ?: 'root';
   $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
   ```
6. Truy cập website tại: [http://localhost/netfix2/](http://localhost/netfix2/)
   - **Tài khoản User mẫu:** `tuan@gmail.com` / Mật khẩu: `123456`
   - **Tài khoản Admin mẫu:** `admin` / Mật khẩu: `admin123` (Cổng đăng nhập: `/admin_login.php`)

---

## 🧪 Hướng Dẫn Thực Thi Các Bộ Kiểm Thử Tự Động

### 1. Chạy Kiểm Thử Tự Động Qua Postman Collection
- **File Tổng Hợp Cả 5 Phân Hệ:** `Postman/Netfix2_MASTER_ALL_5_MODULES_Automation.postman_collection.json`
- **Các bước thực hiện:**
  1. Mở **Postman** $\rightarrow$ Nhấn **Import**.
  2. Chọn file `Postman/Netfix2_MASTER_ALL_5_MODULES_Automation.postman_collection.json` (và tùy chọn `Postman/Netfix2 - Local Environment.json`).
  3. Chuột phải vào Collection **`Netfix2 - MASTER ALL 5 MODULES Automation (AUTH + MOV + INT + VIP + ADM)`** $\rightarrow$ Chọn **Run collection** $\rightarrow$ Nhấn **Run** để thực thi toàn bộ kịch bản kiểm thử của cả 5 phân hệ.

### 2. Chạy Python Automated Test Runner (Đọc CSV Thiết Kế & Xuất Báo Cáo CSV)
Trong thư mục `docs/`, dự án cung cấp bộ script Python tự động đọc các kịch bản từ `design_testcase_*.csv` và xuất kết quả ra `report_run_testcase_*.csv`:
```powershell
# Chạy file tổng hợp toàn bộ 76 Test Cases của 5 phân hệ:
python docs/test_runner_master.py

# Hoặc chạy riêng từng phân hệ:
python docs/test_runner_auth.py
python docs/test_runner_mov.py
python docs/test_runner_int.py
python docs/test_runner_vip.py
python docs/test_runner_adm.py
```

### 3. Chạy Kiểm Thử Động E2E Với CodeceptJS & Playwright
Đảm bảo đã cài đặt Node.js và chạy `npm install` tại thư mục gốc:
```powershell
# Chạy file E2E tổng hợp cả 5 phân hệ:
npx codeceptjs run tests/master_all_modules_test.js --steps

# Hoặc chạy lọc theo từng phân hệ bằng thẻ @grep:
npx codeceptjs run tests/master_all_modules_test.js --grep "@AUTH"
npx codeceptjs run tests/master_all_modules_test.js --grep "@MOV"
npx codeceptjs run tests/master_all_modules_test.js --grep "@INT"
npx codeceptjs run tests/master_all_modules_test.js --grep "@VIP"
npx codeceptjs run tests/master_all_modules_test.js --grep "@ADM"
```

---

## 🔄 Quy Trình CI/CD Tự Động (GitHub Actions)

File cấu hình `.github/workflows/ci-cd-pipeline.yml` tự động kích hoạt khi có sự kiện `push` hoặc `pull_request` trên các nhánh:
- `main`, `week*`
- 5 nhánh chức năng Black-Box: `AUTH_BLackbox`, `MOV_Blackbox`, `INTERACTIONS-&-ENGAGEMENT_Blackbox`, `VIP-PACKAGES-&-PAYMENT_Blackbox`, `ADM_Blackbox`

**4 Giai đoạn trong Pipeline:**
1. **`1A. Static Test - SonarQube Scan`:** Kiểm tra cú pháp `php -l` trên toàn bộ file `.php` và quét chất lượng mã nguồn với SonarQube Cloud.
2. **`1B. Static Test - Qodana Scan`:** Quét lỗ hổng bảo mật và phân tích luồng dữ liệu bằng JetBrains Qodana.
3. **`2. Dynamic Test - CodeceptJS E2E`:** Khởi tạo dịch vụ MySQL 8.0, nạp `database/netfixvn.sql`, bật PHP Built-in Server và chạy kiểm thử trình duyệt headless Chromium với CodeceptJS + Playwright.
4. **`3. Release - Build & Push Docker Hub`:** Khi cả kiểm thử tĩnh và động đều `PASS`, hệ thống tự động đóng gói Docker Image và đẩy lên Docker Hub kèm thẻ phiên bản `v1.0.<run_number>`.
