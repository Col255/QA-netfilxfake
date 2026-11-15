const fs = require('fs');
const path = require('path');

Feature('FE3 - INT: White-box Data-Driven Testing (Statement & Branch Coverage)');

const csvPath = path.resolve(__dirname, '../docs/Design/whitebox_testcase_int.csv');

function parseCsvLine(line) {
    const result = [];
    let cur = '';
    let inQuotes = false;
    for (let i = 0; i < line.length; i++) {
        const ch = line[i];
        if (inQuotes) {
            if (ch === '"') {
                if (i + 1 < line.length && line[i + 1] === '"') { cur += '"'; i++; }
                else { inQuotes = false; }
            } else { cur += ch; }
        } else {
            if (ch === '"') { inQuotes = true; }
            else if (ch === ',') { result.push(cur); cur = ''; }
            else { cur += ch; }
        }
    }
    result.push(cur);
    return result;
}

let testRecords = [];
if (fs.existsSync(csvPath)) {
    const raw = fs.readFileSync(csvPath, 'utf-8').replace(/^\uFEFF/, '');
    const lines = raw.split(/\r?\n/).filter(l => l.trim().length > 0);
    const headers = parseCsvLine(lines[0]).map(h => h.trim());
    for (let i = 1; i < lines.length; i++) {
        const vals = parseCsvLine(lines[i]);
        const row = {};
        headers.forEach((h, idx) => {
            row[h] = h === 'Payload_Params' ? (vals[idx] ?? '') : (vals[idx] ?? '').trim();
        });
        testRecords.push(row);
    }
}

testRecords.forEach((tc) => {
    Scenario(`[${tc.WB_TC_ID}] ${tc.Ten_Kich_Ban_WhiteBox}`, async ({ I }) => {
        const endpoint = tc.File_PHP;
        const method = (tc.Method || 'GET').toUpperCase();
        const role = (tc.Session_Role || 'guest').toLowerCase();
        const params = tc.Payload_Params || '';

        // Đảm bảo phiên đăng nhập đúng theo Session_Role (guest / user / admin)
        await I.ensureBrowserSession(role);

        if (method === 'POST') {
            const searchParams = new URLSearchParams(params);
            let targetUrl = endpoint;
            if (endpoint === '/watch.php') {
                const rawId = searchParams.get('id') || '52';
                const movieId = rawId === '1' ? '52' : rawId;
                targetUrl = `/watch.php?id=${encodeURIComponent(movieId)}`;
            }

            I.amOnPage(targetUrl);
            await I.executeScript(({ rawParams }) => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;
                const sp = new URLSearchParams(rawParams);
                for (const [k, v] of sp.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = k;
                    input.value = v;
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            }, { rawParams: params });
            I.wait(0.5);
        } else {
            const url = params ? `${endpoint}?${params}` : endpoint;
            I.amOnPage(url);
        }

        I.dontSee('Fatal error');
        I.dontSee('Parse error');
        I.dontSee('Notice: Undefined variable');
        I.dontSee('Warning: Undefined array key');

        if (endpoint === '/comments.php' && tc.Nhanh_Kich_Hoat && tc.Nhanh_Kich_Hoat.includes('CMT_D1_T')) {
            I.seeInCurrentUrl('admin_login.php');
        }
        if (endpoint === '/watch_history.php' && tc.Nhanh_Kich_Hoat && tc.Nhanh_Kich_Hoat.includes('WH_D1_T')) {
            I.seeInCurrentUrl('login.php');
        }
        if (tc.WB_TC_ID === 'WB_INT_09') {
            I.see('Bình luận không tồn tại hoặc đã bị xóa trước đó.');
        }
        if (tc.WB_TC_ID === 'WB_INT_15') {
            I.see('Bạn cần đăng nhập để bình luận.');
        }
        if (tc.WB_TC_ID === 'WB_INT_16' || tc.WB_TC_ID === 'WB_INT_17') {
            I.see('Nội dung bình luận không được để trống.');
        }
    });
});
