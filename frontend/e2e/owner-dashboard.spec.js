import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsOwner } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.waitForLoadState('networkidle')
  })

  test('greets the authenticated owner rather than a hardcoded name', async ({ page }) => {
    await expect(page.getByRole('heading', { name: new RegExp(OWNERS.hassan.name) })).toBeVisible()
    const text = await bodyText(page)
    expect(text).not.toContain('Administrator')
    expect(text).not.toContain('John Doe')
  })

  test('renders aggregates from the API, not invented numbers', async ({ page }) => {
    const text = await bodyText(page)

    // The mock dashboard used to claim these.
    expect(text).not.toContain('EGP 250K')
    expect(text).not.toContain('+12.5% vs last month')

    for (const label of ['Properties', 'Units', 'Occupied Units', 'Customers', 'Pending Requests', 'Expected Rent', 'Collected', 'Overdue']) {
      await expect(page.getByText(label, { exact: true }).first()).toBeVisible()
    }
  })

  test('the property overview lists only this owner properties with unit counts', async ({ page }) => {
    const overview = page.getByRole('table').first()
    await expect(overview.getByRole('cell', { name: PROPERTIES.nileView.name })).toBeVisible()
    await expect(overview.getByRole('cell', { name: PROPERTIES.palmGardens.name })).toBeVisible()
    expect(await bodyText(page)).not.toContain(PROPERTIES.marina.name)
  })

  test('recent payments and recent requests show seeded records', async ({ page }) => {
    const text = await bodyText(page)
    expect(text).toMatch(/PAY-2026-\d{4}/)
    await expect(page.getByRole('heading', { name: /recent payments/i })).toBeVisible()
    await expect(page.getByRole('heading', { name: /recent purchase requests/i })).toBeVisible()
  })

  test('quick actions link to the real create screens', async ({ page }) => {
    for (const [name, path] of [
      [/add property/i, '/owner/properties/create'],
      [/add building/i, '/owner/buildings/create'],
      [/add unit/i, '/owner/units/create'],
      [/create contract/i, '/owner/contracts/create'],
    ]) {
      await expect(page.getByRole('link', { name })).toHaveAttribute('href', path)
    }
  })

  test('the dashboard survives a reload while authenticated', async ({ page }) => {
    await page.reload({ waitUntil: 'networkidle' })
    expect(page.url()).toContain('/owner/dashboard')
    await expect(page.getByRole('heading', { name: new RegExp(OWNERS.hassan.name) })).toBeVisible()
  })
})
