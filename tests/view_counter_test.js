const { loadCsvDataAndCoverage } = require('../steps_file');

// Đọc trực tiếp các Test Case thuộc nhóm Bộ đếm lượt xem (/api/view_counter.php) từ design_testcase_int.csv
const { table, tracker } = loadCsvDataAndCoverage(
  'design_testcase_int.csv',
  row => row.Endpoint === '/api/view_counter.php'
);

Feature('KAN-33: Kiểm thử RESTful API Bộ đếm Lượt xem Phim Data-Driven Từ CSV (/api/view_counter.php)');

Data(table).Scenario(
  'Thực thi Test Case Bộ đếm lượt xem từ design_testcase_int.csv @KAN_33',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('KAN-33: API BỘ ĐẾM LƯỢT XEM (/api/view_counter.php)');
});
