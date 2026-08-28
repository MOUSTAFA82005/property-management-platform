import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { CUSTOMERS, OWNERS, PASSWORD } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner, unique } from './helpers/app.js'
import { apiLogin } from './helpers/api.js'
import { API_URL } from '../playwright.config.js'

test.beforeAll(() => prepareDatabase())

test.describe('Customer profile', () => {
  test('loads the authenticated user and saves an edit that persists', async ({ page }) => {
    const newName = unique('Omar')

    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/profile')
    await page.waitForLoadState('networkidle')

    await expect(page.getByText(CUSTOMERS.omar.email).first()).toBeVisible()

    await page.getByRole('button', { name: /edit profile/i }).click()
    await page.getByLabel('Full Name').fill(newName)
    await page.getByRole('button', { name: /save changes/i }).click()

    await expect(page.getByText(/profile updated/i)).toBeVisible()

    // Persisted server-side, not just in the store.
    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).toContain(newName)
  })

  test('shows validation errors from the API', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.salma.email)
    await page.goto('/profile')
    await page.waitForLoadState('networkidle')

    await page.getByRole('button', { name: /edit profile/i }).click()
    await page.getByLabel('Phone').fill('not-a-phone-number')
    await page.getByRole('button', { name: /save changes/i }).click()

    await expect(page.getByText(/valid Egyptian mobile number/i)).toBeVisible()
  })

  test('changing a password requires the current one', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.dina.email)
    await page.goto('/profile')
    await page.waitForLoadState('networkidle')

    await page.getByRole('button', { name: /edit profile/i }).click()
    await page.getByLabel('Current Password').fill('definitely-wrong')
    await page.getByLabel('New Password', { exact: true }).fill('brand-new-password')
    await page.getByLabel('Confirm New Password').fill('brand-new-password')
    await page.getByRole('button', { name: /save changes/i }).click()

    await expect(page.getByText(/not your current password/i)).toBeVisible()
  })
})

test.describe('Owner profile', () => {
  test('shows the authenticated owner, never a hardcoded role label', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/profile')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain(OWNERS.hassan.name)
    expect(text).toContain(OWNERS.hassan.email)
    expect(text).not.toContain('Administrator')
  })

  test('an owner can edit their own profile', async ({ page }) => {
    const newName = unique('Hassan')

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/profile')
    await page.waitForLoadState('networkidle')

    await page.getByRole('button', { name: /edit profile/i }).click()
    await page.getByLabel('Name', { exact: true }).fill(newName)
    await page.getByRole('button', { name: /save changes/i }).click()

    await expect(page.getByText(/profile updated/i)).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).toContain(newName)
  })
})

test.describe('Profile cannot be used to escalate privileges', () => {
  test('role and status are ignored when sent to the profile endpoint', async ({ request }) => {
    const token = await apiLogin(request, CUSTOMERS.karim.email, PASSWORD)

    const response = await request.put(`${API_URL}/api/profile`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      data: { name: 'Escalation Attempt', role: 'owner', status: 'inactive' },
    })

    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body.user.role).toBe('customer')
    expect(body.user.status).toBe('active')

    // And the account really still behaves as a customer.
    const ownerArea = await request.get(`${API_URL}/api/owner/dashboard`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    })
    expect(ownerArea.status()).toBe(403)
  })

  test('a customer cannot take another user email', async ({ request }) => {
    const token = await apiLogin(request, CUSTOMERS.karim.email, PASSWORD)

    const response = await request.put(`${API_URL}/api/profile`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      data: { email: CUSTOMERS.omar.email },
    })

    expect(response.status()).toBe(422)
  })

  test('the profile endpoint always answers for the token holder', async ({ request }) => {
    const token = await apiLogin(request, CUSTOMERS.salma.email, PASSWORD)

    // A spoofed id in the query string must change nothing.
    const response = await request.get(`${API_URL}/api/profile?id=1`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    })

    expect((await response.json()).user.email).toBe(CUSTOMERS.salma.email)
  })
})
