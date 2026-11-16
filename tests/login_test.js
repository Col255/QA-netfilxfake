const { loadCsvDataAndCoverage } = require('../steps_file');

const { table, tracker } = loadCsvDataAndCoverage('design_testcase_auth.csv');

Feature('Phân Hệ 1: AUTH & SECURITY — Kiểm Thử Data-Driven Từ CSV (24 Test Cases)');

Data(table).Scenario(
  'Thực thi Test Case từ design_testcase_auth.csv @AUTH',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('PHÂN HỆ 1: AUTHENTICATION & SECURITY (AUTH)');
});
