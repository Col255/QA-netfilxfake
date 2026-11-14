const fs = require('fs');
const path = require('path');

Feature('FE5 - ADM: White-box Data-Driven Testing (Statement & Branch Coverage)');

const csvPath = path.resolve(__dirname, '../docs/Design/whitebox_testcase_adm.csv');

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
        headers.forEach((h, idx) => { row[h] = (vals[idx] ?? '').trim(); });
        testRecords.push(row);
    }
}

testRecords.forEach((tc) => {
    Scenario(`[${tc.WB_TC_ID}] ${tc.Ten_Kich_Ban_WhiteBox}`, async ({ I }) => {
        const endpoint = tc.File_PHP;
        const method = (tc.Method || 'GET').toUpperCase();
        const role = tc.Session_Role || 'guest';
        const params = tc.Payload_Params;

        if (role === 'guest') {
            I.amOnPage('/logout.php');
        }

        if (method === 'POST') {
            I.amOnPage(endpoint);
            I.dontSee('Fatal error');
            I.dontSee('Parse error');
        } else {
            const url = params ? `${endpoint}?${params}` : endpoint;
            I.amOnPage(url);
        }

        I.dontSee('Fatal error');
        I.dontSee('Parse error');
        I.dontSee('Notice: Undefined variable');
        I.dontSee('Warning: Undefined array key');

        if (tc.Nhanh_Kich_Hoat && tc.Nhanh_Kich_Hoat.includes('_D1_T')) {
            I.seeInCurrentUrl('admin_login.php');
        }
    });
});