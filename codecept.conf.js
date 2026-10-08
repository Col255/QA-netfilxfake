exports.config = {
    tests: './tests/*_test.js',
    output: './output',
    helpers: {
        Playwright: {
            url: 'http://localhost/netfix2',
            show: true, // true: Hiện cửa sổ trình duyệt khi test; false: Chạy ngầm
            browser: 'chromium'
        }
    },
    include: {
        I: './steps_file.js'
    },
    name: 'QA netflix fake'
};