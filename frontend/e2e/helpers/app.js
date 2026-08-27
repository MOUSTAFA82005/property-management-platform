import { expect } from '@playwright/test'
import { PASSWORD } from './accounts.js'

/**
 * Shared page interactions. Login lives here rather than being repeated in
 * every spec, and every helper waits on something observable rather than a
 * fixed sleep.
 */

export async function login(page, email, password = PASSWORD) {
  // Signing in as somebody else mid-spec has to start from a clean slate: the
  // router bounces an already-authenticated visitor away from /login, so the
  // form would never appear.
  await page.goto('/')
  await page.evaluate(() => {
    window.localStorage.removeItem('token')
    window.localStorage.removeItem('user')
  })

  await page.goto('/login')
  await page.getByLabel('Email Address').fill(email)
  await page.getByLabel('Password', { exact: true }).fill(password)
  await page.getByRole('button', { name: /^login$/i }).click()
}

export async function loginAsOwner(page, email) {
  await login(page, email)
  await page.waitForURL('**/owner/dashboard')
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
}

export async function loginAsCustomer(page, email) {
  await login(page, email)
  // Customers land on the public home page.
  await page.waitForURL((url) => new URL(url).pathname === '/')
}

export async function logout(page) {
  const sidebar = page.getByTestId('logout-sidebar')
  const button = (await sidebar.count()) > 0 ? sidebar : page.getByTestId('logout')
  await button.first().click()
  await page.waitForURL('**/login')
}

/** The token the SPA is currently holding, or null. */
export function storedToken(page) {
  return page.evaluate(() => window.localStorage.getItem('token'))
}

export async function setStoredToken(page, token) {
  await page.evaluate((value) => {
    window.localStorage.setItem('token', value)
    window.localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Ghost', role: 'owner' }))
  }, token)
}

/** Wait for a table/list page to finish its initial request. */
export async function waitForContent(page) {
  await page.waitForLoadState('networkidle')
}

/** Visible text of the whole page — used for "must not contain" assertions. */
export function bodyText(page) {
  return page.locator('body').innerText()
}

/** A unique suffix so repeated local runs never collide on unique columns. */
export function unique(prefix) {
  return `${prefix}-${Date.now().toString().slice(-6)}${Math.floor(Math.random() * 100)}`
}

/** Poll the rendered page text until `predicate` holds, then return it. */
export async function textEventually(page, predicate, timeout = 7000) {
  const deadline = Date.now() + timeout
  let text = ''
  while (Date.now() < deadline) {
    text = await bodyText(page)
    if (predicate(text)) return text
    await page.waitForTimeout(150)
  }
  return text
}
