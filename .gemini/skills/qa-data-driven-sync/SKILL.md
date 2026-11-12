---
name: qa-data-driven-sync
description: Kiểm toán nhanh toàn bộ các file CSV Thiết kế (docs/Design/design_testcase_*.csv) và Báo cáo (docs/Report/report_run_testcase_*.csv) của cả 5 phân hệ để thống kê số lượng Test Case, tỷ lệ Pass/Fail và Độ phủ Nhãn (V, X, B, SEC, API, ST) chỉ bằng 1 lệnh CLI.
---

# QA Data-Driven Audit & Sync Skill

Sử dụng skill này khi cần kiểm tra trạng thái, thống kê số lượng Test Case hoặc tính Độ phủ Kiểm thử (Coverage) trên toàn bộ các file CSV trong `docs/Design/` và `docs/Report/`.

## Quy tắc Tiết kiệm Token (QUAN TRỌNG)
1. **KHÔNG** đọc từng file CSV trong `docs/Design/` và `docs/Report/` bằng `read_file` khi chỉ cần thống kê hoặc kiểm tra độ phủ.
2. **LUÔN** chạy lệnh dưới đây để lấy bảng tổng hợp của cả 6 bộ CSV (`auth`, `mov`, `int`, `vip`, `adm`, `master`):

```powershell
node ".gemini/skills/qa-data-driven-sync/scripts/audit_coverage.js"
```
