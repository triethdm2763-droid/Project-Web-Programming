import path from 'node:path';

const projectDirectory = encodeURIComponent(path.basename(process.cwd()));
const baseUrl = process.env.E2E_BASE_URL || `http://localhost/${projectDirectory}`;

/** @type {CodeceptJS.MainConfig} */
export const config = {
  tests: './e2e/*_test.js',
  output: './output',
  helpers: {
    Playwright: {
      browser: 'chromium',
      url: baseUrl,
      show: true
    }
  },
  include: {
    I: './steps_file.js'
  },
  noGlobals: true,
  plugins: {},
  name: 'Project-Web-Programming'
}
