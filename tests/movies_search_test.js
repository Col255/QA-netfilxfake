const fs = require('fs');
const path = require('path');
const { parse } = require('csv-parse/sync');

Feature('MOV - Data-Driven Testing from CSV (Movies & Search)');

const csvFilePath = path.resolve(__dirname, '../docs/Design/design_testcase_mov.csv');
let testRecords = [];

if (fs.existsSync(csvFilePath)) {
    const fileContent = fs.readFileSync(csvFilePath, 'utf-8');
    testRecords = parse(fileContent, {
        columns: true,
        skip_empty_lines: true,
        bom: true
    });
}

testRecords.forEach((test) => {
    Scenario(`[${test.Jira_Task || 'MOV'}] ${test.Ten_Test_Case}`, ({ I }) => {
        const endpoint = test.Endpoint;
        const params = test.Payload_Params ? `?${test.Payload_Params}` : '';
        const fullUrl = `${endpoint}${params}`;

        I.amOnPage(fullUrl);

        I.dontSee('Fatal error');
        I.dontSee('Parse error');

        if (test.Tag_Bao_Phu && test.Tag_Bao_Phu.includes('SEC_SQLI')) {
            I.dontSee('SQL syntax');
            I.dontSee('mysql_fetch');
        }

        if (test.Tag_Bao_Phu && test.Tag_Bao_Phu.includes('SEC_XSS')) {
            I.dontSeeInSource("<script>alert('XSS')</script>");
        }
    });
});