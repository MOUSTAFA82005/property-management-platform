import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, CUSTOMERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner } from './helpers/app.js'
import { apiLogin, apiGet } from './helpers/api.js'
import { API_URL } from '../playwright.config.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner contracts', () => {
  test('lists contracts for this owner units, showing the customer', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/contracts')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain(CUSTOMERS.omar.name)
    // Nadia's tenant must not appear.
    expect(text).not.toContain(CUSTOMERS.youssef.name)
  })

  test('creating a contract occupies the unit and shows up in the list', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)

    await page.goto('/owner/contracts/create')
    await page.waitForLoadState('networkidle')

    // Capture which unit we are letting so the status change can be verified.
    await expect(page.getByLabel('Unit').locator('option')).not.toHaveCount(1)
    const unitOption = await page.getByLabel('Unit').locator('option').nth(1).innerText()
    const unitNumber = unitOption.split('—')[1].trim().split(' ')[0]

    await page.getByLabel('Customer').selectOption({ index: 1 })
    await page.getByLabel('Unit').selectOption({ index: 1 })
    await page.getByLabel('Start date').fill('2026-09-01')
    await page.getByLabel('End date').fill('2027-08-31')
    await page.getByRole('button', { name: /create contract/i }).click()

    await page.waitForURL(/\/owner\/contracts(\/\d+)?$/)
    await page.waitForLoadState('networkidle')

    // The unit the contract was written against is now occupied.
    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')
    const row = page.getByRole('row', { name: new RegExp(unitNumber) })
    await expect(row.getByText('Occupied')).toBeVisible()

    // And it is no longer offered when creating another contract.
    await page.goto('/owner/contracts/create')
    await page.waitForLoadState('networkidle')
    const remaining = (await page.getByLabel('Unit').locator('option').allInnerTexts()).join(' ')
    expect(remaining).not.toContain(unitNumber)
  })

  test('an owner cannot write a contract against another owner unit', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const nadia = await apiLogin(request, OWNERS.nadia.email)

    const nadiaUnits = (await (await apiGet(request, nadia, '/owner/units?status=available')).json()).data
    expect(nadiaUnits.length).toBeGreaterThan(0)

    const response = await request.post(`${API_URL}/api/owner/contracts`, {
      headers: { Authorization: `Bearer ${hassan}`, Accept: 'application/json' },
      data: {
        user_id: 3,
        unit_id: nadiaUnits[0].id,
        start_date: '2026-09-01',
        end_date: '2027-08-31',
        monthly_rent: 1000,
        security_deposit: 2000,
        status: 'active',
      },
    })

    expect(response.status()).toBe(403)
  })

  test('the customer sees their own contract with the right unit and property', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/contracts')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain(PROPERTIES.nileView.name)
    expect(text).toContain('A-101')

    await page.getByRole('link', { name: /^view$/i }).first().click()
    await page.waitForURL(/\/contracts\/\d+$/)
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('heading', { name: /CTR-\d+/ })).toBeVisible()
    await expect(page.getByRole('heading', { name: /payment schedule/i })).toBeVisible()
  })
})
