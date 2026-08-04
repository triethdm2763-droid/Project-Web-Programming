/** @type {CodeceptJS.MainConfig} */
export const config = {
  tests: './e2e/*_test.js',
  output: './output',
  helpers: {
    Playwright: {
      browser: 'chromium',
      url: 'http://localhost/Project-Web-Programming-codeceptJS-v2',
      show: true,
      restart: 'session', // Tự động giữ/mở lại trang nếu browser bị đóng
      waitForTimeout: 5000,
      waitForNavigation: 'domcontentloaded'
    }
  },
  include: {
    I: './steps_file.js'
  },
  noGlobals: true,
  plugins: {},
  name: 'Project-Web-Programming-codeceptJS-v2'
}