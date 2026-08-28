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
    // Every seeded contract is on one of the owner's units, across both
    // published properties.
    expect(text).toContain(CUSTOMERS.omar.name)
    expect(text).toContain(CUSTOMERS.youssef.name)
  })

  test('creating a contract occupies the unit and shows up in the list', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)

    await page.goto('/owner/contracts/create')
    await page.waitForLoadState('networkidle')

    // Capture which unit we are letting so the status change can be verified.
    // A reserved unit is only lettable to the customer whose approved request
    // reserved it, so this picks a free one.
    await expect(page.getByLabel('Unit').locator('option')).not.toHaveCount(1)
    const unitOptions = await page.getByLabel('Unit').locator('option').allInnerTexts()
    const freeIndex = unitOptions.findIndex((t, i) => i > 0 && !t.includes('reserved'))
    expect(freeIndex).toBeGreaterThan(0)
    const unitNumber = unitOptions[freeIndex].split('—')[1].trim().split(' ')[0]

    await page.getByLabel('Customer').selectOption({ index: 1 })
    await page.getByLabel('Unit').selectOption({ index: freeIndex })
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

  test('an approved request becomes a contract, but only for the customer who raised it', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const headers = { Authorization: `Bearer ${hassan}`, Accept: 'application/json' }

    // Approving a request reserves the unit. The contract that closes the
    // request has to be writable from that reserved state.
    const approved = (await (await apiGet(request, hassan, '/owner/purchase-requests?status=approved')).json()).data[0]
    expect(approved).toBeTruthy()

    const customers = (await (await apiGet(request, hassan, '/owner/customers')).json()).data
    const someoneElse = customers.find((c) => c.id !== approved.customer_id)
    expect(someoneElse).toBeTruthy()

    const lease = {
      unit_id: approved.unit_id,
      start_date: '2026-09-01',
      end_date: '2027-08-31',
      monthly_rent: 1000,
      security_deposit: 2000,
      status: 'active',
    }

    // The reservation belongs to one customer — nobody else can be moved in.
    const wrongCustomer = await request.post(`${API_URL}/api/owner/contracts`, {
      headers,
      data: { ...lease, user_id: someoneElse.id },
    })
    expect(wrongCustomer.status()).toBe(422)

    const rightCustomer = await request.post(`${API_URL}/api/owner/contracts`, {
      headers,
      data: { ...lease, user_id: approved.customer_id },
    })
    expect(rightCustomer.status()).toBe(201)

    // And the unit has moved on from reserved to occupied.
    const unit = (await (await apiGet(request, hassan, `/owner/units/${approved.unit_id}`)).json()).data
    expect(unit.status).toBe('occupied')
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
