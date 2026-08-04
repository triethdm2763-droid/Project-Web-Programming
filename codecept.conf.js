/** @type {CodeceptJS.MainConfig} */
export const config = {
  tests: './e2e/*_test.js',
  output: './output',

  helpers: {
    Playwright: {
      browser: 'chromium',
      url: 'http://localhost/Project-Web-Programming',
      show: true
    }
  },

  include: {
    I: './steps_file.js'
  },

  plugins: {},

  name: 'Project-Web-Programming'
}