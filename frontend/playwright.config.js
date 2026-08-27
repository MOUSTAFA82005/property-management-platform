import { defineConfig, devices } from '@playwright/test'

const API_PORT = 8001
const WEB_PORT = 4173

export const API_URL = `http://127.0.0.1:${API_PORT}`
export const WEB_URL = `http://127.0.0.1:${WEB_PORT}`

export default defineConfig({
  testDir: './e2e',
  globalSetup: './e2e/global-setup.js',

  // Specs share one SQLite database and each resets it, so they must not run
  // concurrently. Correctness over speed: the whole suite is well under a minute.
  fullyParallel: false,
  workers: 1,

  timeout: 30_000,
  expect: { timeout: 7_000 },

  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,

  reporter: process.env.CI
    ? [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]]
    : [['list']],

  use: {
    baseURL: WEB_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

  // Both servers run against the throwaway E2E database and ports.
  webServer: [
    {
      command: `php artisan serve --env=e2e --host=127.0.0.1 --port=${API_PORT}`,
      cwd: '../backend',
      // Laravel's built-in health route. Playwright starts webServer entries
      // *before* globalSetup, so the readiness check must not depend on the
      // database — globalSetup is what creates it.
      url: `${API_URL}/up`,
      reuseExistingServer: !process.env.CI,
      timeout: 60_000,
      stdout: 'ignore',
      stderr: 'pipe',
    },
    {
      command: `npx vite preview --mode e2e --host 127.0.0.1 --port ${WEB_PORT} --strictPort`,
      url: WEB_URL,
      reuseExistingServer: !process.env.CI,
      timeout: 60_000,
      stdout: 'ignore',
      stderr: 'pipe',
    },
  ],
})
