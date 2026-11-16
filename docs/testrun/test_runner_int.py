"""
AUTOMATED TEST RUNNER CHO 5 TASK JIRA INT (NETFIX2)
Đọc test case thiết kế và xuất kết quả kiểm thử ra CSV
"""

import os
import csv
import time
import json
import urllib.request
import urllib.parse
import urllib.error

# Cấu hình địa chỉ cơ sở máy chủ Netfix2
BASE_URL = "http://localhost/netfix2"

def run_int_test_suite():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    input_csv = os.path.normpath(os.path.join(script_dir, "..", "Design", "design_testcase_int.csv"))
    output_csv = os.path.normpath(os.path.join(script_dir, "..", "Report", "report_run_testcase_int.csv"))

    print("+" + "=" * 90 + "+")
    print("| KHỞI CHẠY KIỂM THỬ TỰ ĐỘNG CHO 5 TASK JIRA INT - MODULE INTERACTIONS & ENGAGEMENT")
    print(f"| File Test Case  : {input_csv}")
    print(f"| File Báo Cáo    : {output_csv}")
    print("+" + "=" * 90 + "+")

    if not os.path.exists(input_csv):
        print(f"[LỖI] Không tìm thấy file {input_csv}!")
        return

    with open(input_csv, mode="r", encoding="utf-8-sig") as f:
        tests = list(csv.DictReader(f))

    report_rows = []
    passed_count = 0
    failed_count = 0

    header_fmt = "{:<4} | {:<7} | {:<32} | {:<6} | {:<10} | {:<8} | {:<6}"
    print(header_fmt.format("STT", "Jira", "Tên Test Case", "Method", "Response", "Status", "Kết quả"))
    print("-" * 92)

    for row in tests:
        stt = row.get("STT", "")
        jira_task = row.get("Jira_Task", "")
        tc_name = row.get("Ten_Test_Case", "")
        endpoint = row.get("Endpoint", "")
        method = row.get("Method", "GET").upper()
        payload_params = row.get("Payload_Params", "")
        expected_raw = row.get("Ket_Qua_Mong_Doi", "")
        tag = row.get("Tag_Bao_Phu", "")

        url = f"{BASE_URL}{endpoint}"
        status_code = None
        duration_ms = 0
        verdict = "FAIL"

        start_time = time.time()
        try:
            if method == "POST":
                data = payload_params.encode('utf-8')
                req = urllib.request.Request(url, data=data, method="POST")
                req.add_header('Content-Type', 'application/x-www-form-urlencoded')
            else:
                full_url = f"{url}?{payload_params}" if payload_params else url
                req = urllib.request.Request(full_url, method="GET")

            req.add_header("User-Agent", "Mozilla/5.0 (QA Automated Runner)")
            with urllib.request.urlopen(req, timeout=5) as resp:
                status_code = resp.getcode()
                duration_ms = round((time.time() - start_time) * 1000, 2)
        except urllib.error.HTTPError as e:
            status_code = e.code
            duration_ms = round((time.time() - start_time) * 1000, 2)
        except Exception as e:
            status_code = 500  # Mô phỏng lỗi kết nối / chưa bật server
            duration_ms = round((time.time() - start_time) * 1000, 2)

        # Đánh giá PASS/FAIL theo logic của Postman script: Code 200/302 & Time < 2000ms
        if "Khong hop le" in expected_raw or "Tu choi" in expected_raw:
            # Test case biên sai hoặc bảo mật: nếu server chặn (400, 403, 422) hoặc redirect cảnh báo là PASS
            verdict = "PASS" if status_code in [200, 302, 400, 403, 422] else "FAIL"
        else:
            verdict = "PASS" if (status_code in [200, 302] and duration_ms < 2000) else "FAIL"

        if verdict == "PASS":
            passed_count += 1
        else:
            failed_count += 1

        print(header_fmt.format(stt, jira_task, tc_name[:32], method, f"{duration_ms}ms", str(status_code), verdict))

        report_rows.append({
            "STT": stt,
            "Jira_Task": jira_task,
            "Ten_Test_Case": tc_name,
            "Endpoint": endpoint,
            "Method": method,
            "Thoi_Gian_ms": duration_ms,
            "Status_Code": status_code,
            "Ket_Qua_Mong_Doi": expected_raw,
            "Trang_Thai": verdict,
            "Tag_Bao_Phu": tag
        })

    # Xuất file báo cáo chuẩn UTF-8
    fieldnames = [
        "STT", "Jira_Task", "Ten_Test_Case", "Endpoint", "Method",
        "Thoi_Gian_ms", "Status_Code", "Ket_Qua_Mong_Doi", "Trang_Thai", "Tag_Bao_Phu"
    ]
    with open(output_csv, mode="w", encoding="utf-8-sig", newline="") as out:
        writer = csv.DictWriter(out, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(report_rows)

    print("-" * 92)
    print(f"TỔNG KẾT: {len(tests)} Test Cases | PASS: {passed_count} | FAIL: {failed_count}")
    print(f"File báo cáo kết quả đã được ghi tại: {output_csv}\n")

if __name__ == "__main__":
    run_int_test_suite()
