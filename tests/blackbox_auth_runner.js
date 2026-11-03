/**
 * ============================================================================
 * DATA-DRIVEN BLACK-BOX TEST RUNNER — PHÂN HỆ 1: AUTH & SECURITY (AUTH_TC01..24)
 * ============================================================================
 * - Tự động đọc file Test Case đầu vào: docs/BANG_TONG_HOP_TEST_CASE_AUTH.xlsx
 * - Thực thi kiểm thử hộp đen (Black-box HTTP Testing) cho từng Test Case ID
 * - Đối chiếu Kết quả mong đợi (Expected) và Kết quả thực tế (Actual)
 * - Xuất file báo cáo kết quả chuẩn: reports/blackbox_report_AUTH.csv
 *
 * Cách chạy:
 *   1. Chạy toàn bộ 24 Test Cases của phân hệ AUTH:
 *      node tests/blackbox_auth_runner.js
 *   2. Chạy riêng 1 Test Case ID (khi đang test & fix từng thẻ trên Jira):
 *      node tests/blackbox_auth_runner.js --tc AUTH_TC08
 */

const ExcelJS = require('exceljs');
const fs = require('fs');
const path = require('path');
const http = require('http');
const https = require('https');
const querystring = require('querystring');

const BASE_URL = process.env.BASE_URL || 'http://localhost/netfix2';
const INPUT_EXCEL = path.join(__dirname, '..', 'docs', 'BANG_TONG_HOP_TEST_CASE_AUTH.xlsx');
const REPORT_DIR = path.join(__dirname, '..', 'reports');
const OUTPUT_CSV = path.join(REPORT_DIR, 'blackbox_report_AUTH.csv');

function cellToString(cell) {
  const v = cell ? cell.value : '';
  if (v === null || v === undefined) return '';
  if (typeof v === 'object') {
    if (v.richText) return v.richText.map(x => x.text).join('').trim();
    if (v.result !== undefined) return String(v.result).trim();
  }
  return String(v).trim();
}

/**
 * 1. HÀM ĐỌC TỰ ĐỘNG FILE TEST CASE (.XLSX)
 */
async function readTestCasesFromExcel(excelPath) {
  const wb = new ExcelJS.Workbook();
  await wb.xlsx.readFile(excelPath);
  const ws = wb.getWorksheet(2) || wb.worksheets[0];
  const testCases = [];

  for (let r = 4; r <= ws.rowCount; r++) {
    const row = ws.getRow(r);
    const tcId = cellToString(row.getCell(2));
    if (!tcId || !tcId.startsWith('AUTH_TC')) continue;

    testCases.push({
      stt: cellToString(row.getCell(1)),
      tcId,
      endpoint: cellToString(row.getCell(3)),
      title: cellToString(row.getCell(4)),
      preconditions: cellToString(row.getCell(5)),
      steps: cellToString(row.getCell(6)),
      testData: cellToString(row.getCell(7)),
      expected: cellToString(row.getCell(8)),
      technique: cellToString(row.getCell(9)),
      priority: cellToString(row.getCell(10)),
      type: cellToString(row.getCell(11)),
      author: cellToString(row.getCell(12))
    });
  }
  return testCases;
}

/**
 * Hàm gửi HTTP Request (GET/POST) phục vụ Black-Box Testing
 */
function sendHttpRequest({ endpoint, method = 'GET', formData = null, cookieSid = '' }) {
  return new Promise((resolve, reject) => {
    const startTime = Date.now();
    const targetUrl = new URL(BASE_URL.replace(/\/$/, '') + '/' + endpoint.replace(/^\//, ''));
    const isHttps = targetUrl.protocol === 'https:';
    const lib = isHttps ? https : http;

    const postBody = formData ? querystring.stringify(formData) : '';
    const headers = {
      'Accept': 'text/html,application/xhtml+xml,application/json'
    };
    if (cookieSid) {
      headers['Cookie'] = `PHPSESSID=${cookieSid}`;
    }
    if (method === 'POST') {
      headers['Content-Type'] = 'application/x-www-form-urlencoded';
      headers['Content-Length'] = Buffer.byteLength(postBody);
    }

    const req = lib.request(
      {
        hostname: targetUrl.hostname,
        port: targetUrl.port || (isHttps ? 443 : 80),
        path: targetUrl.pathname + targetUrl.search,
        method,
        headers,
        timeout: 5000
      },
      (res) => {
        const chunks = [];
        res.on('data', chunk => chunks.push(chunk));
        res.on('end', () => {
          const durationMs = Date.now() - startTime;
          const body = Buffer.concat(chunks).toString('utf8');
          resolve({
            statusCode: res.statusCode,
            headers: res.headers,
            body,
            durationMs
          });
        });
      }
    );

    req.on('error', (err) => reject(err));
    req.on('timeout', () => {
      req.destroy();
      reject(new Error('Request timeout (5000ms)'));
    });

    if (method === 'POST' && postBody) {
      req.write(postBody);
    }
    req.end();
  });
}

/**
 * 2. HÀM THỰC THI KIỂM THỬ HỘP ĐEN (BLACK-BOX EXECUTOR) CHO TỪNG TEST CASE ID
 */
async function executeSingleTestCase(tc, context) {
  const ts = context.runTimestamp;
  const dynUser = context.dynamicUser;
  const dynEmail = context.dynamicEmail;
  const authSid = context.authUserSid;

  try {
    switch (tc.tcId) {
      case 'AUTH_TC01': {
        const res = await sendHttpRequest({ endpoint: '/register.php', method: 'GET', cookieSid: `guest01_${ts}` });
        const hasForm = res.body.includes('register-form') && res.body.includes('name="username"');
        return {
          actual: `HTTP ${res.statusCode} | Có form .register-form & 4 ô input: ${hasForm}`,
          status: res.statusCode === 200 && hasForm ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC02': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest02_${ts}`,
          formData: { username: dynUser, email: dynEmail, password: '123456', confirm_password: '123456' }
        });
        const ok = res.body.includes('Đăng ký thành công! Vui lòng đăng nhập.');
        return {
          actual: ok
            ? `HTTP ${res.statusCode} | .success: "Đăng ký thành công! Vui lòng đăng nhập." (Refresh -> login.php)`
            : `HTTP ${res.statusCode} | Không thấy thông báo đăng ký thành công`,
          status: res.statusCode === 200 && ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC03': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest03_${ts}`,
          formData: { username: `bva5_${ts}`, email: `bva5_${ts}@gmail.com`, password: '12345', confirm_password: '12345' }
        });
        const ok = res.body.includes('Mật khẩu phải có ít nhất 6 ký tự.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Mật khẩu phải có ít nhất 6 ký tự."` : `HTTP ${res.statusCode} | Không bắt lỗi mật khẩu 5 ký tự`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC04': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest04_${ts}`,
          formData: { username: '', email: '', password: '', confirm_password: '' }
        });
        const ok = res.body.includes('Vui lòng nhập đầy đủ thông tin.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Vui lòng nhập đầy đủ thông tin."` : `HTTP ${res.statusCode} | Thiếu thông báo lỗi rỗng`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC05': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest05_${ts}`,
          formData: { username: `mis_${ts}`, email: `mis_${ts}@gmail.com`, password: '123456', confirm_password: '654321' }
        });
        const ok = res.body.includes('Mật khẩu xác nhận không khớp.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Mật khẩu xác nhận không khớp."` : `HTTP ${res.statusCode} | Không bắt lỗi lệch confirm_password`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC06': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest06_${ts}`,
          formData: { username: dynUser, email: `other_${ts}@gmail.com`, password: '123456', confirm_password: '123456' }
        });
        const ok = res.body.includes('Tên đăng nhập hoặc email đã được sử dụng.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Tên đăng nhập hoặc email đã được sử dụng."` : `HTTP ${res.statusCode} | Không chặn trùng username`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC07': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest07_${ts}`,
          formData: { username: `other_${ts}`, email: dynEmail, password: '123456', confirm_password: '123456' }
        });
        const ok = res.body.includes('Tên đăng nhập hoặc email đã được sử dụng.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Tên đăng nhập hoặc email đã được sử dụng."` : `HTTP ${res.statusCode} | Không chặn trùng email`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC08': {
        const res = await sendHttpRequest({
          endpoint: '/register.php',
          method: 'POST',
          cookieSid: `guest08_${ts}`,
          formData: { username: `badmail_${ts}`, email: `not_an_email_${ts}`, password: '123456', confirm_password: '123456' }
        });
        const hasValidation = res.body.includes('Địa chỉ email không hợp lệ.') || res.body.includes('Email không hợp lệ');
        return {
          actual: hasValidation
            ? `HTTP ${res.statusCode} | .error: "Địa chỉ email không hợp lệ."`
            : `HTTP ${res.statusCode} | [BUG-AUTH-01] Hệ thống vẫn cho đăng ký thành công với email sai định dạng (Thiếu filter_var ở Backend)`,
          status: hasValidation ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC09': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: authSid,
          formData: { identifier: dynEmail, password: '123456' }
        });
        const loc = res.headers['location'] || '';
        const ok = res.statusCode === 302 && loc.includes('index.php');
        return {
          actual: `HTTP ${res.statusCode} | Location: ${loc || 'None'} (Khởi tạo Session thành công)`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC10': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: `usersess2_${ts}`,
          formData: { identifier: dynUser, password: '123456' }
        });
        const loc = res.headers['location'] || '';
        const ok = res.statusCode === 302 && loc.includes('index.php');
        return {
          actual: `HTTP ${res.statusCode} | Location: ${loc || 'None'} (Đăng nhập bằng Username thành công)`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC11': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: `guest11_${ts}`,
          formData: { identifier: dynEmail, password: 'sai_mat_khau_123' }
        });
        const ok = res.body.includes('Mật khẩu không đúng.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Mật khẩu không đúng."` : `HTTP ${res.statusCode} | Không báo lỗi sai mật khẩu`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC12': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: `guest12_${ts}`,
          formData: { identifier: `khong_ton_tai_${ts}@gmail.com`, password: '123456' }
        });
        const ok = res.body.includes('Tên đăng nhập hoặc email không tồn tại.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Tên đăng nhập hoặc email không tồn tại."` : `HTTP ${res.statusCode} | Phản hồi không khớp`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC13': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: `guest13_${ts}`,
          formData: { identifier: '', password: '' }
        });
        const ok = res.body.includes('Vui lòng nhập đầy đủ tên đăng nhập/email và mật khẩu.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Vui lòng nhập đầy đủ tên đăng nhập/email và mật khẩu."` : `HTTP ${res.statusCode} | Phản hồi không khớp`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC14': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: `guest14_${ts}`,
          formData: { identifier: 'admin', password: 'admin123' }
        });
        const ok = res.body.includes('Tên đăng nhập hoặc email không tồn tại.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | Chặn Admin tại /login.php: "Tên đăng nhập hoặc email không tồn tại."` : `HTTP ${res.statusCode} | Không chặn Admin`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC15': {
        const res = await sendHttpRequest({
          endpoint: '/admin_login.php',
          method: 'POST',
          cookieSid: `admin15_${ts}`,
          formData: { identifier: 'admin', password: 'admin123' }
        });
        const loc = res.headers['location'] || '';
        const ok = res.statusCode === 302 && loc.includes('admin_dashboard.php');
        return {
          actual: ok
            ? `HTTP ${res.statusCode} | Location: ${loc} (Đăng nhập Admin thành công)`
            : `HTTP ${res.statusCode} | [BUG-AUTH-02] Không thể đăng nhập Admin (Dòng 29 dùng === plain-text thay vì password_verify)`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC16': {
        const res = await sendHttpRequest({
          endpoint: '/admin_login.php',
          method: 'POST',
          cookieSid: `guest16_${ts}`,
          formData: { identifier: dynEmail, password: '123456' }
        });
        const ok = res.body.includes('Tên đăng nhập hoặc email không tồn tại hoặc tài khoản này không phải admin.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | Chặn User thường vào cổng Admin thành công` : `HTTP ${res.statusCode} | Phản hồi không khớp`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC17': {
        const res = await sendHttpRequest({
          endpoint: '/profile.php',
          method: 'GET',
          cookieSid: `guest17_${ts}`
        });
        const loc = res.headers['location'] || '';
        const ok = res.statusCode === 302 && loc.includes('login.php');
        return {
          actual: `HTTP ${res.statusCode} | Redirect Location: ${loc || 'None'} (Chặn khách vãng lai thành công)`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC18': {
        const res = await sendHttpRequest({
          endpoint: '/profile.php',
          method: 'GET',
          cookieSid: authSid
        });
        const ok = res.statusCode === 200 && res.body.includes('Hồ sơ của bạn') && res.body.includes('Trạng thái VIP:');
        return {
          actual: ok ? `HTTP ${res.statusCode} | Hiển thị Hồ sơ & đồng bộ trạng thái VIP chính xác` : `HTTP ${res.statusCode} | Không tải được trang profile.php`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC19': {
        const upUser = `${dynUser}_up`;
        const upEmail = `up_${dynEmail}`;
        const res = await sendHttpRequest({
          endpoint: '/edit_profile.php',
          method: 'POST',
          cookieSid: authSid,
          formData: { update_profile: '1', username: upUser, email: upEmail }
        });
        const ok = res.body.includes('Thông tin cá nhân đã được cập nhật thành công!');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .success: "Thông tin cá nhân đã được cập nhật thành công!"` : `HTTP ${res.statusCode} | Cập nhật hồ sơ thất bại`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC20': {
        const res = await sendHttpRequest({
          endpoint: '/edit_profile.php',
          method: 'POST',
          cookieSid: authSid,
          formData: { update_profile: '1', username: `${dynUser}_up`, email: 'sai_dinh_dang_email' }
        });
        const ok = res.body.includes('Địa chỉ email không hợp lệ.');
        return {
          actual: ok ? `HTTP ${res.statusCode} | .error: "Địa chỉ email không hợp lệ."` : `HTTP ${res.statusCode} | Không bắt lỗi email sai định dạng`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC21': {
        const res = await sendHttpRequest({
          endpoint: '/change_password.php',
          method: 'POST',
          cookieSid: authSid,
          formData: { current_password: '123456', new_password: '654321', confirm_new_password: '654321' }
        });
        const loc = res.headers['location'] || '';
        const ok = res.statusCode === 302 && loc.includes('status=success');
        return {
          actual: `HTTP ${res.statusCode} | Location: ${loc || 'None'}`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC22': {
        const res = await sendHttpRequest({
          endpoint: '/change_password.php',
          method: 'POST',
          cookieSid: authSid,
          formData: { current_password: '654321', new_password: '12345', confirm_new_password: '12345' }
        });
        const loc = res.headers['location'] || '';
        const ok = res.statusCode === 302 && loc.includes('status=error');
        return {
          actual: `HTTP ${res.statusCode} | Location: ${loc || 'None'} (Từ chối mật khẩu 5 ký tự)`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC23': {
        const res = await sendHttpRequest({
          endpoint: '/logout.php',
          method: 'GET',
          cookieSid: authSid
        });
        const loc = res.headers['location'] || '';
        const checkAfter = await sendHttpRequest({ endpoint: '/profile.php', method: 'GET', cookieSid: authSid });
        const ok = res.statusCode === 302 && loc.includes('login.php') && checkAfter.statusCode === 302;
        return {
          actual: `HTTP ${res.statusCode} -> Location: ${loc} | Truy cập lại /profile.php bị đẩy về ${checkAfter.headers['location']}`,
          status: ok ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      case 'AUTH_TC24': {
        const res = await sendHttpRequest({
          endpoint: '/login.php',
          method: 'POST',
          cookieSid: `guest24_${ts}`,
          formData: { identifier: "' OR '1'='1 -- <script>alert(1)</script>", password: "' OR '1'='1 --" }
        });
        const safe = res.body.includes('Tên đăng nhập hoặc email không tồn tại.') && !res.body.includes('SQL syntax');
        return {
          actual: safe ? `HTTP ${res.statusCode} | Chặn đứng SQLi & XSS an toàn qua Prepared Statement` : `HTTP ${res.statusCode} | Nguy cơ lộ lỗi bảo mật`,
          status: safe ? 'PASS' : 'FAIL',
          timeMs: res.durationMs
        };
      }
      default:
        return { actual: 'Chưa định nghĩa kịch bản', status: 'SKIP', timeMs: 0 };
    }
  } catch (err) {
    return {
      actual: `Lỗi kết nối Server (${BASE_URL}): ${err.message}`,
      status: 'ERROR',
      timeMs: 0
    };
  }
}

/**
 * 3. HÀM XUẤT BÁO CÁO KẾT QUẢ RA FILE .CSV (EXPORT CSV REPORT)
 */
function exportCsvReport(results, outputPath) {
  if (!fs.existsSync( path.dirname(outputPath) )) {
    fs.mkdirSync(path.dirname(outputPath), { recursive: true });
  }
  const csvEscape = (val) => `"${String(val ?? '').replace(/\r?\n/g, ' | ').replace(/"/g, '""')}"`;

  const headers = [
    'STT',
    'Test_Case_ID',
    'Endpoint',
    'Test_Case_Title',
    'Test_Technique',
    'Priority',
    'Input_Test_Data',
    'Expected_Result',
    'Actual_Result',
    'Status',
    'Response_Time_ms',
    'Executed_At'
  ];

  const lines = [headers.map(csvEscape).join(',')];
  for (const r of results) {
    lines.push([
      r.stt,
      r.tcId,
      r.endpoint,
      r.title,
      r.technique,
      r.priority,
      r.testData,
      r.expected,
      r.actual,
      r.status,
      r.timeMs,
      r.executedAt
    ].map(csvEscape).join(','));
  }

  fs.writeFileSync(outputPath, '\uFEFF' + lines.join('\r\n'), 'utf8');
}

/**
 * HÀM CHÍNH: ĐỌC TEST CASE -> CHẠY BLACK-BOX TEST -> XUẤT CSV REPORT
 */
async function runBlackBoxTestSuite() {
  const args = process.argv.slice(2);
  const tcFilterIdx = args.indexOf('--tc');
  const filterTcId = tcFilterIdx !== -1 && args[tcFilterIdx + 1] ? args[tcFilterIdx + 1].toUpperCase() : null;

  console.log('============================================================================');
  console.log(' BẮT ĐẦU ĐỌC FILE TEST CASE & THỰC THI BLACK-BOX TESTING (AUTH & SECURITY)');
  console.log(` File đầu vào : ${INPUT_EXCEL}`);
  console.log(` Server đích  : ${BASE_URL}`);
  console.log('============================================================================');

  const allTestCases = await readTestCasesFromExcel(INPUT_EXCEL);
  console.log(`[+] Đã đọc thành công ${allTestCases.length} Test Cases từ file Excel.`);

  const ts = Date.now();
  const context = {
    runTimestamp: ts,
    dynamicUser: `loc_qa_${ts}`,
    dynamicEmail: `loc_qa_${ts}@gmail.com`,
    authUserSid: `usersess_${ts}`
  };

  // Nếu chỉ chạy 1 Test Case lẻ (ví dụ --tc AUTH_TC19), tự động tiền tạo tài khoản & phiên đăng nhập nếu cần
  if (filterTcId && filterTcId !== 'AUTH_TC01' && filterTcId !== 'AUTH_TC02') {
    await executeSingleTestCase({ tcId: 'AUTH_TC02' }, context);
    await executeSingleTestCase({ tcId: 'AUTH_TC09' }, context);
  }

  const targetCases = filterTcId
    ? allTestCases.filter(x => x.tcId === filterTcId)
    : allTestCases;

  const reportRows = [];
  let passCount = 0;
  let failCount = 0;

  for (const tc of targetCases) {
    const res = await executeSingleTestCase(tc, context);
    const nowStr = new Date().toISOString().replace('T', ' ').slice(0, 19);
    if (res.status === 'PASS') passCount++;
    else failCount++;

    const icon = res.status === 'PASS' ? '✅ PASS' : '❌ ' + res.status;
    console.log(` ${icon} | ${tc.tcId} (${res.timeMs}ms) - ${tc.title}`);
    if (res.status !== 'PASS') {
      console.log(`          ↳ Thực tế: ${res.actual}`);
    }

    reportRows.push({
      ...tc,
      actual: res.actual,
      status: res.status,
      timeMs: res.timeMs,
      executedAt: nowStr
    });
  }

  exportCsvReport(reportRows, OUTPUT_CSV);
  console.log('============================================================================');
  console.log(` TỔNG KẾT: ${reportRows.length} Test Cases | PASS: ${passCount} | FAIL/ERROR: ${failCount}`);
  console.log(` ĐÃ XUẤT BÁO CÁO CSV TẠI: ${OUTPUT_CSV}`);
  console.log('============================================================================');
}

runBlackBoxTestSuite().catch(console.error);
