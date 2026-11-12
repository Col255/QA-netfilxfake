---
name: php-whitebox-cfg
description: Tự động phân tích mã nguồn PHP trong netfix2/ để trích xuất các nút điều kiện (if/elseif/loops/ternary), tính độ phức tạp Cyclomatic V(G), sinh sơ đồ Luồng điều khiển (CFG Mermaid) và danh sách Đường đi cơ sở (Basis Paths) cho Kiểm thử Hộp trắng (White-Box Testing) mà không tốn token đọc toàn bộ file PHP.
---

# PHP White-Box & CFG Analyzer Skill

Sử dụng skill này bất kỳ khi nào người dùng yêu cầu:
- Phân tích **White-Box Testing (Kiểm thử Hộp trắng)** cho các file PHP trong `netfix2/`.
- Tính **Độ phức tạp Cyclomatic $V(G)$**, vẽ **Control Flow Graph (CFG)** hoặc liệt kê **Basis Paths**.
- Thiết kế Test Case phủ câu lệnh ($C_0$), phủ nhánh ($C_1$), hoặc phủ điều kiện con ($C_2$ / MC/DC).

## Quy tắc Tiết kiệm Token (QUAN TRỌNG)
1. **KHÔNG** dùng `read_file` đọc toàn bộ các file PHP dài hàng trăm dòng chỉ để đếm `if/else`.
2. **LUÔN** chạy script có sẵn dưới đây qua `run_command` để lấy ngay bảng nút điều kiện, $V(G)$ và sơ đồ Mermaid CFG:

```powershell
node ".gemini/skills/php-whitebox-cfg/scripts/analyze_cfg.js" netfix2/<ten_file>.php
```

Hoặc quét tổng quan cả 5 phân hệ (`AUTH`, `MOV`, `INT`, `VIP`, `ADM`) trong 1 lệnh:
```powershell
node ".gemini/skills/php-whitebox-cfg/scripts/analyze_cfg.js" --summary
```
