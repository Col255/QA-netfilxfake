const { loadCsvDataAndCoverage } = require('../steps_file');

const { table, tracker } = loadCsvDataAndCoverage('design_testcase_master.csv');

Feature('MASTER TEST SUITE: Tổng Hợp 5 Phân Hệ Data-Driven Từ CSV (76 Test Cases)');

Data(table).Scenario(
  'Thực thi Test Case từ design_testcase_master.csv @MASTER',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('MASTER SUITE: TOÀN BỘ 5 PHÂN HỆ (AUTH + MOV + INT + VIP + ADM)');
});
