const { loadCsvDataAndCoverage } = require('../steps_file');

const { table, tracker } = loadCsvDataAndCoverage('design_testcase_mov.csv');

Feature('Phân Hệ 2: MOVIES & SEARCH (MOV) — Kiểm Thử Data-Driven Từ CSV (13 Test Cases)');

Data(table).Scenario(
  'Thực thi Test Case từ design_testcase_mov.csv @MOV',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('PHÂN HỆ 2: MOVIES, SEARCH & STREAMING (MOV)');
});
