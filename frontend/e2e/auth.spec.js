import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, CUSTOMERS, PASSWORD } from './helpers/accounts.js'
import { bodyText, login, loginAsCustomer, loginAsOwner, logout, setStoredToken, storedToken, unique } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Registration', () => {
  test('a visitor can register and is signed in immediately', async ({ page }) => {
    const email = `${unique('e2e')}@example.com`

    await page.goto('/register')
    await page.getByLabel('Full Name').fill('E2E Newcomer')
    await page.getByLabel('Email Address').fill(email)
    await page.getByLabel('Password', { exact: true }).fill('secret-password')
    await page.getByLabel('Confirm Password').fill('secret-password')
    await page.getByRole('button', { name: /register/i }).click()

    // Registration returns a token, so the new customer lands signed in.
    await page.waitForURL((url) => new URL(url).pathname === '/')
    expect(await storedToken(page)).not.toBeNull()

    await page.goto('/profile')
    await page.waitForLoadState('networkidle')
    await expect(page.getByText(email).first()).toBeVisible()
  })

  test('server-side validation errors are shown on the form', async ({ page }) => {
    await page.goto('/register')
    await page.getByRole('button', { name: /register/i }).click()
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toMatch(/name field is required/i)
    expect(text).toMatch(/email field is required/i)
    expect(text).toMatch(/password field is required/i)
  })

  test('a duplicate email is rejected and typed values survive', async ({ page }) => {
    await page.goto('/register')
    await page.getByLabel('Full Name').fill('Duplicate Attempt')
    await page.getByLabel('Email Address').fill(CUSTOMERS.omar.email)
    await page.getByLabel('Password', { exact: true }).fill('secret-password')
    await page.getByLabel('Confirm Password').fill('secret-password')
    await page.getByRole('button', { name: /register/i }).click()
    await page.waitForLoadState('networkidle')

    expect(await bodyText(page)).toMatch(/already been taken/i)
    await expect(page.getByLabel('Full Name')).toHaveValue('Duplicate Attempt')
    expect(await storedToken(page)).toBeNull()
  })

  test('a mismatched password confirmation is rejected', async ({ page }) => {
    await page.goto('/register')
    await page.getByLabel('Full Name').fill('Mismatch')
    await page.getByLabel('Email Address').fill(`${unique('mm')}@example.com`)
    await page.getByLabel('Password', { exact: true }).fill('secret-password')
    await page.getByLabel('Confirm Password').fill('different-password')
    await page.getByRole('button', { name: /register/i }).click()
    await page.waitForLoadState('networkidle')

    expect(await bodyText(page)).toMatch(/confirmation does not match/i)
  })
})

test.describe('Login', () => {
  test('a customer signs in and reaches the customer area', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    expect(await storedToken(page)).not.toBeNull()

    await page.goto('/profile')
    await page.waitForLoadState('networkidle')
    await expect(page.getByText(CUSTOMERS.omar.name).first()).toBeVisible()
  })

  test('an owner signs in and is redirected to the owner dashboard', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    expect(page.url()).toContain('/owner/dashboard')
    await expect(page.getByText(OWNERS.hassan.name).first()).toBeVisible()
  })

  test('invalid credentials are rejected without creating a session', async ({ page }) => {
    await login(page, OWNERS.hassan.email, 'wrong-password')
    await page.waitForLoadState('networkidle')

    expect(await bodyText(page)).toMatch(/do not match our records/i)
    expect(page.url()).toContain('/login')
    expect(await storedToken(page)).toBeNull()
  })

  test('an unknown email is rejected the same way', async ({ page }) => {
    await login(page, 'nobody@example.com', PASSWORD)
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toMatch(/do not match our records/i)
  })

  test('missing fields are caught by backend validation', async ({ page }) => {
    await page.goto('/login')
    await page.getByRole('button', { name: /^login$/i }).click()
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toMatch(/required/i)
  })
})

test.describe('Session lifecycle', () => {
  test('a reload preserves an authenticated owner session', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)

    await page.reload({ waitUntil: 'networkidle' })

    expect(page.url()).toContain('/owner/dashboard')
    await expect(page.getByText(OWNERS.hassan.name).first()).toBeVisible()
  })

  test('a reload preserves an authenticated customer session', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/purchase-requests')
    await page.waitForLoadState('networkidle')

    await page.reload({ waitUntil: 'networkidle' })

    expect(page.url()).toContain('/purchase-requests')
    expect(page.url()).not.toContain('/login')
  })

  test('logout clears the session and revokes the token server-side', async ({ page, request }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    const token = await storedToken(page)

    await logout(page)

    expect(await storedToken(page)).toBeNull()

    // The token must be dead on the server, not merely forgotten by the client.
    const { API_URL } = await import('../playwright.config.js')
    const response = await request.get(`${API_URL}/api/auth/me`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    })
    expect(response.status()).toBe(401)
  })

  test('an invalid token is discarded and the user is sent to login', async ({ page }) => {
    await page.goto('/login')
    await setStoredToken(page, '999|not-a-real-token')

    await page.goto('/owner/dashboard')
    await page.waitForURL('**/login**')

    expect(await storedToken(page)).toBeNull()
  })

  test('protected customer pages require authentication', async ({ page }) => {
    for (const path of ['/profile', '/contracts', '/payments', '/purchase-requests']) {
      await page.goto(path)
      await page.waitForURL('**/login**')
      expect(page.url()).toContain('/login')
    }
  })

  test('protected owner pages require authentication', async ({ page }) => {
    for (const path of ['/owner/dashboard', '/owner/properties', '/owner/payments']) {
      await page.goto(path)
      await page.waitForURL('**/login**')
      expect(page.url()).toContain('/login')
    }
  })

  test('a signed-in user is kept away from the login and register screens', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)

    await page.goto('/login')
    await page.waitForURL((url) => new URL(url).pathname === '/')

    await page.goto('/register')
    await page.waitForURL((url) => new URL(url).pathname === '/')
  })
})
