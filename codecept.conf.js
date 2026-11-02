exports.config = {
    tests: './tests/*_test.js',
    output: './output',
    helpers: {
        Playwright: {
            url: process.env.BASE_URL || 'http://localhost/netfix2',
            show: !process.env.CI, // Local: Hiện cửa sổ trình duyệt; GitHub Actions (CI): Tự động chạy ngầm (headless)
            browser: 'chromium',
            waitForNavigation: 'domcontentloaded',
            waitForTimeout: 5000
        }
    },
    include: {
        I: './steps_file.js'
    },
    name: 'QA netflix fake'
};