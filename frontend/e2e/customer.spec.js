import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { CUSTOMERS, OWNERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Customer purchase request journey', () => {
  test('a customer requests an available public unit, then cancels it', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.salma.email)

    // Browse to a genuinely available unit through the public catalog.
    await page.goto('/properties/1')
    await page.waitForLoadState('networkidle')

    const row = page.getByRole('row').filter({ has: page.getByRole('button', { name: /^request$/i }) }).first()
    const unitNumber = (await row.getByRole('cell').first().innerText()).trim()
    await row.getByRole('button', { name: /^request$/i }).click()

    await expect(page.getByText(/request submitted for unit/i)).toBeVisible()

    // It appears in the customer's own list.
    await page.goto('/purchase-requests')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toContain(unitNumber)

    // Open it and cancel.
    await page.getByRole('row', { name: new RegExp(unitNumber) }).getByRole('link', { name: /^view$/i }).click()
    await page.waitForURL(/\/purchase-requests\/\d+$/)
    await page.waitForLoadState('networkidle')

    await expect(page.getByText('Pending')).toBeVisible()
    await page.getByRole('button', { name: /cancel request/i }).click()
    await expect(page.getByText(/request cancelled/i)).toBeVisible()
    await expect(page.getByText('Cancelled').first()).toBeVisible()
  })

  test('a second open request for the same unit is refused', async ({ page }) => {
    // Youssef holds no seeded request in Nile View, so the first request
    // here is genuinely his first for this unit.
    await loginAsCustomer(page, CUSTOMERS.youssef.email)

    await page.goto('/properties/1')
    await page.waitForLoadState('networkidle')

    const row = page.getByRole('row').filter({ has: page.getByRole('button', { name: /^request$/i }) }).first()
    const unitNumber = (await row.getByRole('cell').first().innerText()).trim()

    await row.getByRole('button', { name: /^request$/i }).click()
    await expect(page.getByText(/request submitted for unit/i)).toBeVisible()

    // Ask for the very same unit again.
    await page.reload({ waitUntil: 'networkidle' })
    await page.getByRole('row', { name: new RegExp(unitNumber) })
      .getByRole('button', { name: /^request$/i }).click()
    await expect(page.getByText(/already have an open request/i)).toBeVisible()
  })

  test('a unit that is not available offers no request button', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)

    // A-101 is seeded as occupied.
    await page.goto('/units/1')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toMatch(/currently occupied/i)
    await expect(page.getByRole('button', { name: /request this unit/i })).toHaveCount(0)
  })

  test('the owner of the unit sees the new request', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.karim.email)
    await page.goto('/properties/1')
    await page.waitForLoadState('networkidle')
    await page.getByRole('button', { name: /^request$/i }).first().click()
    await expect(page.getByText(/request submitted/i)).toBeVisible()

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/purchase-requests')
    await page.waitForLoadState('networkidle')

    const ownerText = await bodyText(page)
    expect(ownerText).toContain(CUSTOMERS.karim.name)
    expect(ownerText).toContain(PROPERTIES.nileView.name)
  })
})

test.describe('Customer account pages', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
  })

  test('contracts, payments and requests all load real data', async ({ page }) => {
    await page.goto('/contracts')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toContain(PROPERTIES.nileView.name)

    await page.goto('/payments')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toMatch(/PAY-2026-\d{4}/)

    await page.goto('/purchase-requests')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).not.toContain('PR-1001')
  })
})
