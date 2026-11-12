---
name: project-graph-router
description: "[BẮT BUỘC CHẠY ĐẦU TIÊN] Bộ định tuyến chọn Skill thông minh (Skill Router) kết hợp Đồ thị Tri thức Dự án nén (Compressed Project Knowledge Graph). Giúp chọn đúng Skill phù hợp nhất với yêu cầu người dùng và tra cứu sẵn cấu trúc file, bảng DB, biến POST/GET, hàm PHP và tọa độ dòng (StartLine-EndLine) từ đồ thị nén để tiết kiệm 80-95% Token/Quota."
---

# Project Graph & Skill Router (Meta-Skill Tiết Kiệm Quota)

## 1. NGUYÊN TẮC CỐT LÕI (BẮT BUỘC TRƯỚC MỌI HÀNH ĐỘNG)
Trước khi gọi bất kỳ Skill nào khác hoặc dùng `read_file` đọc mã nguồn:
1. **Bước 1 — Định tuyến Skill (Skill Routing):** Đối chiếu yêu cầu của người dùng với **Ma trận Chọn Skill** ở Mục 2 để chọn đúng **1 Skill tối ưu nhất** (không nạp nhiều skill dư thừa).
2. **Bước 2 — Tra cứu Đồ thị Nén (`project-graph.json`):** Thay vì đọc mò nhiều file trong thư mục dự án, hãy chạy lệnh tra cứu đồ thị nén để biết ngay **file nào**, **chứa hàm/biến gì**, **tương tác bảng SQL nào**, và **nằm ở dòng thứ mấy (`StartLine` - `EndLine`)**:
   ```powershell
   node ".gemini/skills/project-graph-router/scripts/build_graph.js" --query "<tu_khoa_hoac_phan_he>"
   ```
3. **Bước 3 — Đọc Cắt Lát Chính Xác (Surgical Line Reading):** Khi cần đọc nội dung file bằng `read_file`, **CHỈ** đọc đúng khoảng dòng `phpLogicLines` (`StartLine` đến `EndLine`) mà đồ thị đã chỉ định (tuyệt đối không đọc phần HTML/CSS tĩnh bên dưới).

---

## 2. MA TRẬN CHỌN SKILL (SKILL DECISION MATRIX)

| Yêu cầu của Người dùng | Skill ĐƯỢC CHỌN | Lệnh Thực thi Tiết kiệm Token | Không được làm (Tránh tốn Quota) |
| :--- | :--- | :--- | :--- |
| **1. Tra cứu cấu trúc dự án, tìm file, biến `$_POST`/`$_GET`, bảng DB, luồng chuyển trang** | **`project-graph-router`** *(Skill này)* | `node ".gemini/skills/project-graph-router/scripts/build_graph.js" --query <keyword>` | Không dùng `read_file` đọc hàng loạt file PHP để tìm biến/bảng. |
| **2. Kiểm thử White-Box, vẽ CFG, tính độ phức tạp $V(G)$, tìm nhánh `if/else`, lập Basis Paths** | **`php-whitebox-cfg`** | `node ".gemini/skills/php-whitebox-cfg/scripts/analyze_cfg.js" netfix2/<file>.php` | Không đọc toàn bộ file PHP bằng mắt để tự đếm `if/else`. |
| **3. Phân tích file `qodana.sarif.json`, tìm lỗi Static Analysis / Control-Flow / Data-Flow** | **`qodana-sarif-digest`** | `node ".gemini/skills/qodana-sarif-digest/scripts/parse_sarif.js" <path>` | Tuyệt đối không dùng `read_file` lên file `.sarif.json` (>1MB). |
| **4. Kiểm tra số lượng Test Case, độ phủ nhãn (`V, X, B, SEC, API`), đồng bộ CSV Design & Report** | **`qa-data-driven-sync`** | `node ".gemini/skills/qa-data-driven-sync/scripts/audit_coverage.js"` | Không đọc từng file CSV trong `docs/Design/` và `docs/Report/`. |
| **5. Vẽ sơ đồ kiến trúc hệ thống C4 Model / Structurizr (`workspace.dsl`)** | **`c4-architecture`** | Đọc `workspace.dsl` | Không quét lại toàn bộ source code nếu `project-graph.json` đã có đủ thông tin. |
| **6. Sinh hoặc cập nhật tài liệu đặc tả SRS (`docs/srs.docx`)** | **`bmad-srs-generator`** | Dùng dữ liệu từ `project-graph.json` | Không đọc lại toàn bộ `netfix2/*.php` từ đầu. |

---

## 3. CÁC LỆNH TRA CỨU ĐỒ THỊ DỰ ÁN NÉN (`build_graph.js`)

- **Cập nhật lại đồ thị nén khi dự án có thay đổi code:**
  ```powershell
  node ".gemini/skills/project-graph-router/scripts/build_graph.js" --build
  ```
- **Xem bản đồ tổng quan toàn dự án (Nodes, Edges, Modules, SQL Tables):**
  ```powershell
  node ".gemini/skills/project-graph-router/scripts/build_graph.js" --overview
  ```
- **Tra cứu theo Phân hệ (`AUTH`, `MOV`, `INT`, `VIP`, `ADM`) hoặc tên file/hàm/bảng (ví dụ `orders.php`, `vip_orders`, `watch.php`):**
  ```powershell
  node ".gemini/skills/project-graph-router/scripts/build_graph.js" --query AUTH
  node ".gemini/skills/project-graph-router/scripts/build_graph.js" --query orders.php
  ```
