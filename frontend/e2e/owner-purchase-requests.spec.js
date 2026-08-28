import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, CUSTOMERS } from './helpers/accounts.js'
import { bodyText, loginAsOwner } from './helpers/app.js'
import { apiLogin, apiGet } from './helpers/api.js'
import { API_URL } from '../playwright.config.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner purchase requests', () => {
  test('lists only requests raised against this owner units', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/purchase-requests')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain(CUSTOMERS.salma.name)
    // The mock request ids that used to be hardcoded here.
    expect(text).not.toContain('PR-1001')
  })

  test('approving a pending request reserves the unit', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/purchase-requests?status=pending')
    await page.waitForLoadState('networkidle')

    const row = page.getByRole('row').filter({ has: page.getByRole('button', { name: /^approve$/i }) }).first()
    const unitNumber = (await row.getByRole('cell').nth(3).innerText()).trim()

    await row.getByRole('button', { name: /^approve$/i }).click()
    await expect(page.getByText(/approved/i).first()).toBeVisible()

    // The unit the request pointed at is now reserved.
    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')
    await expect(
      page.getByRole('row', { name: new RegExp(unitNumber) }).getByText('Reserved'),
    ).toBeVisible()
  })

  test('rejecting a pending request leaves the unit alone', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/purchase-requests?status=pending')
    await page.waitForLoadState('networkidle')

    const row = page.getByRole('row').filter({ has: page.getByRole('button', { name: /^reject$/i }) }).first()
    const unitNumber = (await row.getByRole('cell').nth(3).innerText()).trim()

    await row.getByRole('button', { name: /^reject$/i }).click()
    await expect(page.getByText(/rejected/i).first()).toBeVisible()

    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')
    await expect(
      page.getByRole('row', { name: new RegExp(unitNumber) }).getByText('Available'),
    ).toBeVisible()
  })

  test('a request that is already decided cannot be decided again', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const approved = (await (await apiGet(request, hassan, '/owner/purchase-requests?status=approved')).json()).data[0]
    expect(approved).toBeTruthy()

    const headers = { Authorization: `Bearer ${hassan}`, Accept: 'application/json' }
    const again = await request.post(`${API_URL}/api/owner/purchase-requests/${approved.id}/approve`, { headers })
    expect(again.status()).toBe(422)

    const rejectInstead = await request.post(`${API_URL}/api/owner/purchase-requests/${approved.id}/reject`, { headers })
    expect(rejectInstead.status()).toBe(422)
  })

  test('a customer cannot decide their own request', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const omar = await apiLogin(request, CUSTOMERS.omar.email)

    const pending = (await (await apiGet(request, hassan, '/owner/purchase-requests?status=pending')).json()).data[0]
    expect(pending).toBeTruthy()

    // Approval is the owner's decision alone — the customer-facing token must
    // not reach the owner endpoints at all.
    const headers = { Authorization: `Bearer ${omar}`, Accept: 'application/json' }
    expect((await request.post(`${API_URL}/api/owner/purchase-requests/${pending.id}/approve`, { headers })).status()).toBe(403)
    expect((await request.post(`${API_URL}/api/owner/purchase-requests/${pending.id}/reject`, { headers })).status()).toBe(403)
  })

  test('the detail page shows the customer and unit behind a request', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/purchase-requests')
    await page.waitForLoadState('networkidle')
    await page.getByRole('link', { name: /review/i }).first().click()

    await page.waitForURL(/\/owner\/purchase-requests\/\d+$/)
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('heading', { name: /Request #\d+/ })).toBeVisible()
    const field = (label) => page.locator('.owner-field').filter({ hasText: label }).locator('input')
    await expect(field('Customer').first()).not.toHaveValue('')
    await expect(field('Unit').first()).not.toHaveValue('')
  })
})
