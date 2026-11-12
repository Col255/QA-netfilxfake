---
name: bmad-srs-generator
description: Áp dụng phương pháp BMAD Method để tự động trích xuất và sinh tài liệu đặc tả yêu cầu phần mềm (SRS - Software Requirements Specification) từ mã nguồn và CSDL có sẵn theo chuẩn IEEE 830 / ISO 29148.
---

# BMAD SRS GENERATOR FOR NETFIX2

## 1. Mục tiêu (Objective):
Sử dụng nguyên lý BMAD (Business - Model - Architecture - Development) để dịch ngược (Reverse-Engineer) mã nguồn PHP (`netfix2/`) và cơ sở dữ liệu (`database/netfixvn.sql`) thành tài liệu SRS chuyên nghiệp phục vụ kiểm thử QA.

## 2. Quy tắc sinh yêu cầu (BMAD Principles):
1. **Traceable ID**: Mọi yêu cầu bắt buộc phải có mã định danh duy nhất (VD: `REQ-AUTH-01`, `REQ-MOV-02`, `REQ-VIP-03`, `REQ-ADM-04`).
2. **Actor-driven**: Phải xác định rõ tác nhân (Guest, Registered User, VIP User, Administrator).
3. **Structured Specification**: Mỗi chức năng phải có đủ 5 thành phần:
   - **Mục tiêu (Objective)**
   - **Dữ liệu đầu vào (Input & Data validation)**
   - **Quy tắc nghiệp vụ (Business Rules / Logic)**
   - **Kết quả trả về (Expected Output / Success Flow)**
   - **Kịch bản ngoại lệ & Báo lỗi (Exception / Alternative Flows)**
4. **Measurable NFRs**: Yêu cầu phi chức năng phải có chỉ số đo lường cụ thể (thời gian tính bằng giây/ms, giao thức, thuật toán mã hóa).

## 3. Cấu trúc tài liệu đầu ra:
Tài liệu sinh ra phải tuân thủ chuẩn IEEE 830:
- Chương 1: Giới thiệu & Phạm vi (Scope, Purpose, Definitions)
- Chương 2: Mô tả tổng thể & Ma trận tác nhân (Actors & Role Matrix)
- Chương 3: Đặc tả chi tiết yêu cầu chức năng (Chi tiết từng REQ-ID)
- Chương 4: Đặc tả yêu cầu phi chức năng (Performance, Security, Reliability)
- Chương 5: Từ điển dữ liệu & Sơ đồ quan hệ thực thể (ERD)