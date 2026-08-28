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

test.describe('Owner contract edit and delete', () => {
  /**
   * A contract of this block's own, with values it chose.
   *
   * The tests above add contracts too, so anything matched by customer name
   * is ambiguous by the time this block runs — and every seeded contract
   * carries payments, which makes it deliberately undeletable. Both problems
   * go away by creating the subject here.
   */
  async function makeContract(request, overrides = {}) {
    const token = await apiLogin(request, OWNERS.hassan.email)
    const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' }

    const units = (await (await apiGet(request, token, '/owner/units?status=available')).json()).data
    const customers = (await (await apiGet(request, token, '/owner/customers')).json()).data
    expect(units.length, 'need a free unit to let').toBeGreaterThan(0)

    const response = await request.post(`${API_URL}/api/owner/contracts`, {
      headers,
      data: {
        user_id: customers[0].id,
        unit_id: units[0].id,
        start_date: '2026-10-01',
        end_date: '2027-09-30',
        monthly_rent: 9000,
        security_deposit: 18000,
        status: 'active',
        ...overrides,
      },
    })

    expect(response.status()).toBe(201)
    return (await response.json()).data
  }

  /** The list row for one contract, matched on its id cell rather than text. */
  function rowFor(page, contract) {
    return page.getByRole('row').filter({
      has: page.getByRole('cell', { name: `#${contract.id}`, exact: true }),
    })
  }

  async function openEditFromList(page, contract) {
    await page.goto('/owner/contracts')
    await page.waitForLoadState('networkidle')

    await rowFor(page, contract).getByRole('link', { name: /^edit$/i }).click()
    await page.waitForURL(new RegExp(`/owner/contracts/${contract.id}/edit$`))
    await page.waitForLoadState('networkidle')
  }

  test('the edit button opens a form pre-filled with the contract data', async ({ page, request }) => {
    const contract = await makeContract(request)

    await loginAsOwner(page, OWNERS.hassan.email)
    await openEditFromList(page, contract)

    // Every field carries the record's current value, not an empty form.
    await expect(page.getByLabel('Customer')).toHaveValue(String(contract.user_id))
    await expect(page.getByLabel('Unit')).toHaveValue(String(contract.unit_id))
    await expect(page.getByLabel('Start date')).toHaveValue('2026-10-01')
    await expect(page.getByLabel('End date')).toHaveValue('2027-09-30')
    await expect(page.getByLabel('Monthly rent (EGP)')).toHaveValue(/^9000/)
    await expect(page.getByLabel('Status')).toHaveValue('active')
  })

  test('an edit is saved and shows up in the list', async ({ page, request }) => {
    const contract = await makeContract(request)

    await loginAsOwner(page, OWNERS.hassan.email)
    await openEditFromList(page, contract)

    await page.getByLabel('Monthly rent (EGP)').fill('17250')
    await page.getByLabel('Status').selectOption('terminated')
    await page.getByRole('button', { name: /update contract/i }).click()

    await expect(page.getByText(/contract updated/i)).toBeVisible()

    // The change survives a round trip through the API.
    await page.goto('/owner/contracts')
    await page.waitForLoadState('networkidle')
    await expect(rowFor(page, contract)).toContainText(/terminated/i)

    const token = await apiLogin(request, OWNERS.hassan.email)
    const saved = (await (await apiGet(request, token, `/owner/contracts/${contract.id}`)).json()).data
    expect(Number(saved.monthly_rent)).toBe(17250)
    expect(saved.status).toBe('terminated')
  })

  test('the edit form surfaces API validation errors', async ({ page, request }) => {
    const contract = await makeContract(request)

    await loginAsOwner(page, OWNERS.hassan.email)
    await openEditFromList(page, contract)

    // An end date before the start date is refused by Laravel, not the browser.
    await page.getByLabel('Start date').fill('2027-01-01')
    await page.getByLabel('End date').fill('2026-01-01')
    await page.getByRole('button', { name: /update contract/i }).click()

    await expect(page.getByText(/must be a date after/i)).toBeVisible()
    await expect(page.getByText(/contract updated/i)).toHaveCount(0)

    // The bad value is refused, not silently accepted.
    const token = await apiLogin(request, OWNERS.hassan.email)
    const unchanged = (await (await apiGet(request, token, `/owner/contracts/${contract.id}`)).json()).data
    expect(unchanged.start_date).toContain('2026-10-01')
  })

  test('deleting asks for confirmation first, and cancelling changes nothing', async ({ page, request }) => {
    const contract = await makeContract(request)

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/contracts')
    await page.waitForLoadState('networkidle')

    await rowFor(page, contract).getByRole('button', { name: /^delete$/i }).click()

    // Nothing is deleted until the dialog is confirmed.
    await expect(page.getByRole('heading', { name: /delete CTR-\d+\?/i })).toBeVisible()
    await page.getByRole('button', { name: /^cancel$/i }).click()

    await page.reload({ waitUntil: 'networkidle' })
    await expect(rowFor(page, contract)).toHaveCount(1)
  })

  test('a confirmed delete removes the contract from the list', async ({ page, request }) => {
    const contract = await makeContract(request)

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/contracts')
    await page.waitForLoadState('networkidle')

    await rowFor(page, contract).getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete contract/i }).click()

    await expect(page.getByText(/was deleted/i)).toBeVisible()
    await expect(rowFor(page, contract)).toHaveCount(0)

    // And it is really gone, not just hidden client-side.
    await page.reload({ waitUntil: 'networkidle' })
    await expect(rowFor(page, contract)).toHaveCount(0)
  })

  test('a contract with payments is refused with the API message', async ({ page, request }) => {
    const token = await apiLogin(request, OWNERS.hassan.email)
    const contracts = (await (await apiGet(request, token, '/owner/contracts')).json()).data
    const withPayments = contracts.find((c) => (c.payments?.length ?? 0) > 0)
    expect(withPayments, 'a seeded contract should have payments').toBeTruthy()

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/contracts')
    await page.waitForLoadState('networkidle')

    await rowFor(page, withPayments).getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete contract/i }).click()

    await expect(page.getByText(/still has payments recorded against it/i)).toBeVisible()

    // The refusal is real: the contract is still there after a reload.
    await page.reload({ waitUntil: 'networkidle' })
    await expect(rowFor(page, withPayments)).toHaveCount(1)
  })

  test('the detail page can edit and delete the contract it is showing', async ({ page, request }) => {
    const contract = await makeContract(request)

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto(`/owner/contracts/${contract.id}`)
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('link', { name: /^edit$/i })).toBeVisible()

    await page.getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete contract/i }).click()

    // A successful delete returns to the list, without the contract.
    await page.waitForURL(/\/owner\/contracts$/)
    await page.waitForLoadState('networkidle')
    await expect(rowFor(page, contract)).toHaveCount(0)
  })

  test('a customer cannot edit or delete a contract through the owner API', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const omar = await apiLogin(request, CUSTOMERS.omar.email)

    const contract = (await (await apiGet(request, hassan, '/owner/contracts')).json()).data[0]
    const headers = { Authorization: `Bearer ${omar}`, Accept: 'application/json' }

    // Hiding the buttons is not the control — the endpoints refuse outright.
    expect((await request.put(`${API_URL}/api/owner/contracts/${contract.id}`, {
      headers, data: { monthly_rent: 1 },
    })).status()).toBe(403)

    expect((await request.delete(`${API_URL}/api/owner/contracts/${contract.id}`, { headers })).status()).toBe(403)

    // A contract that does not exist is a 404, not a silent success.
    expect((await request.delete(`${API_URL}/api/owner/contracts/999999`, {
      headers: { Authorization: `Bearer ${hassan}`, Accept: 'application/json' },
    })).status()).toBe(404)

    // And the record really is untouched.
    const stillThere = (await (await apiGet(request, hassan, `/owner/contracts/${contract.id}`)).json()).data
    expect(Number(stillThere.monthly_rent)).not.toBe(1)
  })
})

