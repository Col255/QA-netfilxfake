const fs = require('fs');
const path = require('path');
const { parse } = require('csv-parse/sync');

Feature('ADM - Data-Driven Testing from CSV (Admin Management)');

const csvFilePath = path.resolve(__dirname, '../docs/Design/design_testcase_adm.csv');
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
    Scenario(`[${test.Jira_Task || 'ADM'}] ${test.Ten_Test_Case}`, ({ I }) => {
        const endpoint = test.Endpoint;
        I.amOnPage(endpoint);

        I.dontSee('Fatal error');
        I.dontSee('Parse error');
        I.seeInCurrentUrl('login.php');

        if (test.Tag_Bao_Phu && test.Tag_Bao_Phu.includes('SEC_SQLI')) {
            I.dontSee('SQL syntax');
            I.dontSee('mysql_fetch');
        }
    });
});