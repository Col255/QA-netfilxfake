const { loadCsvDataAndCoverage } = require('../steps_file');

// Đọc trực tiếp các Test Case thuộc nhóm Quản lý & Thêm phim (/add_movie.php, /manage_movies.php) từ design_testcase_adm.csv
const { table, tracker } = loadCsvDataAndCoverage(
  'design_testcase_adm.csv',
  row => row.Endpoint === '/add_movie.php' || row.Endpoint === '/manage_movies.php'
);

Feature('KAN-37: Kiểm thử Phân quyền Quản trị & CRUD Nội dung Phim Data-Driven Từ CSV (/add_movie.php, /manage_movies.php)');

Data(table).Scenario(
  'Thực thi Test Case Quản trị phim từ design_testcase_adm.csv @KAN_37',
  async ({ I, current }) => {
    await I.executeDataDrivenTestCase(current, tracker);
  }
);

AfterSuite(() => {
  tracker.printCoverageReport('KAN-37: QUẢN TRỊ NỘI DUNG PHIM ADMIN (/add_movie.php, /manage_movies.php)');
});
