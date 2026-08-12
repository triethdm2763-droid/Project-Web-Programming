import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectFolder = path.basename(__dirname);
const baseUrl = process.env.CODECEPT_BASE_URL || `http://localhost/${projectFolder}`;
const showBrowser = process.env.CODECEPT_SHOW !== 'false';

/** @type {CodeceptJS.MainConfig} */
export const config = {
  tests: './e2e/*_test.js',
  output: './output',
  helpers: {
    Playwright: {
      browser: 'chromium',
      url: baseUrl,
      show: showBrowser
    }
  },
  include: {
    I: './steps_file.js'
  },
  noGlobals: true,
  plugins: {},
  name: 'Project-Web-Programming'
}
