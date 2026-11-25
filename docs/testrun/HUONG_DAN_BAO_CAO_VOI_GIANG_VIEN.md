# HƯỚNG DẪN CHI TIẾT BÁO CÁO TIẾN ĐỘ & KẾT QUẢ VỚI GIẢNG VIÊN
**Dự án:** Netfix2 (Website Xem Phim Trực Tuyến - PHP 8.2 + MySQLi)  
**Phân hệ phụ trách chính:** **FE2 - Movies & Discovery** và **FE3 - Interactions & Engagement**  
**Sinh viên báo cáo / Tài khoản GitHub:** `Duong204-uth` (`351806516+Duong204-uth@users.noreply.github.com`)  
**Tài liệu tham chiếu:** [BAO_CAO_NGHIEM_THU_WHITEBOX.html](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/BAO_CAO_NGHIEM_THU_WHITEBOX.html) | [PR #5 trên GitHub](https://github.com/Col255/QA-netfilxfake/pull/5)  
**Ngày báo cáo:** 18/11/2026  

---

## MỤC LỤC BÀI BÁO CÁO

1. [Phần 1: Lời mở đầu & Giới thiệu phạm vi công việc (1 phút)](#phần-1-lời-mở-đầu--giới-thiệu-phạm-vi-công-việc)
2. [Phần 2: Phương pháp luận Kiểm thử Hộp trắng đã áp dụng (2 phút)](#phần-2-phương-pháp-luận-kiểm-thử-hộp-trắng-đã-áp-dụng)
3. [Phần 3: Giai đoạn 1 - Thiết kế Test Case & Phát hiện 9 Defect (3 phút)](#phần-3-giai-đoạn-1---thiết-kế-test-case--phát-hiện-9-defect)
4. [Phần 4: Giai đoạn 2 - Quy trình Git & Khắc phục lỗi trên Jira (3 phút)](#phần-4-giai-đoạn-2---quy-trình-git--khắc-phục-lỗi-trên-jira)
5. [Phần 5: Giai đoạn 3 - Nghiệm thu sau Fix & Độ bao phủ 100% (2 phút)](#phần-5-giai-đoạn-3---nghiệm-thu-sau-fix--độ-bao-phủ-100)
6. [Phần 6: Danh mục sản phẩm bàn giao minh chứng cho thầy (1 phút)](#phần-6-danh-mục-sản-phẩm-bàn-giao-minh-chứng-cho-thầy)
7. [Phần 7: Bộ câu hỏi & Kịch bản phản biện tự tin với thầy (FAQ Defense)](#phần-7-bộ-câu-hỏi--kịch-bản-phản-biện-tự-tin-với-thầy)

---

## PHẦN 1: LỜI MỞ ĐẦU & GIỚI THIỆU PHẠM VI CÔNG VIỆC

> **Lời thoại mẫu để bạn nói với thầy:**
> *"Em chào thầy. Trong đợt kiểm thử tuần 6 của dự án Netfix2, em phụ trách triển khai toàn diện quy trình **Kiểm thử Hộp trắng (White-Box Testing)** và **Khắc phục lỗi (Defect Resolution)** cho 2 phân hệ trọng yếu của hệ thống:
> - **Phân hệ FE2 (Movies & Discovery):** Quản lý hiển thị trang chủ, danh sách phim, tìm kiếm và phát video (`index.php`, `movies.php`, `search.php`, `watch.php`).
> - **Phân hệ FE3 (Interactions & Engagement):** Quản lý tương tác người dùng gồm bộ đếm lượt xem, bình luận phim và lịch sử xem (`api/view_counter.php`, `comments.php`, `watch_history.php`).
>
> Em đã hoàn thành trọn vẹn quy trình từ: Vẽ đồ thị dòng điều khiển (CFG) $\rightarrow$ Tính toán độ phức tạp $V(G)$ $\rightarrow$ Thiết kế 123 ca kiểm thử phủ 100% câu lệnh ($C_0$) và nhánh ($C_1$) $\rightarrow$ Phát hiện 9 Defect $\rightarrow$ Tạo branch độc lập sửa lỗi theo chuẩn Git Flow & Jira Smart Commits $\rightarrow$ Tích hợp vào `week6` và tạo Pull Request #5 lên `main`.*"

---

## PHẦN 2: PHƯƠNG PHÁP LUẬN KIỂM THỬ HỘP TRẮNG ĐÃ ÁP DỤNG

Nếu thầy hỏi: *"Em đã áp dụng kỹ thuật kiểm thử nào và tính toán độ bao phủ ra sao?"*, bạn mở file [WHITEBOX_CFG_FE2_MOV.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/WHITEBOX_CFG_FE2_MOV.md) và [WHITEBOX_CFG_FE3_INT.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/WHITEBOX_CFG_FE3_INT.md) lên và trình bày theo 4 ý sau:

1. **Vẽ sơ đồ dòng điều khiển (Control Flow Graph - CFG):**
   - Đánh mã hóa từng khối lệnh tuần tự ($S_1, S_2, ...$) và từng điểm rẽ nhánh quyết định ($D_1, D_2, ...$).
   - Nhánh được chia rõ ràng thành nhánh True ($D_i\_T$) và nhánh False ($D_i\_F$).
2. **Tính toán độ phức tạp Cyclomatic Complexity $V(G)$ theo 2 công thức chuẩn IEEE:**
   - Công thức 1: $V(G) = P + 1$ (với $P$ là số điểm rẽ nhánh vị từ - Predicate Nodes).
   - Công thức 2: $V(G) = E - N + 2$ (với $E$ là số cạnh, $N$ là số nút của đồ thị).
   - **Kết quả lý thuyết phân hệ:**
     - `index.php`: $P = 35 \implies V(G) = 36$
     - `movies.php`: $P = 18 \implies V(G) = 19$
     - `search.php`: $P = 14 \implies V(G) = 15$
     - `watch.php`: $P = 34 \implies V(G) = 35$
     - `api/view_counter.php`: $P = 1 \implies V(G) = 2$
     - `comments.php`: $P = 8 \implies V(G) = 9$
     - `watch_history.php`: $P = 4 \implies V(G) = 5$
     - **$\implies$ Tổng $V(G)$ lý thuyết cho 7 tệp mã nguồn:** $36 + 19 + 15 + 35 + 2 + 9 + 5 = \mathbf{121}$.
3. **Kỹ thuật Đường cơ sở (Basis Path Testing):**
   - Thiết kế tối thiểu **121 đường cơ sở độc lập** đảm bảo mỗi nhánh $D_i$ và mỗi khối lệnh $S_i$ được duyệt qua ít nhất một lần.
4. **Kết hợp Phân tích Giá trị Biên (Boundary Value Analysis - BVA 7 điểm):**
   - Biên dưới, biên trên, giá trị âm, tràn số nguyên (`id=0`, `id=-1`, `id=1`, `id=2147483647`, `id=2147483648`).
   - Kiểm tra chuỗi rỗng, toàn khoảng trắng (`"   "`), và chuỗi vượt ngưỡng 500 ký tự (10,000 ký tự).

---

## PHẦN 3: GIAI ĐOẠN 1 - THIẾT KẾ TEST CASE & PHÁT HIỆN 9 DEFECT

Mở file [DEFECT_REPORT.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/DEFECT_REPORT.md) và trình bày kết quả chạy thử nghiệm ban đầu:

* **Tổng số ca kiểm thử thiết kế:** **123 Test Cases**
* **Kết quả trước khi fix:** **112 PASS / 11 FAIL**
* **Xác nhận 9 Defect (1 High, 4 Medium, 4 Low):**

| STT | Mã Defect | Mã Jira | Tệp tin & Dòng | Mức độ | Bản chất lỗi phát hiện |
| :---: | :---: | :---: | :--- | :---: | :--- |
| 1 | **WB-DEF-01** | `KAN-205` | `netfix2/watch.php:75` | **High** | **Nhánh chết (Target BUG-01):** Toán tử 3 ngôi `isset($_SESSION['user_id']) ? false : false` khiến cả 2 nhánh đều trả về `false`, tạo dead branch. |
| 2 | **WB-DEF-02** | `KAN-206` | `netfix2/api/view_counter.php:11` | **Medium** | **Báo thành công ảo:** Gọi API tăng view với ID không tồn tại trong DB (`id=999999`) nhưng vẫn trả về `{"success": true}` do không kiểm tra `affected_rows > 0`. |
| 3 | **WB-DEF-03** | `KAN-208` | `netfix2/api/view_counter.php:2` | **Low** | **Thiếu Content-Type:** Xuất JSON nhưng thiếu header `Content-Type: application/json; charset=utf-8`, server mặc định trả về `text/html`. |
| 4 | **WB-DEF-04** | `KAN-209` | `netfix2/index.php:31` | **Low** | **PHP 8 Deprecated:** Khai báo tham số tùy chọn trước tham số bắt buộc trong `function getMovies($conn, $genre = null, $limit)`. |
| 5 | **WB-DEF-05** | `KAN-207` | `netfix2/watch.php:160` | **Medium** | **Thiếu chặn biên trên (BVA Max):** Không giới hạn độ dài bình luận, cho phép chèn chuỗi 10,000 ký tự vào bảng CSDL. |
| 6 | **WB-DEF-06** | `KAN-210` | `netfix2/watch.php:158` | **Low** | **Thiếu phản hồi người dùng:** Khi gửi bình luận toàn khoảng trắng (`"   "`), hệ thống bỏ qua không lưu DB nhưng không gán `$comment_error`. |
| 7 | **WB-DEF-07** | `KAN-211` | `netfix2/watch_history.php:13` | **Low** | **Lỗi biên âm (BVA Min-1):** Dùng `is_numeric($_GET['delete'])` chấp nhận số âm (`delete=-5`), dẫn đến chạy truy vấn DELETE vô nghĩa với ID âm. |
| 8 | **WB-DEF-08** | `KAN-212` | `netfix2/search.php:75,86` | **Medium** | **Lộ schema CSDL:** Dùng lệnh `die("Lỗi: " . $conn->error)` làm dừng ứng dụng và lộ thông tin cấu trúc cơ sở dữ liệu. |
| 9 | **WB-DEF-09** | `KAN-213` | `netfix2/search.php:60,66` | **Low** | **Bộ lọc chết (Dead Filter):** Điều kiện `if (!empty($query))` bao bọc cả `genre`, khiến người dùng chỉ chọn thể loại mà không gõ từ khóa thì không ra kết quả nào. |

---

## PHẦN 4: GIAI ĐOẠN 2 - QUY TRÌNH GIT & KHẮC PHỤC LỖI TRÊN JIRA

> **Ý chính để nhấn mạnh tính chuyên nghiệp với thầy:**
> *"Thưa thầy, em không sửa trực tiếp trên nhánh `main` hay sửa dồn một lúc. Em tuân thủ nghiêm ngặt mô hình **Git Feature Branching** kết hợp **Jira Smart Commits**:
> 1. Mỗi defect được tạo một branch độc lập từ `week6` với format `KAN-xxx-...`.
> 2. Commit message mang đúng mã Jira và định danh tác giả `Duong204-uth`.
> 3. Kiểm tra cú pháp PHP 8.2 (`php -l`) sạch sẽ trước khi commit.
> 4. Push từng nhánh lên remote `origin` để Jira tự động bắt webhook hiển thị commit trong mục Development của Issue.
> 5. Sau khi hoàn thành toàn bộ, em merge tuần tự từng nhánh vào `week6`, xử lý conflict chuẩn xác và mở **Pull Request #5** từ `week6` vào `main`."*

### Minh chứng các nhánh và commit đã push lên remote:
- `KAN-205` $\rightarrow$ Branch `KAN-205-fix-nhanh-chết-gan-biến-user-is-vip-tại-watch-php-bug-01` (Commit `11f809d`)
- `KAN-206` $\rightarrow$ Branch `KAN-206-kiểm-tra-số-dong-bị-ảnh-hưởng-stmt-affected-rows-khi-tang-view-tại-api-view-counter-php` (Commit `c40e209`)
- `KAN-207` $\rightarrow$ Branch `KAN-207-bổ-sung-rang-buộc-dộ-dai-tối-da-500-ky-tự-bva-max-cho-binh-luận-tại-watch-php` (Commit `8091775`)
- `KAN-208` $\rightarrow$ Branch `KAN-208-bổ-sung-header-content-type-application-json-cho-api-view-counter-php` (Commit `1384841`)
- `KAN-209` $\rightarrow$ Branch `KAN-209-chuẩn-hoa-thứ-tự-tham-số-ham-get-movies-dể-triệt-tieu-lỗi-php-8-deprecated-trong-index-php` (Commit `229de04`)
- `KAN-210` $\rightarrow$ Branch `KAN-210-bổ-sung-thong-bao-lỗi-comment-error-khi-gửi-binh-luận-rỗng-hoặc-toan-khoảng-trắng-tại-watch-php` (Commit `6d04c41`)
- `KAN-211` $\rightarrow$ Branch `KAN-211-bổ-sung-kiểm-tra-số-nguyen-dương-0-khi-xoa-lịch-sử-xem-tại-watch-history-php` (Commit `38a19af`)
- `KAN-212` $\rightarrow$ Branch `KAN-212-thay-thế-lệnh-die-bằng-xử-ly-lỗi-an-toan-khong-lam-lộ-schema-sql-tại-search-php` (Commit `cfdc05f`)
- `KAN-213` $\rightarrow$ Branch `KAN-213-tach-dộc-lập-diều-kiện-lọc-thể-loại-genre-khong-phụ-thuộc-từ-khoa-query-tại-search-php` (Commit `9134067`)
- **Các task chuẩn hóa Qodana Static Scan cũng đã hoàn tất:**
  - `KAN-189` (Commit `e15bab0`): Thêm `/** @var mysqli $conn */` và null-safety guard vào `api/view_counter.php`.
  - `KAN-190` (Commit `6c40fa7`): Xóa thẻ đóng `?>` thừa theo chuẩn PSR-12 tại `api/view_counter.php`.
  - `KAN-191` (Commit `16a18de`): Thêm `/** @var mysqli $conn */` vào `comments.php`.
  - `KAN-193` (Commit `896bc97`): Thêm `/** @var mysqli $conn */` và khối `if ($stmt)` vào `watch_history.php`.

---

## PHẦN 5: GIAI ĐOẠN 3 - NGHIỆM THU SAU FIX & ĐỘ BAO PHỦ 100%

Mở trực tiếp file HTML nghiệm thu: [BAO_CAO_NGHIEM_THU_WHITEBOX.html](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/BAO_CAO_NGHIEM_THU_WHITEBOX.html) để trình chiếu cho thầy xem bảng thống kê số liệu:

```text
================================================================================
BẢNG THỐNG KÊ ĐỘ BAO PHỦ VÀ KẾT QUẢ NGHIỆM THU HOÀN THIỆN (PHÂN HỆ FE2 & FE3)
================================================================================
* Tổng số Test Cases thực thi    : 123 / 123 Test Cases
* Số ca kiểm thử ĐẠT (PASS)      : 123 (100.0%)
* Số ca kiểm thử KHÔNG ĐẠT (FAIL): 0 (0.0%)
* Độ bao phủ câu lệnh (C0)       : 100.0% (138/138 Khối lệnh Si)
* Độ bao phủ nhánh (C1)          : 100.0% (228/228 Nhánh rẽ Di)
* Độ phức tạp Cyclomatic V(G)    : 121 / 121 (Khớp tuyệt đối lý thuyết đồ thị CFG)
* Tỷ lệ Defect khắc phục         : 9 / 9 Defect (100.0% Resolved & Verified)
* Lỗi hồi quy (Regression Bugs)  : 0 lỗi
* Pull Request tích hợp          : PR #5 (week6 -> main)
================================================================================
```

### Bảng chi tiết 7 file mục tiêu đạt 100% PASS:
1. `netfix2/api/view_counter.php`: 8 TCs $\rightarrow$ **100% PASS** ($C_0$: 4/4, $C_1$: 2/2, $V(G)$: 2)
2. `netfix2/comments.php`: 5 TCs $\rightarrow$ **100% PASS** ($C_0$: 12/12, $C_1$: 16/16, $V(G)$: 9)
3. `netfix2/watch_history.php`: 5 TCs $\rightarrow$ **100% PASS** ($C_0$: 6/6, $C_1$: 8/8, $V(G)$: 5)
4. `netfix2/index.php`: 36 TCs $\rightarrow$ **100% PASS** ($C_0$: 38/38, $C_1$: 70/70, $V(G)$: 36)
5. `netfix2/movies.php`: 19 TCs $\rightarrow$ **100% PASS** ($C_0$: 20/20, $C_1$: 36/36, $V(G)$: 19)
6. `netfix2/search.php`: 15 TCs $\rightarrow$ **100% PASS** ($C_0$: 16/16, $C_1$: 28/28, $V(G)$: 15)
7. `netfix2/watch.php`: 35 TCs $\rightarrow$ **100% PASS** ($C_0$: 42/42, $C_1$: 68/68, $V(G)$: 35)

---

## PHẦN 6: DANH MỤC SẢN PHẨM BÀN GIAO MINH CHỨNG CHO THẦY

Khi thầy hỏi: *"Em bàn giao những file nào để chứng minh?"*, bạn mở cây thư mục `docs/testrun/` và chỉ cho thầy 8 sản phẩm cụ thể:

1. **Báo cáo Nghiệm thu HTML chuyên nghiệp:** [BAO_CAO_NGHIEM_THU_WHITEBOX.html](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/BAO_CAO_NGHIEM_THU_WHITEBOX.html) *(Thiết kế chuẩn doanh nghiệp, in A4 / xuất PDF `Ctrl+P` có khối ký xác nhận).*
2. **Báo cáo Nghiệm thu Markdown:** [BAO_CAO_NGHIEM_THU_WHITEBOX.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/BAO_CAO_NGHIEM_THU_WHITEBOX.md) *(Đọc trực tiếp trong IDE hoặc GitHub).*
3. **Sơ đồ CFG & Phân tích chi tiết:** 
   - [WHITEBOX_CFG_FE2_MOV.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/WHITEBOX_CFG_FE2_MOV.md)
   - [WHITEBOX_CFG_FE3_INT.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/WHITEBOX_CFG_FE3_INT.md)
4. **Ma trận Kiểm thử (Test Matrix 123 TCs):** [TEST_MATRIX.csv](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/TEST_MATRIX.csv)
5. **Ma trận Truy vết 4 chiều (Traceability Matrix):** [TRACEABILITY_MATRIX.csv](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/TRACEABILITY_MATRIX.csv) *(Truy vết Requirement $\leftrightarrow$ CFG Node $\leftrightarrow$ Test Case $\leftrightarrow$ Defect).*
6. **Báo cáo Độ bao phủ chuẩn Clover:** [coverage.xml](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/coverage.xml)
7. **Dữ liệu tổng hợp JSON:** [coverage-summary.json](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/coverage-summary.json)
8. **File nạp dữ liệu CSDL kiểm thử mẫu:** [01_seed_whitebox_accounts.sql](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/db_fixtures/01_seed_whitebox_accounts.sql) *(Tài khoản Guest, User thường `tuan`, VIP `vip_user`, Admin `admin`, Blocked `blocked_user`).*

---

## PHẦN 7: BỘ CÂU HỎI & KỊCH BẢN PHẢN BIỆN TỰ TIN VỚI THẦY (FAQ DEFENSE)

Dưới đây là các câu hỏi giảng viên hay hỏi nhất khi nghiệm thu và cách bạn trả lời dứt khoát, chuẩn kỹ thuật:

### Câu hỏi 1: *"Làm sao em chứng minh được độ phức tạp $V(G) = 121$ của em là tính toán đúng chứ không phải bịa số?"*
> **Cách trả lời:**
> *"Dạ thưa thầy, em tính toán $V(G)$ dựa trên cấu trúc rẽ nhánh thực tế của từng hàm và từng file theo đúng định lý McCabe:
> - Mỗi câu lệnh điều kiện `if`, `elseif`, toán tử ba ngôi `? :`, vòng lặp `foreach`, `while` tương ứng với một điểm rẽ nhánh vị từ $P$. Khi đó $V(G) = P + 1$.
> - Trong file `docs/testrun/WHITEBOX_CFG_FE2_MOV.md` và `FE3_INT.md`, em đã liệt kê tường minh từng nút nhánh từ $D_1$ đến $D_n$, kèm dòng code tương ứng trong PHP.
> - Ví dụ: Trong `netfix2/index.php`, có 35 điểm rẽ nhánh $\implies V(G) = 36$. Tổng cộng 7 file có đúng 114 điểm rẽ nhánh vị từ, tương ứng $V(G) = 114 + 7 = 121$. Số lượng Basis Paths em thiết kế khớp chính xác 121 đường cơ sở độc lập này."*

### Câu hỏi 2: *"BUG-01 là lỗi gì và tại sao nó lại được xếp vào mức độ High?"*
> **Cách trả lời:**
> *"Dạ thưa thầy, BUG-01 nằm tại dòng 75 của tệp `netfix2/watch.php`. Đoạn code ban đầu là:
> ```php
> $user_is_vip = isset($_SESSION['user_id']) ? false : false;
> ```
> Đây là một **Dead Branch (Nhánh chết)** nghiêm trọng: Dù người dùng đã đăng nhập hay chưa đăng nhập thì biểu thức toán tử 3 ngôi đều trả về `false`. Nhánh True bị triệt tiêu hoàn toàn, làm sai lệch logic phân quyền VIP của người dùng khi xem phim. Em đã refactor thành gán mặc định an toàn `$user_is_vip = false;` trước khi kiểm tra phiên đăng nhập và truy vấn bảng `subscriptions`."*

### Câu hỏi 3: *"Tại sao em không commit trực tiếp code sửa lên nhánh chính mà phải tạo nhiều branch KAN-xxx như vậy?"*
> **Cách trả lời:**
> *"Dạ thưa thầy, đó là nguyên tắc **Branch per Issue** trong quy trình phát triển phần mềm chuẩn Agile:
> 1. Mỗi branch tương ứng với đúng một Issue trên Jira để tiện theo dõi tiến độ độc lập.
> 2. Giữ cho nhánh `week6` và `main` luôn ở trạng thái sạch, ổn định (Clean working tree).
> 3. Tích hợp webhook của GitHub với Jira: Khi branch mang tên `KAN-xxx` và commit có prefix `KAN-xxx:`, Jira tự động chuyển trạng thái task và liên kết commit vào panel Development.
> 4. Sau khi test độc lập từng task thành công, em mới merge tuần tự vào nhánh `week6` và mở Pull Request #5 để Review."*

### Câu hỏi 4: *"Em có kiểm tra lại các commit của bạn cùng nhóm (như bạn Huy) không và phát hiện ra vấn đề gì?"*
> **Cách trả lời:**
> *"Dạ có thưa thầy. Em đã review kỹ các task `KAN-186`, `KAN-192`, `KAN-194`, `KAN-195`, `KAN-196` của bạn Huy và đã ghi nhận 3 vấn đề cần bạn ấy tối ưu:
> 1. Trong file kiểm thử `tests/whitebox_int_test.js`, với các ca kiểm thử POST bạn ấy dùng lệnh `I.amOnPage()` của CodeceptJS vốn chỉ gửi request GET, nên thực tế chưa test được luồng POST.
> 2. Trong runner `whitebox_runner_int.js`, đoạn đăng nhập bạn ấy gửi field `username=tuan` thay vì `identifier=tuan` nên server `login.php` không nhận diện được.
> 3. Cấu hình `qodana.yaml` bạn ấy để PHP `8.1` trong khi toàn bộ CI/CD Pipeline của nhóm chạy trên PHP `8.2`. Em đã gửi comment chi tiết từng task để bạn ấy hoàn thiện."*

---

## TỔNG KẾT BƯỚC CHUẨN BỊ TRƯỚC KHI BÁO CÁO:

1. Mở sẵn trình duyệt tab 1: File báo cáo HTML [BAO_CAO_NGHIEM_THU_WHITEBOX.html](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/BAO_CAO_NGHIEM_THU_WHITEBOX.html).
2. Mở sẵn trình duyệt tab 2: Pull Request trên GitHub [https://github.com/Col255/QA-netfilxfake/pull/5](https://github.com/Col255/QA-netfilxfake/pull/5).
3. Mở sẵn VS Code hiển thị file [HUONG_DAN_BAO_CAO_VOI_GIANG_VIEN.md](file:///c:/Users/PHUONG%20THAO/Downloads/netfix2/docs/testrun/HUONG_DAN_BAO_CAO_VOI_GIANG_VIEN.md) này để làm "phao" trình bày.
4. Tự tin trình bày theo đúng cấu trúc 7 phần ở trên. Chúc bạn báo cáo đạt điểm tối đa!
