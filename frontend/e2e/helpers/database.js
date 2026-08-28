import { execFileSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'
import { dirname, resolve } from 'node:path'

const backendDir = resolve(dirname(fileURLToPath(import.meta.url)), '../../../backend')

/**
 * Rebuild the E2E database from migrations + DemoDataSeeder.
 *
 * `--env=e2e` makes Laravel load backend/.env.e2e, which points at a
 * throwaway SQLite file. A developer's own database is never touched, no
 * matter what is configured in backend/.env.
 *
 * `shell: true` is what makes this work on Windows, where `php` on PATH is
 * usually a `.bat`/`.cmd` shim rather than an executable — Node will not
 * spawn one directly and fails with ENOENT. The arguments are fixed literals
 * with no spaces or shell metacharacters, so routing them through a shell is
 * safe; only `cwd` contains spaces, and that is not shell-expanded.
 */
export function prepareDatabase() {
  execFileSync('php', ['artisan', 'migrate:fresh', '--seed', '--force', '--env=e2e'], {
    cwd: backendDir,
    shell: true,
    stdio: 'pipe',
    env: { ...process.env, APP_ENV: 'e2e' },
  })
}
