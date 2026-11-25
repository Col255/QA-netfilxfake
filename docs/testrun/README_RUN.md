# HƯỚNG DẪN CÀI ĐẶT & THỰC THI KIỂM THỬ HỘP TRẮNG (FE2 & FE3)
## Dự án Netfix2 | White-Box Testing (C0, C1, V(G)) & BVA

---

## 1. CÁC TỆP TIN BÀN GIAO TRONG THƯ MỤC NÀY
* `docs/testrun/WHITEBOX_CFG_FE2_MOV.md`: Sơ đồ CFG, bảng $S_i/D_i$, $V(G)=105$, 105 Basis Paths của phân hệ MOV.
* `docs/testrun/WHITEBOX_CFG_FE3_INT.md`: Sơ đồ CFG, bảng $S_i/D_i$, $V(G)=16$, 16 Basis Paths của phân hệ INT.
* `docs/testrun/TEST_MATRIX.csv`: Ma trận 125 Test Cases đầy đủ cột theo chuẩn mục 7.
* `docs/testrun/TRACEABILITY_MATRIX.csv`: Ma trận truy vết Requirement ↔ CFG Node ↔ TC ↔ Defect.
* `docs/testrun/COVERAGE_REPORT.html`: Giao diện trực quan hóa báo cáo độ bao phủ C0 & C1.
* `docs/testrun/coverage.xml`: Báo cáo độ bao phủ chuẩn Clover/Cobertura.
* `docs/testrun/coverage-summary.json`: Tổng hợp số liệu và đối chiếu $V(G)$ lý thuyết.
* `docs/testrun/DEFECT_REPORT.md`: Báo cáo chi tiết 9 defect phát hiện (có xác nhận BUG-01 Confirmed).
* `docs/testrun/db_fixtures/01_seed_whitebox_accounts.sql`: File nạp dữ liệu mẫu 4 trạng thái (Guest, User, VIP, Admin).

---

## 2. CÁCH CHẠY LẠI TOÀN BỘ KIỂM THỬ

### Cách 1: Chạy bằng Node.js / Bun Test Runner (Khuyến nghị, nhanh nhất)
```powershell
# Chạy bộ runner tự động cho FE2 và FE3
node docs/testrun/whitebox_fe2_fe3_runner.js
# hoặc dùng bun:
bun docs/testrun/whitebox_fe2_fe3_runner.js
```

### Cách 2: Chạy qua PHPUnit (với Xdebug mode=coverage)
```powershell
# Nạp CSDL mẫu trước khi chạy
C:\xampp\mysql\bin\mysql.exe -u root netfixvn < docs\testrun\db_fixtures\01_seed_whitebox_accounts.sql

# Chạy kiểm thử hộp trắng qua PHPUnit
vendor\bin\phpunit --configuration phpunit.xml
```

### Cách 3: Chạy E2E CodeceptJS Runner (nếu có server web bật)
```powershell
powershell -ExecutionPolicy Bypass -File .\run_tests.ps1
```

---

## 3. RÀNG BUỘC BẢO TOÀN MÃ NGUỒN
Mã nguồn gốc trong `netfix2/` được bảo vệ nghiêm ngặt:
```powershell
git diff netfix2/
# Kết quả trả về rỗng (0 dòng thay đổi)
```
