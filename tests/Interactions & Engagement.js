const { loadCsvDataAndCoverage } = require('../steps_file');

const { table, tracker } = loadCsvDataAndCoverage('design_testcase_int.csv');

Feature('Phân Hệ 3: INTERACTIONS & ENGAGEMENT (INT) — Kiểm Thử Data-Driven Từ CSV (15 Test Cases)');

Data(table).Scenario(
  'Thực thi Test Case từ design_testcase_int.csv @INT',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('PHÂN HỆ 3: INTERACTIONS & ENGAGEMENT (INT)');
});
