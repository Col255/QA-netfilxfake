const fs = require('fs');
const path = require('path');
const { parse } = require('csv-parse/sync');

Feature('FE5 - ADM: White-box Data-Driven Testing (Statement & Branch Coverage)');

const csvPath = path.resolve(__dirname, '../docs/Design/whitebox_testcase_adm.csv');
let testRecords = [];

if (fs.existsSync(csvPath)) {
    const content = fs.readFileSync(csvPath, 'utf-8');
    testRecords = parse(content, {
        columns: true,
        skip_empty_lines: true,
        bom: true
    });
}

testRecords.forEach((tc) => {
    Scenario(`[${tc.WB_TC_ID}] ${tc.Ten_Kich_Ban_WhiteBox}`, async ({ I }) => {
        const endpoint = tc.File_PHP;
        const method = (tc.Method || 'GET').toUpperCase();
        const role = tc.Session_Role || 'guest';
        const params = tc.Payload_Params;

        // 1. Phân quyền vai trò: nếu là guest thì đảm bảo phiên chưa đăng nhập
        if (role === 'guest') {
            I.amOnPage('/logout.php');
        }

        // 2. Thực thi kiểm thử theo phương thức
        if (method === 'POST') {
            I.amOnPage(endpoint);
            I.dontSee('Fatal error');
            I.dontSee('Parse error');
        } else {
            const url = params ? `${endpoint}?${params}` : endpoint;
            I.amOnPage(url);
        }

        // 3. Statement Coverage: Không dính bất kỳ lỗi PHP Warning hay Undefined nào
        I.dontSee('Fatal error');
        I.dontSee('Parse error');
        I.dontSee('Notice: Undefined variable');
        I.dontSee('Warning: Undefined array key');

        // 4. Branch Coverage kiểm tra điều kiện phân quyền:
        if (tc.Nhanh_Kich_Hoat && tc.Nhanh_Kich_Hoat.includes('_D1_T')) {
            I.seeInCurrentUrl('admin_login.php');
        }
    });
});