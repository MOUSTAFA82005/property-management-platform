import { prepareDatabase } from './helpers/database.js'

/**
 * Build the E2E database before any test runs.
 *
 * Playwright launches the webServer entries before this hook, which is why the
 * backend readiness probe points at /up rather than a database-backed route.
 * Each spec file re-seeds in its own beforeAll, so specs stay independent.
 */
export default function globalSetup() {
  prepareDatabase()
}
