# Quy tắc Bắt buộc: Định tuyến Skill & Tra cứu Đồ thị Nén Trước Khi Đọc File

1. **Cân nhắc chọn Skill trước khi hành động (Skill Routing First):**
   - Trước khi kích hoạt bất kỳ Skill nào, luôn đối chiếu yêu cầu của người dùng với bảng định tuyến trong `.gemini/skills/project-graph-router/SKILL.md`:
     - Yêu cầu về **White-Box / CFG / $V(G)$ / Basis Paths** $\rightarrow$ Dùng `php-whitebox-cfg` (`node ".gemini/skills/php-whitebox-cfg/scripts/analyze_cfg.js"`).
     - Yêu cầu về **Qodana / `.sarif.json`** $\rightarrow$ Dùng `qodana-sarif-digest` (`node ".gemini/skills/qodana-sarif-digest/scripts/parse_sarif.js"`).
     - Yêu cầu về **CSV Test Case / Độ phủ / Báo cáo** $\rightarrow$ Dùng `qa-data-driven-sync` (`node ".gemini/skills/qa-data-driven-sync/scripts/audit_coverage.js"`).
     - Yêu cầu **tìm hiểu code / luồng trang / bảng DB / biến input** $\rightarrow$ Dùng `project-graph-router` (`node ".gemini/skills/project-graph-router/scripts/build_graph.js" --query <tu_khoa>`).

2. **Đọc File Cắt Lát theo Tọa độ Đồ thị (Surgical Line Reading):**
   - Không đọc toàn bộ file PHP bằng `read_file`. Luôn dùng `phpBackendLines: [StartLine, EndLine]` trong `.gemini/skills/project-graph-router/scripts/project-graph.json` để chỉ đọc phần logic PHP (bỏ qua phần HTML/CSS tĩnh bên dưới) nhằm tiết kiệm tối đa Token & Quota.
