exports.config = {
  tests: './tests/*.js',
  output: './output',
  helpers: {
    Playwright: {
      url: process.env.BASE_URL || 'http://localhost/netfix2',
      show: !process.env.CI,
      browser: 'chromium',
      waitForNavigation: 'domcontentloaded',
      waitForTimeout: 5000
    },
    REST: {
      endpoint: process.env.BASE_URL || 'http://localhost/netfix2',
      defaultHeaders: {
        'Accept': 'application/json'
      }
    }
  },
  include: {
    I: './steps_file.js'
  },
  name: 'QA netflix fake'
};