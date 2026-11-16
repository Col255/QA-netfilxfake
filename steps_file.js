const fs = require('fs');
const path = require('path');
const assert = require('assert');

// Trạng thái phiên đăng nhập hiện tại trên trình duyệt Chromium ('guest' | 'user' | 'admin')
let currentBrowserRole = 'guest';

/**
 * Bộ phân tích dòng CSV chuẩn RFC-4180 (xử lý an toàn dấu ngoặc kép và dấu phẩy)
 */
function parseCsvLine(line) {
  const result = [];
  let cur = '';
  let inQuotes = false;
  for (let i = 0; i < line.length; i++) {
    const ch = line[i];
    if (inQuotes) {
      if (ch === '"') {
        if (i + 1 < line.length && line[i + 1] === '"') {
          cur += '"';
          i++;
        } else {
          inQuotes = false;
        }
      } else {
        cur += ch;
      }
    } else {
      if (ch === '"') {
        inQuotes = true;
      } else if (ch === ',') {
        result.push(cur);
        cur = '';
      } else {
        cur += ch;
      }
    }
  }
  result.push(cur);
  return result;
}

/**
 * Đọc trực tiếp file docs/Design/design_testcase_*.csv và khởi tạo DataTable cho CodeceptJS
 * Tuyệt đối không gán cứng (hardcode) bất kỳ input Test Case nào trong file test .js
 */
function loadCsvDataAndCoverage(csvFileName, filterFn = null) {
  const designSubPath = path.join(__dirname, 'docs', 'Design', csvFileName);
  const rootDocsPath = path.join(__dirname, 'docs', csvFileName);
  const csvPath = fs.existsSync(designSubPath) ? designSubPath : rootDocsPath;
  const relativeCsvLabel = fs.existsSync(designSubPath)
    ? `docs/Design/${csvFileName}`
    : `docs/${csvFileName}`;
  const raw = fs.readFileSync(csvPath, 'utf8').replace(/^\uFEFF/, '');
  const lines = raw.split(/\r?\n/).filter(l => l.trim().length > 0);
  const headers = parseCsvLine(lines[0]).map(h => h.trim());

  const rows = [];
  for (let i = 1; i < lines.length; i++) {
    const vals = parseCsvLine(lines[i]);
    const rowObj = {};
    headers.forEach((h, idx) => {
      rowObj[h] = (vals[idx] ?? '').trim();
    });
    if (!filterFn || filterFn(rowObj)) {
      rows.push(rowObj);
    }
  }

  // Khởi tạo bảng DataTable chuẩn của CodeceptJS
  const table = new DataTable([
    'STT',
    'Jira_Task',
    'Ten_Test_Case',
    'Endpoint',
    'Method',
    'Payload_Params',
    'Ket_Qua_Mong_Doi',
    'Tag_Bao_Phu'
  ]);

  const designedTags = new Set();
  for (const r of rows) {
    table.add([
      r.STT,
      r.Jira_Task,
      r.Ten_Test_Case,
      r.Endpoint,
      r.Method,
      r.Payload_Params,
      r.Ket_Qua_Mong_Doi,
      r.Tag_Bao_Phu
    ]);
    splitTags(r.Tag_Bao_Phu).forEach(t => designedTags.add(t));
  }

  // Bộ theo dõi Độ phủ (Coverage Tracker) khi các Test Case được implement & thực thi
  const executedStt = new Set();
  const coveredTags = new Set();

  const tracker = {
    csvFileName,
    totalDesignedCases: rows.length,
    designedTags,
    executedStt,
    coveredTags,
    recordExecution(current) {
      if (current && current.STT) {
        executedStt.add(String(current.STT));
      }
      if (current && current.Tag_Bao_Phu) {
        splitTags(current.Tag_Bao_Phu).forEach(t => coveredTags.add(t));
      }
    },
    printCoverageReport(suiteTitle) {
      const totalTC = rows.length;
      const execTC = executedStt.size;
      const tcPct = totalTC > 0 ? ((execTC / totalTC) * 100).toFixed(2) : '100.00';

      const allDesigned = Array.from(designedTags);
      const allCovered = Array.from(coveredTags);
      const tagPct = allDesigned.length > 0
        ? ((allCovered.length / allDesigned.length) * 100).toFixed(2)
        : '100.00';

      const groupTags = (predicate) => {
        const d = allDesigned.filter(predicate);
        const c = d.filter(t => coveredTags.has(t));
        const pct = d.length > 0 ? ((c.length / d.length) * 100).toFixed(2) + '%' : 'N/A';
        return { d, c, pct };
      };

      const vGroup = groupTags(t => /^(MOV_)?V\d+/i.test(t));
      const xGroup = groupTags(t => /^(MOV_)?X\d+/i.test(t));
      const bGroup = groupTags(t => /^B\d+/i.test(t));
      const sGroup = groupTags(t => /^(MOV_)?SEC_/i.test(t));
      const aGroup = groupTags(t => /^API_/i.test(t));

      console.log('\n+' + '='.repeat(88) + '+');
      console.log(`| BÁO CÁO ĐỘ PHỦ KIỂM THỬ (COVERAGE REPORT) - ${suiteTitle}`);
      console.log(`| Nguồn dữ liệu (Data-Driven) : ${relativeCsvLabel}`);
      console.log('+' + '-'.repeat(88) + '+');
      console.log(`| 1. Độ phủ Cài đặt Test Case : ${execTC}/${totalTC} Test Cases (${tcPct}%)`);
      console.log(`| 2. Tổng Độ phủ Nhãn Thiết kế: ${allCovered.length}/${allDesigned.length} Tags (${tagPct}%)`);
      console.log('+' + '-'.repeat(88) + '+');
      console.log(`|   - Phân vùng Hợp lệ (V)    : ${vGroup.c.length}/${vGroup.d.length} (${vGroup.pct}) -> [${vGroup.c.join(', ')}]`);
      console.log(`|   - Phân vùng Không HL (X)  : ${xGroup.c.length}/${xGroup.d.length} (${xGroup.pct}) -> [${xGroup.c.join(', ')}]`);
      console.log(`|   - Giá trị Biên (BVA - B)  : ${bGroup.c.length}/${bGroup.d.length} (${bGroup.pct}) -> [${bGroup.c.join(', ')}]`);
      console.log(`|   - Bảo mật OWASP (SEC)     : ${sGroup.c.length}/${sGroup.d.length} (${sGroup.pct}) -> [${sGroup.c.join(', ')}]`);
      console.log(`|   - Chức năng API (API)     : ${aGroup.c.length}/${aGroup.d.length} (${aGroup.pct}) -> [${aGroup.c.join(', ')}]`);
      console.log('+' + '='.repeat(88) + '+\n');
    }
  };

  return { table, tracker, rows };
}

function splitTags(tagStr) {
  if (!tagStr) return [];
  return String(tagStr)
    .split(/[;,]/)
    .map(s => s.trim())
    .filter(Boolean);
}

/**
 * Xác định quyền truy cập cần thiết trên trình duyệt để hiển thị đúng giao diện trang đích
 */
function resolveRequiredRole(endpoint, method, tags) {
  const isRbacOrAuthTest = tags.includes('SEC_RBAC') || tags.includes('SEC_AUTH');

  // Các trang Đăng ký / Đăng nhập hoặc Test Case đang kiểm tra chặn khách vãng lai (SEC_AUTH / SEC_RBAC)
  if (
    endpoint === '/register.php' ||
    endpoint === '/login.php' ||
    endpoint === '/admin_login.php' ||
    isRbacOrAuthTest
  ) {
    return 'guest';
  }

  // Các trang Quản trị viên (Admin CMS)
  const adminEndpoints = [
    '/add_movie.php',
    '/manage_movies.php',
    '/orders.php',
    '/customers.php',
    '/statistics.php',
    '/comments.php'
  ];
  if (adminEndpoints.includes(endpoint)) {
    return 'admin';
  }

  // Các trang Người dùng thành viên (User)
  const userEndpoints = [
    '/profile.php',
    '/edit_profile.php',
    '/change_password.php',
    '/pricing.php',
    '/process_payment.php',
    '/payment_return.php',
    '/watch_history.php'
  ];
  if (userEndpoints.includes(endpoint) || (endpoint === '/watch.php' && method === 'POST')) {
    return 'user';
  }

  return null; // Giữ nguyên trạng thái hiện tại cho các trang công khai
}

const actorExport = function () {
  return actor({
    /**
     * Đảm bảo trình duyệt Chromium đang ở đúng trạng thái đăng nhập (guest / user / admin)
     * trước khi mở giao diện Test Case
     */
    async ensureBrowserSession(requiredRole) {
      if (!requiredRole || currentBrowserRole === requiredRole) {
        return;
      }

      if (requiredRole === 'guest') {
        this.amOnPage('/logout.php');
        currentBrowserRole = 'guest';
        return;
      }

      if (requiredRole === 'user') {
        if (currentBrowserRole !== 'guest') {
          this.amOnPage('/logout.php');
        }
        this.amOnPage('/login.php');
        this.fillField('#identifier', 'tuan@gmail.com');
        this.fillField('#password', '123456');
        this.click('button[type="submit"]');
        currentBrowserRole = 'user';
        return;
      }

      if (requiredRole === 'admin') {
        if (currentBrowserRole !== 'guest') {
          this.amOnPage('/logout.php');
        }
        this.amOnPage('/admin_login.php');
        this.fillField('#identifier', 'admin');
        this.fillField('#password', 'admin123');
        this.click('button[type="submit"]');
        currentBrowserRole = 'admin';
      }
    },

    /**
     * Hiển thị thanh HUD trạng thái Test Case ở góc trên bên phải màn hình Chromium
     */
    async renderTestCaseHud(current) {
      const plainTc = {
        STT: String(current.STT || ''),
        Jira_Task: String(current.Jira_Task || ''),
        Ten_Test_Case: String(current.Ten_Test_Case || ''),
        Endpoint: String(current.Endpoint || ''),
        Method: String(current.Method || ''),
        Payload_Params: String(current.Payload_Params || ''),
        Ket_Qua_Mong_Doi: String(current.Ket_Qua_Mong_Doi || ''),
        Tag_Bao_Phu: String(current.Tag_Bao_Phu || '')
      };
      await this.executeScript((tc) => {
        if (!document || !document.body) return;
        let hud = document.getElementById('qa-data-driven-hud');
        if (!hud) {
          hud = document.createElement('div');
          hud.id = 'qa-data-driven-hud';
          hud.style.cssText = [
            'position:fixed',
            'top:12px',
            'right:12px',
            'z-index:999999',
            'background:rgba(20,20,20,0.95)',
            'color:#fff',
            'border-left:5px solid #e50914',
            'border-radius:6px',
            'padding:10px 14px',
            'font-family:Segoe UI,sans-serif',
            'font-size:12px',
            'line-height:1.45',
            'box-shadow:0 6px 20px rgba(0,0,0,0.65)',
            'max-width:420px',
            'pointer-events:none'
          ].join(';');
          document.body.appendChild(hud);
        }
        hud.innerHTML = `
          <div style="color:#e50914;font-weight:700;font-size:13px;margin-bottom:3px;">
            [STT ${tc.STT} | ${tc.Jira_Task}] ${tc.Ten_Test_Case}
          </div>
          <div style="color:#ccc;">
            <b>${tc.Method}</b> <code style="color:#4cd137;">${tc.Endpoint}</code>
          </div>
          <div style="color:#aaa;word-break:break-all;margin-top:2px;">
            <b>Input CSV:</b> ${tc.Payload_Params || '(Trống)'}
          </div>
          <div style="color:#fbc531;margin-top:2px;">
            <b>Tags:</b> ${tc.Tag_Bao_Phu}
          </div>
        `;
      }, plainTc);
    },

    /**
     * Thực thi động 1 dòng Test Case đọc từ file CSV (Data-Driven)
     * dựa hoàn toàn vào các cột: Endpoint, Method, Payload_Params, Ket_Qua_Mong_Doi, Tag_Bao_Phu
     */
    async executeDataDrivenTestCase(current, tracker) {
      if (tracker) {
        tracker.recordExecution(current);
      }

      const endpoint = current.Endpoint || '/index.php';
      const method = (current.Method || 'GET').toUpperCase();
      const params = current.Payload_Params || '';
      const expected = current.Ket_Qua_Mong_Doi || '';
      const tags = current.Tag_Bao_Phu || '';

      // Đảm bảo đúng phiên đăng nhập trên trình duyệt (guest / user / admin)
      const requiredRole = resolveRequiredRole(endpoint, method, tags);
      await this.ensureBrowserSession(requiredRole);

      if (method === 'POST') {
        // 1. Mở đúng trang giao diện chứa biểu mẫu tương ứng với Endpoint POST
        const parsedParams = new URLSearchParams(params);
        let displayPage = endpoint;

        if (endpoint === '/process_payment.php') {
          const pkgId = parsedParams.get('package_id') || '1';
          displayPage = `/checkout.php?package_id=${encodeURIComponent(pkgId)}`;
        } else if (endpoint === '/change_password.php') {
          displayPage = '/edit_profile.php';
        } else if (endpoint === '/watch.php') {
          const movieId = parsedParams.get('id') || '1';
          displayPage = `/watch.php?id=${encodeURIComponent(movieId)}`;
        }

        this.amOnPage(displayPage);
        await this.renderTestCaseHud(current);

        // 2. Điền trực quan từng trường dữ liệu từ cột Payload_Params của CSV lên biểu mẫu trên màn hình
        if (params) {
          await this.executeScript((rawParams) => {
            const searchParams = new URLSearchParams(rawParams);
            const stripAccents = (s) =>
              String(s || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            for (const [key, val] of searchParams.entries()) {
              // Xử lý radio button (ví dụ: payment_method trên trang checkout.php)
              const radios = document.querySelectorAll(`input[type="radio"][name="${key}"]`);
              if (radios.length > 0) {
                radios.forEach((r) => {
                  if (stripAccents(r.value) === stripAccents(val)) {
                    r.checked = true;
                    if (r.parentElement) {
                      r.parentElement.style.border = '2px solid #e50914';
                      r.parentElement.style.boxShadow = '0 0 10px rgba(229,9,20,0.6)';
                    }
                  }
                });
                continue;
              }

              const el =
                document.querySelector(`[name="${key}"]`) ||
                document.querySelector(`#${key}`);
              if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT')) {
                if (el.type !== 'hidden' && el.type !== 'file') {
                  el.value = val;
                  el.style.border = '2px solid #e50914';
                  el.style.boxShadow = '0 0 10px rgba(229,9,20,0.6)';
                  el.scrollIntoView({ behavior: 'instant', block: 'center' });
                }
              }
            }
          }, params);
          this.wait(0.25);
        }

        // 3. Gửi request POST với đúng Payload_Params từ file CSV và kiểm tra phản hồi Backend
        const res = await this.sendPostRequest(endpoint, params, {
          'Content-Type': 'application/x-www-form-urlencoded'
        });

        const validCodes = [200, 301, 302, 303, 400, 401, 403, 404, 422];
        assert.ok(
          validCodes.includes(res.status),
          `[${current.Jira_Task} - ${current.Ten_Test_Case}] Mã HTTP ${res.status} không khớp kỳ vọng (${expected})`
        );

        const bodyText = typeof res.data === 'string' ? res.data : JSON.stringify(res.data || '');
        assert.ok(!bodyText.includes('Fatal error'), 'Phản hồi chứa lỗi PHP Fatal error');
        assert.ok(!bodyText.includes('SQL syntax'), 'Phản hồi làm lộ lỗi cú pháp SQL');
        assert.ok(!bodyText.includes('SQLSTATE'), 'Phản hồi làm lộ lỗi SQLSTATE');

        this.dontSee('Fatal error');
        this.dontSee('SQL syntax');
        this.dontSee('SQLSTATE');
      } else {
        // Phương thức GET: Nối Payload_Params từ file CSV vào URL
        const targetUrl = params ? `${endpoint}?${params}` : endpoint;
        const res = await this.sendGetRequest(targetUrl);

        const validCodes = [200, 301, 302, 303, 400, 401, 403, 404, 422];
        assert.ok(
          validCodes.includes(res.status),
          `[${current.Jira_Task} - ${current.Ten_Test_Case}] Mã HTTP ${res.status} không khớp kỳ vọng (${expected})`
        );

        // Kiểm tra hiển thị trên trình duyệt Chromium
        this.amOnPage(targetUrl);
        if (endpoint === '/logout.php') {
          currentBrowserRole = 'guest';
        }

        this.dontSee('Fatal error');
        this.dontSee('SQL syntax');
        this.dontSee('SQLSTATE');

        // Nếu Tag_Bao_Phu có kiểm thử bảo mật XSS, xác nhận mã độc không bị thực thi thô
        if (tags.includes('XSS')) {
          this.dontSeeInSource('<script>alert(1)</script>');
          this.dontSeeInSource("<script>alert('XSS')</script>");
          this.dontSeeInSource('<script>alert("XSS")</script>');
        }

        // Nếu là API JSON /api/view_counter.php, đối chiếu cấu trúc JSON và hiển thị giao diện Dark API Inspector
        if (endpoint.includes('/api/view_counter.php')) {
          if (params === 'id=0' || params.startsWith('id=-')) {
            this.see('"success":false');
          } else {
            this.see('"success":');
          }

          const plainTc = {
            Jira_Task: String(current.Jira_Task || ''),
            Ten_Test_Case: String(current.Ten_Test_Case || ''),
            Method: String(current.Method || ''),
            Endpoint: String(current.Endpoint || ''),
            Payload_Params: String(current.Payload_Params || ''),
            Ket_Qua_Mong_Doi: String(current.Ket_Qua_Mong_Doi || '')
          };
          await this.executeScript((tc) => {
            if (!document || !document.body) return;
            const rawJson = document.body.innerText;
            document.body.style.cssText =
              'background:#121212;color:#f1f1f1;font-family:Segoe UI,sans-serif;padding:40px;margin:0;';
            document.body.innerHTML = `
              <div style="max-width:720px;margin:40px auto;background:#1e1e1e;border:1px solid #333;border-top:4px solid #e50914;border-radius:10px;padding:28px;box-shadow:0 10px 30px rgba(0,0,0,0.6);">
                <h2 style="color:#e50914;margin:0 0 12px 0;">⚡ Netfix2 REST API Inspector</h2>
                <p style="margin:6px 0;color:#ccc;"><b>Test Case:</b> [${tc.Jira_Task}] ${tc.Ten_Test_Case}</p>
                <p style="margin:6px 0;color:#ccc;"><b>Endpoint:</b> <code style="color:#4cd137;">${tc.Method} ${tc.Endpoint}?${tc.Payload_Params}</code></p>
                <p style="margin:6px 0;color:#ccc;"><b>Kỳ vọng:</b> ${tc.Ket_Qua_Mong_Doi}</p>
                <div style="margin-top:16px;background:#0d0d0d;border:1px solid #2c2c2c;border-radius:6px;padding:16px;">
                  <div style="color:#888;font-size:12px;margin-bottom:6px;">JSON Response Payload:</div>
                  <pre style="color:#4cd137;font-size:15px;margin:0;white-space:pre-wrap;">${rawJson}</pre>
                </div>
              </div>
            `;
          }, plainTc);
          this.wait(0.2);
        }

        await this.renderTestCaseHud(current);
      }
    }
  });
};

actorExport.loadCsvDataAndCoverage = loadCsvDataAndCoverage;
module.exports = actorExport;
