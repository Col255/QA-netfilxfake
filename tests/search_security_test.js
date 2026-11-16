const { loadCsvDataAndCoverage } = require('../steps_file');

// Đọc trực tiếp các Test Case thuộc nhóm Tìm kiếm (/search.php - MOV-02) từ design_testcase_mov.csv
const { table, tracker } = loadCsvDataAndCoverage(
  'design_testcase_mov.csv',
  row => row.Endpoint === '/search.php' || row.Jira_Task === 'MOV-02'
);

Feature('KAN-25: Kiểm thử API Tìm kiếm Phim & Bảo mật Data-Driven Từ CSV (/search.php)');

Data(table).Scenario(
  'Thực thi Test Case Tìm kiếm từ design_testcase_mov.csv @KAN_25',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('KAN-25: TÌM KIẾM PHIM & BẢO MẬT (/search.php)');
});
