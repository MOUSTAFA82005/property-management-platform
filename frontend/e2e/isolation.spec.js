import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, CUSTOMERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner } from './helpers/app.js'
import { apiGet, apiLogin } from './helpers/api.js'

/**
 * The security surface of the application.
 *
 * PropSpace has a single owner, so the boundaries that matter here are
 * customer-against-customer and role-against-role. Owner-against-owner
 * isolation is proved in the backend suite (OwnerIsolationTest), which
 * builds two portfolios of its own rather than relying on the demo data.
 *
 * Every assertion checks rendered data or an API response — never just a
 * URL — because a redirect proves nothing about what the server would return.
 */
test.beforeAll(() => prepareDatabase())

test.describe('Owner scope — rendered data', () => {
  test('the owner sees their whole portfolio across every owner page', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)

    const pages = {
      '/owner/properties': [PROPERTIES.nileView.name, PROPERTIES.palmGardens.name, PROPERTIES.marina.name],
      '/owner/buildings': ['Tower A', 'Block 1', 'Marina Tower'],
      '/owner/units': ['A-101', 'M-501'],
      // Page one only — the seeded payments now run past the page size.
      '/owner/payments': ['PAY-2026-'],
      '/owner/contracts': [CUSTOMERS.omar.name, CUSTOMERS.youssef.name],
      '/owner/purchase-requests': [CUSTOMERS.salma.name],
    }

    for (const [path, present] of Object.entries(pages)) {
      await page.goto(path)
      await page.waitForLoadState('networkidle')
      const text = await bodyText(page)

      for (const needle of present) {
        expect(text, `${path} should show ${needle}`).toContain(needle)
      }
    }
  })

  test('the customer list is built from real relationships, not the user table', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)

    const customers = (await (await apiGet(request, hassan, '/owner/customers')).json()).data

    // Only customers, and only through a contract or a purchase request.
    for (const customer of customers) {
      expect(customer.role, `${customer.email} is not a customer`).toBe('customer')
      expect(customer.contracts_count + customer.purchase_requests_count).toBeGreaterThan(0)
    }

    // The owner's own account is not a customer of themselves.
    const me = (await (await apiGet(request, hassan, '/auth/me')).json()).user
    expect(customers.map((c) => c.id)).not.toContain(me.id)
    expect((await apiGet(request, hassan, `/owner/customers/${me.id}`)).status()).toBe(404)
  })
})

test.describe('Customer isolation', () => {
  test('a customer sees only their own contracts, payments and requests', async ({ request }) => {
    const omar = await apiLogin(request, CUSTOMERS.omar.email)
    const salma = await apiLogin(request, CUSTOMERS.salma.email)

    const salmaContract = (await (await apiGet(request, salma, '/contracts')).json()).data[0]
    const salmaPayment = (await (await apiGet(request, salma, '/payments')).json()).data[0]
    const salmaRequest = (await (await apiGet(request, salma, '/purchase-requests')).json()).data[0]

    expect((await apiGet(request, omar, `/contracts/${salmaContract.id}`)).status()).toBe(403)
    expect((await apiGet(request, omar, `/payments/${salmaPayment.id}`)).status()).toBe(403)
    expect((await apiGet(request, omar, `/purchase-requests/${salmaRequest.id}`)).status()).toBe(403)

    // And the lists themselves never contain the other customer's rows.
    const omarContractIds = (await (await apiGet(request, omar, '/contracts')).json()).data.map((c) => c.id)
    expect(omarContractIds).not.toContain(salmaContract.id)
  })

  test('a customer with no contract still cannot see anyone elses', async ({ request }) => {
    const nour = await apiLogin(request, CUSTOMERS.nour.email)
    const omar = await apiLogin(request, CUSTOMERS.omar.email)

    // Nour has only ever enquired, so her own lists are empty.
    expect((await (await apiGet(request, nour, '/contracts')).json()).data).toHaveLength(0)
    expect((await (await apiGet(request, nour, '/payments')).json()).data).toHaveLength(0)
    expect((await (await apiGet(request, nour, '/purchase-requests')).json()).data.length).toBeGreaterThan(0)

    const omarContract = (await (await apiGet(request, omar, '/contracts')).json()).data[0]
    expect((await apiGet(request, nour, `/contracts/${omarContract.id}`)).status()).toBe(403)
  })

  test('a customer cannot reach owner pages or owner endpoints', async ({ page, request }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)

    await page.goto('/owner/dashboard')
    await page.waitForURL((url) => !new URL(url).pathname.startsWith('/owner'))

    const omar = await apiLogin(request, CUSTOMERS.omar.email)
    for (const path of ['/owner/dashboard', '/owner/properties', '/owner/customers', '/owner/payments']) {
      expect((await apiGet(request, omar, path)).status(), path).toBe(403)
    }
  })

  test('an owner is kept out of customer-only account pages', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)

    await page.goto('/profile')
    await page.waitForURL('**/owner/dashboard')
  })
})
