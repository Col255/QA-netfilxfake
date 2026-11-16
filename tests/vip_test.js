const { loadCsvDataAndCoverage } = require('../steps_file');

const { table, tracker } = loadCsvDataAndCoverage('design_testcase_vip.csv');

Feature('Phân Hệ 4: VIP PACKAGES & PAYMENT (VIP) — Kiểm Thử Data-Driven Từ CSV (11 Test Cases)');

Data(table).Scenario(
  'Thực thi Test Case từ design_testcase_vip.csv @VIP',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('PHÂN HỆ 4: VIP PACKAGES & PAYMENT (VIP)');
});
