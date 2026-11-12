---
name: qodana-sarif-digest
description: Phân tích nhanh file qodana.sarif.json (nặng >1MB) thành bảng báo cáo lỗi Control-Flow, Data-Flow và Code Smell theo 5 phân hệ (AUTH, MOV, INT, VIP, ADM) mà không cần đọc trực tiếp file JSON khổng lồ vào ngữ cảnh AI.
---

# Qodana SARIF Digest Skill

Sử dụng skill này khi người dùng đính kèm hoặc yêu cầu phân tích file `qodana.sarif.json` từ GitHub Actions / Qodana Scan.

## Quy tắc Tiết kiệm Token (QUAN TRỌNG)
1. **TUYỆT ĐỐI KHÔNG** dùng `read_file` để đọc toàn bộ file `*.sarif.json` (vì file SARIF nặng >1MB sẽ tiêu tốn hàng chục nghìn tokens).
2. **LUÔN** chạy script Python dưới đây để trích xuất báo cáo cô đọng trong 1 giây:

```powershell
node ".gemini/skills/qodana-sarif-digest/scripts/parse_sarif.js" "<duong_dan_file_qodana.sarif.json>"
```
