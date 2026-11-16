const { loadCsvDataAndCoverage } = require('../steps_file');

// Đọc trực tiếp các Test Case thuộc nhóm Xem phim (/watch.php - MOV-03) từ design_testcase_mov.csv
const { table, tracker } = loadCsvDataAndCoverage(
  'design_testcase_mov.csv',
  row => row.Endpoint === '/watch.php' || row.Jira_Task === 'MOV-03'
);

Feature('KAN-29: Kiểm thử Trình chiếu Video & Phân quyền Xem Phim Data-Driven Từ CSV (/watch.php)');

Data(table).Scenario(
  'Thực thi Test Case Xem phim từ design_testcase_mov.csv @KAN_29',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('KAN-29: TRÌNH CHIẾU VIDEO & STREAMING (/watch.php)');
});
