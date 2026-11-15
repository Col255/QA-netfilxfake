import os
import xml.etree.ElementTree as ET
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def create_document():
    doc = docx.Document()
    
    # Page setup - Margins 1 inch (72pt)
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)
        
        # Header & Footer
        footer = section.footer
        p_ft = footer.paragraphs[0]
        p_ft.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        f_run = p_ft.add_run("Báo cáo cấu hình mạng Cisco Packet Tracer | SV: Lê Hoàng Nhật Bình - 086205004972")
        f_run.font.name = "Calibri"
        f_run.font.size = Pt(8.5)
        f_run.font.color.rgb = RGBColor(0x94, 0xA3, 0xB8)

    # Styles & Colors
    # Primary: #0F4C81 (Navy), Secondary: #1E88E5 (Blue), Dark: #1E293B
    
    def set_cell_background(cell, hex_color):
        shading_elm = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
        cell._tc.get_or_add_tcPr().append(shading_elm)

    def set_cell_margins(cell, top=100, bottom=100, left=140, right=140):
        tcPr = cell._tc.get_or_add_tcPr()
        tcMar = OxmlElement('w:tcMar')
        for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
            node = OxmlElement(f'w:{m}')
            node.set(qn('w:w'), str(val))
            node.set(qn('w:type'), 'dxa')
            tcMar.append(node)
        tcPr.append(tcMar)

    def set_table_borders(table, color="D1D5DB", sz="4", val="single"):
        tblPr = table._tbl.tblPr
        borders = parse_xml(
            f'<w:tblBorders {nsdecls("w")}>'
            f'  <w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'  <w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'  <w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'  <w:insideV w:val="none"/>'
            f'  <w:left w:val="none"/>'
            f'  <w:right w:val="none"/>'
            f'</w:tblBorders>'
        )
        tblPr.append(borders)

    def format_styled_table(table, col_widths, headers, data, align_cols=None):
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        table.autofit = False
        set_table_borders(table)
        
        # Header Row
        hdr_cells = table.rows[0].cells
        for i, title in enumerate(headers):
            cell = hdr_cells[i]
            cell.text = title
            cell.width = col_widths[i]
            set_cell_background(cell, "0F4C81")
            set_cell_margins(cell, top=120, bottom=120, left=120, right=120)
            p = cell.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            run = p.runs[0]
            run.font.name = "Calibri"
            run.font.size = Pt(9.5)
            run.font.bold = True
            run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
            
        # Data Rows
        for r_idx, row_data in enumerate(data):
            row_cells = table.add_row().cells
            bg_color = "F8FAFC" if r_idx % 2 == 1 else "FFFFFF"
            for c_idx, val in enumerate(row_data):
                cell = row_cells[c_idx]
                cell.text = str(val)
                cell.width = col_widths[c_idx]
                set_cell_background(cell, bg_color)
                set_cell_margins(cell, top=90, bottom=90, left=120, right=120)
                p = cell.paragraphs[0]
                if align_cols and c_idx in align_cols:
                    p.alignment = align_cols[c_idx]
                else:
                    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
                run = p.runs[0]
                run.font.name = "Calibri"
                run.font.size = Pt(9)
                run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)
        
        # Add space after table
        p_sp = doc.add_paragraph()
        p_sp.paragraph_format.space_before = Pt(2)
        p_sp.paragraph_format.space_after = Pt(6)

    def add_h1(text):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(16)
        p.paragraph_format.space_after = Pt(6)
        p.paragraph_format.keep_with_next = True
        run = p.add_run(text)
        run.font.name = "Calibri"
        run.font.size = Pt(15)
        run.font.bold = True
        run.font.color.rgb = RGBColor(0x0F, 0x4C, 0x81)

    def add_h2(text):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.keep_with_next = True
        run = p.add_run(text)
        run.font.name = "Calibri"
        run.font.size = Pt(12.5)
        run.font.bold = True
        run.font.color.rgb = RGBColor(0x1E, 0x88, 0xE5)

    def add_h3(text):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(8)
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.keep_with_next = True
        run = p.add_run(text)
        run.font.name = "Calibri"
        run.font.size = Pt(11)
        run.font.bold = True
        run.font.color.rgb = RGBColor(0x1E, 0x29, 0x3B)

    def add_p(text, bold_prefix="", italic=False):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.line_spacing = 1.15
        if bold_prefix:
            r_pre = p.add_run(bold_prefix)
            r_pre.font.name = "Calibri"
            r_pre.font.size = Pt(10.5)
            r_pre.font.bold = True
            r_pre.font.color.rgb = RGBColor(0x0F, 0x4C, 0x81)
        run = p.add_run(text)
        run.font.name = "Calibri"
        run.font.size = Pt(10.5)
        run.font.italic = italic
        run.font.color.rgb = RGBColor(0x33, 0x41, 0x55)
        return p

    def add_code_block(code_text):
        tbl = doc.add_table(rows=1, cols=1)
        tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        tbl.autofit = False
        
        cell = tbl.cell(0, 0)
        cell.width = Inches(6.5)
        set_cell_background(cell, "F8FAFC")
        set_cell_margins(cell, top=100, bottom=100, left=150, right=150)
        
        tcPr = cell._tc.get_or_add_tcPr()
        borders = parse_xml(
            f'<w:tcBorders {nsdecls("w")}>'
            f'  <w:left w:val="single" w:sz="24" w:space="0" w:color="0F4C81"/>'
            f'  <w:top w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>'
            f'  <w:bottom w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>'
            f'  <w:right w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>'
            f'</w:tcBorders>'
        )
        tcPr.append(borders)
        
        p = cell.paragraphs[0]
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.line_spacing = 1.15
        
        lines = code_text.split('\n')
        for i, line in enumerate(lines):
            run = p.add_run(line + ('\n' if i < len(lines)-1 else ''))
            run.font.name = 'Consolas'
            run.font.size = Pt(8.5)
            # Syntax color hints
            if line.strip().startswith('!'):
                run.font.color.rgb = RGBColor(0x94, 0xA3, 0xB8)
            elif line.strip().startswith('interface ') or line.strip().startswith('router ') or line.strip().startswith('ip nat ') or line.strip().startswith('ip route'):
                run.font.color.rgb = RGBColor(0x0F, 0x4C, 0x81)
            elif line.strip().startswith('hostname '):
                run.font.color.rgb = RGBColor(0xB9, 0x1C, 0x1C)
            else:
                run.font.color.rgb = RGBColor(0x1E, 0x29, 0x3B)
        
        p_after = doc.add_paragraph()
        p_after.paragraph_format.space_before = Pt(0)
        p_after.paragraph_format.space_after = Pt(4)

    def add_callout(title, text):
        tbl = doc.add_table(rows=1, cols=1)
        tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        tbl.autofit = False
        
        cell = tbl.cell(0, 0)
        cell.width = Inches(6.5)
        set_cell_background(cell, "EFF6FF")
        set_cell_margins(cell, top=120, bottom=120, left=150, right=150)
        
        tcPr = cell._tc.get_or_add_tcPr()
        borders = parse_xml(
            f'<w:tcBorders {nsdecls("w")}>'
            f'  <w:left w:val="single" w:sz="24" w:space="0" w:color="2563EB"/>'
            f'  <w:top w:val="single" w:sz="4" w:space="0" w:color="DBEAFE"/>'
            f'  <w:bottom w:val="single" w:sz="4" w:space="0" w:color="DBEAFE"/>'
            f'  <w:right w:val="single" w:sz="4" w:space="0" w:color="DBEAFE"/>'
            f'</w:tcBorders>'
        )
        tcPr.append(borders)
        
        p = cell.paragraphs[0]
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(2)
        
        run_t = p.add_run(f'💡 {title}\n')
        run_t.font.name = 'Calibri'
        run_t.font.size = Pt(10.5)
        run_t.font.bold = True
        run_t.font.color.rgb = RGBColor(0x1D, 0x4E, 0xD8)
        
        run_body = p.add_run(text)
        run_body.font.name = 'Calibri'
        run_body.font.size = Pt(10)
        run_body.font.color.rgb = RGBColor(0x1E, 0x29, 0x3B)
        
        p_after = doc.add_paragraph()
        p_after.paragraph_format.space_before = Pt(0)
        p_after.paragraph_format.space_after = Pt(4)

    # -------------------------------------------------------------
    # DOCUMENT HEADER / BANNER
    # -------------------------------------------------------------
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_before = Pt(10)
    p_title.paragraph_format.space_after = Pt(2)
    r_main_title = p_title.add_run("BÁO CÁO CẤU HÌNH THIẾT BỊ MẠNG CISCO")
    r_main_title.font.name = "Calibri"
    r_main_title.font.size = Pt(22)
    r_main_title.font.bold = True
    r_main_title.font.color.rgb = RGBColor(0x0F, 0x4C, 0x81)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_before = Pt(0)
    p_sub.paragraph_format.space_after = Pt(16)
    r_sub = p_sub.add_run("MÔ HÌNH THỰC HÀNH LAB CISCO PACKET TRACER")
    r_sub.font.name = "Calibri"
    r_sub.font.size = Pt(13)
    r_sub.font.color.rgb = RGBColor(0x64, 0x74, 0x8B)

    # Student Info Box (Table)
    info_tbl = doc.add_table(rows=5, cols=2)
    info_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    info_tbl.autofit = False
    set_table_borders(info_tbl, color="E2E8F0")
    
    info_widths = [Inches(2.2), Inches(4.3)]
    info_data = [
        ("Họ và tên sinh viên:", "Lê Hoàng Nhật Bình"),
        ("Mã số sinh viên (CCCD):", "086205004972"),
        ("Tên tệp tin Packet Tracer (.pkt):", "086205004972_Lê_Hoàng_Nhật_Bình.pkt"),
        ("Đơn vị / Cơ sở đào tạo:", "Trường Đại học Giao thông Vận tải TP.HCM (UTH)"),
        ("Ngày trích xuất cấu hình:", "14/11/2026")
    ]
    for idx, (lbl, val) in enumerate(info_data):
        c0 = info_tbl.rows[idx].cells[0]
        c1 = info_tbl.rows[idx].cells[1]
        c0.width = info_widths[0]
        c1.width = info_widths[1]
        c0.text = lbl
        c1.text = val
        set_cell_background(c0, "F1F5F9")
        set_cell_background(c1, "FFFFFF")
        set_cell_margins(c0, top=70, bottom=70, left=100, right=100)
        set_cell_margins(c1, top=70, bottom=70, left=100, right=100)
        
        p0 = c0.paragraphs[0]
        r0 = p0.runs[0]
        r0.font.name = "Calibri"
        r0.font.size = Pt(10)
        r0.font.bold = True
        r0.font.color.rgb = RGBColor(0x0F, 0x4C, 0x81)
        
        p1 = c1.paragraphs[0]
        r1 = p1.runs[0]
        r1.font.name = "Calibri"
        r1.font.size = Pt(10)
        r1.font.bold = (idx < 2)
        r1.font.color.rgb = RGBColor(0x1E, 0x29, 0x3B)

    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.space_before = Pt(4)
    p_sp.paragraph_format.space_after = Pt(8)

    # -------------------------------------------------------------
    # SECTION 1: TỔNG QUAN HỆ THỐNG VÀ MÔ HÌNH KẾT NỐI
    # -------------------------------------------------------------
    add_h1("1. TỔNG QUAN HỆ THỐNG VÀ MÔ HÌNH KẾT NỐI (TOPOLOGY)")
    add_p(
        "Mô hình mạng được thiết kế và triển khai trên phần mềm mô phỏng Cisco Packet Tracer. "
        "Hệ thống bao gồm 02 bộ định tuyến Cisco 2811 đóng vai trò Router biên kết nối mạng nội bộ và Router ngoại mạng/ISP, "
        "01 Switch Cisco 2960-24TT phân chia các mạng ảo (VLAN), 01 máy chủ dịch vụ Web/DNS Server và 03 máy trạm máy khách (PC).",
        bold_prefix="Mô tả chung: "
    )
    
    add_h2("1.1. Danh mục các thiết bị trong sơ đồ mạng")
    dev_widths = [Inches(0.6), Inches(1.8), Inches(1.6), Inches(1.2), Inches(1.3)]
    dev_headers = ["STT", "Tên thiết bị (Logical)", "Hostname cấu hình", "Chủng loại / Model", "Chức năng hệ thống"]
    dev_data = [
        ["1", "Router0", "LeHoangNhatBinh1", "Cisco 2811", "Router biên, NAT, Inter-VLAN"],
        ["2", "Router1", "LeHoangNhatBinh2", "Cisco 2811", "Router ngoại mạng / ISP Gateway"],
        ["3", "Switch0", "Switch0", "Cisco 2960-24TT", "Switch truy cập, VLAN 2, VLAN 3"],
        ["4", "Server0", "PC (Server0)", "Server", "Web Server (HTTP) & DNS Server"],
        ["5", "PC0", "PC (PC0)", "Desktop PC", "Máy trạm VLAN 2"],
        ["6", "PC1", "PC (PC1)", "Desktop PC", "Máy trạm VLAN 2"],
        ["7", "PC2", "PC (PC2)", "Desktop PC", "Máy trạm VLAN 3"]
    ]
    tbl_dev = doc.add_table(rows=1, cols=5)
    format_styled_table(tbl_dev, dev_widths, dev_headers, dev_data, align_cols={0: WD_ALIGN_PARAGRAPH.CENTER})

    add_h2("1.2. Bảng ánh xạ kết nối cáp vật lý (Cabling Map)")
    link_widths = [Inches(0.5), Inches(1.8), Inches(1.8), Inches(1.2), Inches(1.2)]
    link_headers = ["STT", "Cổng thiết bị đầu A", "Cổng thiết bị đầu B", "Loại cáp mạng", "Mục đích kết nối"]
    link_data = [
        ["1", "Router0 (FastEthernet0/0)", "Switch0 (FastEthernet0/1)", "Cáp thẳng (Straight)", "Trunking Router-on-a-Stick"],
        ["2", "Router0 (FastEthernet0/1)", "Router1 (FastEthernet0/0)", "Cáp chéo (Crossover)", "Liên kết WAN định tuyến tĩnh"],
        ["3", "Router1 (FastEthernet0/1)", "Server0 (FastEthernet0)", "Cáp chéo (Crossover)", "Kết nối mạng máy chủ Server"],
        ["4", "Switch0 (FastEthernet0/6)", "PC1 (FastEthernet0)", "Cáp thẳng (Straight)", "Access port thuộc VLAN 2"],
        ["5", "Switch0 (FastEthernet0/7)", "PC0 (FastEthernet0)", "Cáp thẳng (Straight)", "Access port thuộc VLAN 2"],
        ["6", "Switch0 (FastEthernet0/11)", "PC2 (FastEthernet0)", "Cáp thẳng (Straight)", "Access port thuộc VLAN 3"]
    ]
    tbl_link = doc.add_table(rows=1, cols=5)
    format_styled_table(tbl_link, link_widths, link_headers, link_data, align_cols={0: WD_ALIGN_PARAGRAPH.CENTER})

    add_h2("1.3. Phân hoạch mạng logic (VLAN & Subnets)")
    add_p("Hệ thống mạng được phân chia thành 4 dải mạng logic riêng biệt nhằm tối ưu hóa lưu lượng và kiểm soát an ninh:")
    add_p("• Mạng VLAN 2: Subnet 192.168.3.0/24 (Dành cho nhóm máy trạm PC1, PC0 và SVI quản trị Switch0).")
    add_p("• Mạng VLAN 3: Subnet 192.168.4.0/24 (Dành cho nhóm máy trạm PC2, phân quyền quản trị VTY vào Router).")
    add_p("• Mạng WAN (R1 - R2): Subnet 209.165.210.4/30 (Liên kết điểm - điểm Point-to-Point giữa hai Router).")
    add_p("• Mạng Server / Public: Subnet 8.8.8.0/24 (Vùng mạng chứa Server0 cung cấp dịch vụ Web và DNS).")

    # -------------------------------------------------------------
    # SECTION 2: BẢNG PHÂN BỔ ĐỊA CHỈ IP (IP ADDRESSING TABLE)
    # -------------------------------------------------------------
    add_h1("2. BẢNG PHÂN BỔ ĐỊA CHỈ IP (IP ADDRESSING TABLE)")
    add_p("Bảng phân bổ chi tiết toàn bộ địa chỉ IPv4, Subnet Mask, Gateway và DNS Server cấu hình trong mô hình:")

    ip_widths = [Inches(1.3), Inches(1.1), Inches(1.1), Inches(1.1), Inches(1.0), Inches(0.9)]
    ip_headers = ["Thiết bị", "Giao tiếp (Port)", "Địa chỉ IP", "Subnet Mask", "Default Gateway", "DNS Server"]
    ip_data = [
        ["LeHoangNhatBinh1\n(Router0)", "Fa0/0", "Không gán IP", "N/A", "N/A", "N/A"],
        ["LeHoangNhatBinh1\n(Router0)", "Fa0/0.2 (Sub-if)", "192.168.3.1", "255.255.255.0", "N/A", "N/A"],
        ["LeHoangNhatBinh1\n(Router0)", "Fa0/0.3 (Sub-if)", "192.168.4.1", "255.255.255.0", "N/A", "N/A"],
        ["LeHoangNhatBinh1\n(Router0)", "Fa0/1", "209.165.210.5", "255.255.255.252", "N/A", "N/A"],
        ["LeHoangNhatBinh2\n(Router1)", "Fa0/0", "209.165.210.6", "255.255.255.252", "N/A", "N/A"],
        ["LeHoangNhatBinh2\n(Router1)", "Fa0/1", "8.8.8.7", "255.255.255.0", "N/A", "N/A"],
        ["Switch0", "Vlan2 (SVI)", "192.168.3.2", "255.255.255.0", "192.168.3.1", "N/A"],
        ["Server0", "FastEthernet0", "8.8.8.8", "255.255.255.0", "8.8.8.7", "8.8.8.8"],
        ["PC0", "FastEthernet0", "192.168.3.11", "255.255.255.0", "192.168.3.1", "8.8.8.8"],
        ["PC1", "FastEthernet0", "192.168.3.10", "255.255.255.0", "192.168.3.1", "8.8.8.8"],
        ["PC2", "FastEthernet0", "192.168.4.10", "255.255.255.0", "192.168.4.1", "8.8.8.8"]
    ]
    tbl_ip = doc.add_table(rows=1, cols=6)
    format_styled_table(tbl_ip, ip_widths, ip_headers, ip_data, align_cols={1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER})

    # -------------------------------------------------------------
    # SECTION 3: PHÂN TÍCH VÀ ĐẶC TẢ KỸ THUẬT CẤU HÌNH
    # -------------------------------------------------------------
    add_h1("3. PHÂN TÍCH VÀ ĐẶC TẢ KỸ THUẬT CẤU HÌNH TRỌNG TÂM")
    
    add_h2("3.1. Kỹ thuật định tuyến liên mạng VLAN (Router-on-a-Stick)")
    add_p(
        "Router0 (LeHoangNhatBinh1) sử dụng 1 cổng vật lý FastEthernet0/0 kết nối đến cổng Trunk Fa0/1 của Switch0. "
        "Cổng vật lý Fa0/0 được chia thành các cổng logic con (Sub-interfaces) sử dụng chuẩn đóng gói IEEE 802.1Q:"
    )
    add_p("• Fa0/0.2: Đóng gói encapsulation dot1Q 2, nhận IP 192.168.3.1/24 đóng vai trò Default Gateway cho VLAN 2.")
    add_p("• Fa0/0.3: Đóng gói encapsulation dot1Q 3, nhận IP 192.168.4.1/24 đóng vai trò Default Gateway cho VLAN 3.")
    add_callout(
        "Nguyên lý Router-on-a-Stick",
        "Khi PC thuộc VLAN 2 gửi gói tin đến PC thuộc VLAN 3, gói tin sẽ được gắn thẻ VLAN 2 chuyển qua đường Trunk lên Router0 Fa0/0.2, "
        "Router0 gỡ nhãn, tra bảng định tuyến, chuyển tiếp sang Fa0/0.3, đóng nhãn VLAN 3 và gửi ngược lại Switch0 để chuyển tới đích."
    )

    add_h2("3.2. Dịch vụ biên dịch địa chỉ mạng (NAT Overload / PAT)")
    add_p(
        "Để các máy tính mạng nội bộ (Private IP) có thể truy cập ra mạng bên ngoài và máy chủ Server0, "
        "Router0 được cấu hình kỹ thuật Port Address Translation (PAT - NAT Overload):"
    )
    add_p("• NAT Inside: Được kích hoạt trên 2 cổng logic nội bộ: interface FastEthernet0/0.2 và FastEthernet0/0.3 (ip nat inside).")
    add_p("• NAT Outside: Được kích hoạt trên cổng hướng ngoại: interface FastEthernet0/1 (ip nat outside).")
    add_p("• Lệnh NAT: ip nat inside source list 1 interface FastEthernet0/1 overload")
    add_p("• Access-list 1: access-list 1 permit 192.168.3.0 0.0.0.255 xác định dải mạng VLAN 2 được phép biên dịch địa chỉ IP.")

    add_h2("3.3. Định tuyến mặc định (Default Static Route)")
    add_p("• Trên Router0 (LeHoangNhatBinh1), cấu hình đường định tuyến tĩnh mặc định trỏ ra Next-hop Router 1:")
    add_p("  ip route 0.0.0.0 0.0.0.0 209.165.210.6")
    add_p("• Nhờ lệnh này, mọi lưu lượng không nằm trong bảng định tuyến nội bộ của Router0 đều sẽ được chuyển tiếp sang Router1.")

    add_h2("3.4. Kiểm soát an ninh và bảo mật quản trị (Access-List & Security)")
    add_p("Hệ thống triển khai các chính sách bảo mật quản trị nghiêm ngặt:")
    add_p("• Access-list 10: access-list 10 permit 192.168.4.0 0.0.0.255")
    add_p("• Giới hạn truy cập Line VTY: Trên Router0, cấu hình line vty 0 4 đi kèm lệnh access-class 10 in. "
          "Chính sách này chỉ cho phép duy nhất các máy trạm thuộc dải mạng VLAN 3 (192.168.4.0/24, cụ thể là PC2) được phép Telnet/quản trị Router. "
          "Các máy trạm thuộc VLAN 2 (PC0, PC1) bị chặn hoàn toàn khi cố gắng kết nối quản trị.")
    add_p("• Mã hóa mật khẩu: Lệnh service password-encryption được bật trên toàn bộ các thiết bị mạng Cisco để bảo vệ mật khẩu lưu trong cấu hình.")
    add_p("• Mật khẩu Privileged EXEC (Enable Secret): Sử dụng thuật toán MD5 mã hóa mức độ cao ($1$mERr$3HhIgMGBA/9qNmgzccuxv0).")
    add_p("• Mật khẩu đường truyền VTY (Telnet): Được mã hóa Type 7: password 7 08701E1D (Mật khẩu nguyên bản giải mã được là: 123).")
    add_p("• Thông điệp cảnh báo truy cập (Banner MOTD): Được thiết lập trên từng thiết bị (#LeHoangNhatBinh1#, #LeHoangNhatBinh2#, #Switch0#).")

    add_h2("3.5. Cấu hình phân chia mạng ảo trên Switch0 (VLAN & Trunking)")
    add_p("• Cổng kết nối Router Fa0/1: Cấu hình switchport mode trunk để truyền tải lưu lượng nhiều VLAN.")
    add_p("• Dải cổng Fa0/5 - Fa0/10: Gán vào VLAN 2 (switchport access vlan 2) kết nối PC1 (Fa0/6) và PC0 (Fa0/7).")
    add_p("• Dải cổng Fa0/11 - Fa0/16: Gán vào VLAN 3 (switchport access vlan 3) kết nối PC2 (Fa0/11).")
    add_p("• Quản trị từ xa cho Switch: Khởi tạo Interface Vlan2 nhận IP 192.168.3.2/24 và cấu hình ip default-gateway 192.168.3.1.")

    add_h2("3.6. Cấu hình dịch vụ máy chủ Server0 (DNS & Web Server)")
    add_p("• Dịch vụ Web Server (HTTP/HTTPS): Đang hoạt động (Enabled: 1) phục vụ trang web cho mạng.")
    add_p("• Dịch vụ DNS Server: Đang hoạt động (Enabled: 1). Đã khai báo bản ghi phân giải tên miền A-Record:")
    add_p("  + Tên miền (Domain Name): uth.com")
    add_p("  + Địa chỉ IP phân giải: 8.8.8.8")
    add_p("  + Thời gian sống (TTL): 86400 giây (24 giờ)")

    # -------------------------------------------------------------
    # SECTION 4: TOÀN VĂN FILE CẤU HÌNH THIẾT BỊ (RUNNING-CONFIG)
    # -------------------------------------------------------------
    add_h1("4. TOÀN VĂN FILE CẤU HÌNH THIẾT BỊ (RUNNING-CONFIG)")
    
    # 4.1 Router 0
    add_h2("4.1. Cấu hình Router0 - Hostname: LeHoangNhatBinh1 (Cisco 2811)")
    add_p("Dưới đây là toàn văn running-config trích xuất nguyên bản từ tệp tin Cisco Packet Tracer của Router0:")
    r0_config = """!
version 15.1
no service timestamps log datetime msec
no service timestamps debug datetime msec
service password-encryption
!
hostname LeHoangNhatBinh1
!
enable secret 5 $1$mERr$3HhIgMGBA/9qNmgzccuxv0
!
ip cef
no ipv6 cef
!
license udi pid CISCO2811/K9 sn FTX101749Z9-
!
no ip domain-lookup
!
spanning-tree mode pvst
!
interface FastEthernet0/0
 no ip address
 duplex auto
 speed auto
!
interface FastEthernet0/0.2
 description Gateway VLAN 2 noi Switch0
 encapsulation dot1Q 2
 ip address 192.168.3.1 255.255.255.0
 ip nat inside
!
interface FastEthernet0/0.3
 description Gateway VLAN 3 noi Switch0
 encapsulation dot1Q 3
 ip address 192.168.4.1 255.255.255.0
 ip nat inside
!
interface FastEthernet0/1
 description Noi R2 Fa0/0
 ip address 209.165.210.5 255.255.255.252
 ip nat outside
 duplex auto
 speed auto
!
interface Vlan1
 no ip address
 shutdown
!
ip nat inside source list 1 interface FastEthernet0/1 overload
ip classless
ip route 0.0.0.0 0.0.0.0 209.165.210.6 
!
ip flow-export version 9
!
access-list 10 permit 192.168.4.0 0.0.0.255
access-list 1 permit 192.168.3.0 0.0.0.255
!
banner motd #LeHoangNhatBinh1#
!
line con 0
 logging synchronous
!
line aux 0
!
line vty 0 4
 access-class 10 in
 password 7 08701E1D
 login
!
end"""
    add_code_block(r0_config)

    # 4.2 Router 1
    add_h2("4.2. Cấu hình Router1 - Hostname: LeHoangNhatBinh2 (Cisco 2811)")
    add_p("Toàn văn running-config trích xuất nguyên bản từ Router1:")
    r1_config = """!
version 15.1
no service timestamps log datetime msec
no service timestamps debug datetime msec
service password-encryption
!
hostname LeHoangNhatBinh2
!
enable secret 5 $1$mERr$3HhIgMGBA/9qNmgzccuxv0
!
ip cef
no ipv6 cef
!
license udi pid CISCO2811/K9 sn FTX1017ZBOD-
!
no ip domain-lookup
!
spanning-tree mode pvst
!
interface FastEthernet0/0
 description Noi R1 Fa0/1
 ip address 209.165.210.6 255.255.255.252
 duplex auto
 speed auto
!
interface FastEthernet0/1
 description Noi Webserver Fa0
 ip address 8.8.8.7 255.255.255.0
 duplex auto
 speed auto
!
interface Vlan1
 no ip address
 shutdown
!
ip classless
!
ip flow-export version 9
!
banner motd #LeHoangNhatBinh2#
!
line con 0
 logging synchronous
!
line aux 0
!
line vty 0 4
 password 7 08701E1D
 login
!
end"""
    add_code_block(r1_config)

    # 4.3 Switch 0
    add_h2("4.3. Cấu hình Switch0 - Hostname: Switch0 (Cisco Catalyst 2960)")
    add_p("Toàn văn running-config trích xuất nguyên bản từ Switch0:")
    sw_config = """!
version 15.0
no service timestamps log datetime msec
no service timestamps debug datetime msec
service password-encryption
!
hostname Switch0
!
enable secret 5 $1$mERr$3HhIgMGBA/9qNmgzccuxv0
!
no ip domain-lookup
!
spanning-tree mode pvst
spanning-tree extend system-id
!
interface FastEthernet0/1
 description Noi R1 Fa0/0
 switchport mode trunk
!
interface FastEthernet0/2
!
interface FastEthernet0/3
!
interface FastEthernet0/4
!
interface FastEthernet0/5
 switchport access vlan 2
 switchport mode access
!
interface FastEthernet0/6
 switchport access vlan 2
 switchport mode access
!
interface FastEthernet0/7
 switchport access vlan 2
 switchport mode access
!
interface FastEthernet0/8
 switchport access vlan 2
 switchport mode access
!
interface FastEthernet0/9
 switchport access vlan 2
 switchport mode access
!
interface FastEthernet0/10
 switchport access vlan 2
 switchport mode access
!
interface FastEthernet0/11
 switchport access vlan 3
 switchport mode access
!
interface FastEthernet0/12
 switchport access vlan 3
 switchport mode access
!
interface FastEthernet0/13
 switchport access vlan 3
 switchport mode access
!
interface FastEthernet0/14
 switchport access vlan 3
 switchport mode access
!
interface FastEthernet0/15
 switchport access vlan 3
 switchport mode access
!
interface FastEthernet0/16
 switchport access vlan 3
 switchport mode access
!
interface FastEthernet0/17
!
interface FastEthernet0/18
!
interface FastEthernet0/19
!
interface FastEthernet0/20
!
interface FastEthernet0/21
!
interface FastEthernet0/22
!
interface FastEthernet0/23
!
interface FastEthernet0/24
!
interface GigabitEthernet0/1
!
interface GigabitEthernet0/2
!
interface Vlan1
 no ip address
 shutdown
!
interface Vlan2
 ip address 192.168.3.2 255.255.255.0
!
ip default-gateway 192.168.3.1
!
banner motd #Switch0#
!
line con 0
 logging synchronous
!
line vty 0 4
 password 7 08701E1D
 login
line vty 5 15
 login
!
end"""
    add_code_block(sw_config)

    # 4.4 Server 0
    add_h2("4.4. Cấu hình chi tiết máy chủ Server0 (Web & DNS Server)")
    add_p("Thông số mạng và các dịch vụ cấu hình trên máy chủ Server0:")
    srv_widths = [Inches(2.2), Inches(4.3)]
    srv_headers = ["Thông số / Dịch vụ", "Giá trị cấu hình chi tiết"]
    srv_data = [
        ["Tên thiết bị (Label)", "Server0 (SYS_NAME: PC)"],
        ["Cổng giao tiếp mạng", "FastEthernet0 (nối Router1 Fa0/1)"],
        ["Địa chỉ IP (IP Address)", "8.8.8.8"],
        ["Mặt nạ mạng (Subnet Mask)", "255.255.255.0 (/24)"],
        ["Cổng mặc định (Default Gateway)", "8.8.8.7"],
        ["Máy chủ DNS (DNS Server)", "8.8.8.8 (chính máy chủ này)"],
        ["Dịch vụ HTTP / HTTPS", "Trạng thái: BẬT (Enabled: 1, Port 80 / 443)"],
        ["Dịch vụ DNS Server", "Trạng thái: BẬT (Enabled: 1)"],
        ["Bản ghi phân giải tên miền (DNS Record)", "A-Record: uth.com -> 8.8.8.8 (TTL: 86400s)"]
    ]
    tbl_srv = doc.add_table(rows=1, cols=2)
    format_styled_table(tbl_srv, srv_widths, srv_headers, srv_data)

    # 4.5 PCs
    add_h2("4.5. Cấu hình chi tiết các máy trạm khách (PC0, PC1, PC2)")
    add_p("Bảng cấu hình card mạng và thông số IP gán tĩnh (Static IP) trên các máy tính người dùng:")
    pc_widths = [Inches(1.1), Inches(1.0), Inches(1.2), Inches(1.1), Inches(1.1), Inches(1.0)]
    pc_headers = ["Máy trạm", "VLAN", "Địa chỉ IPv4", "Subnet Mask", "Default Gateway", "DNS Server"]
    pc_tbl_data = [
        ["PC0", "VLAN 2", "192.168.3.11", "255.255.255.0", "192.168.3.1", "8.8.8.8"],
        ["PC1", "VLAN 2", "192.168.3.10", "255.255.255.0", "192.168.3.1", "8.8.8.8"],
        ["PC2", "VLAN 3", "192.168.4.10", "255.255.255.0", "192.168.4.1", "8.8.8.8"]
    ]
    tbl_pc = doc.add_table(rows=1, cols=6)
    format_styled_table(tbl_pc, pc_widths, pc_headers, pc_tbl_data, align_cols={1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER})

    # -------------------------------------------------------------
    # SECTION 5: KIỂM TRA VÀ ĐÁNH GIÁ KẾT QUẢ HOẠT ĐỘNG
    # -------------------------------------------------------------
    add_h1("5. BẢNG KIỂM TRA VÀ ĐÁNH GIÁ KẾT QUẢ HOẠT ĐỘNG (VERIFICATION)")
    add_p("Dưới đây là các kịch bản kiểm thử tính toàn vẹn và mức độ đáp ứng yêu cầu của bài lab:")
    
    test_widths = [Inches(0.5), Inches(1.8), Inches(1.8), Inches(1.2), Inches(1.2)]
    test_headers = ["STT", "Kịch bản kiểm thử", "Câu lệnh thực hiện", "Kết quả mong đợi", "Đánh giá thiết kế"]
    test_data = [
        ["1", "Kiểm tra Ping nội mạng VLAN 2", "PC0: ping 192.168.3.10 (PC1)", "Thành công (Reply 4/4)", "Switching Layer 2 hoạt động tốt"],
        ["2", "Kiểm tra Ping liên VLAN", "PC0: ping 192.168.4.10 (PC2)", "Thành công (Reply 4/4)", "Router-on-a-Stick định tuyến chuẩn"],
        ["3", "Kiểm tra Ping ra Server ngoại mạng", "PC0: ping 8.8.8.8 (Server0)", "Thành công", "NAT Overload & Default Route chuẩn"],
        ["4", "Kiểm tra truy cập Web bằng Domain", "Browser PC0: mở http://uth.com", "Hiển thị trang chủ", "DNS Server phân giải A-Record chuẩn"],
        ["5", "Kiểm tra bảo mật Telnet từ VLAN 2", "PC0/PC1: telnet 192.168.3.1", "Bị chặn (Connection refused)", "Access-list 10 chặn thành công"],
        ["6", "Kiểm tra bảo mật Telnet từ VLAN 3", "PC2: telnet 192.168.4.1", "Cho phép nhập password (123)", "Access-list 10 cho phép đúng thiết kế"]
    ]
    tbl_test = doc.add_table(rows=1, cols=5)
    format_styled_table(tbl_test, test_widths, test_headers, test_data, align_cols={0: WD_ALIGN_PARAGRAPH.CENTER, 3: WD_ALIGN_PARAGRAPH.CENTER})

    # Save document
    out_path = r"c:\Users\PC\Documents\GitHub\QA-netfilxfake\086205004972_Le_Hoang_Nhat_Binh_Cau_Hinh.docx"
    doc.save(out_path)
    print(f"Document successfully created at: {out_path}")
    return out_path

if __name__ == "__main__":
    create_document()
