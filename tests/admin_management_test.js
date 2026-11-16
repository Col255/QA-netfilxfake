const { loadCsvDataAndCoverage } = require('../steps_file');

const { table, tracker } = loadCsvDataAndCoverage('design_testcase_adm.csv');

Feature('Phân Hệ 5: ADMIN MANAGEMENT CMS (ADM) — Kiểm Thử Data-Driven Từ CSV (13 Test Cases)');

Data(table).Scenario(
  'Thực thi Test Case từ design_testcase_adm.csv @ADM',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('PHÂN HỆ 5: ADMIN MANAGEMENT CMS (ADM)');
});
