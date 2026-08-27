import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, CUSTOMERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner } from './helpers/app.js'
import { apiGet, apiLogin } from './helpers/api.js'

/**
 * The security surface of the application.
 *
 * Every assertion here checks rendered data or an API response — never just a
 * URL — because a redirect proves nothing about what the server would return.
 */
test.beforeAll(() => prepareDatabase())

test.describe('Owner isolation — rendered data', () => {
  test('Hassan sees only his own portfolio across every owner page', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)

    const pages = {
      '/owner/properties': { present: [PROPERTIES.nileView.name, PROPERTIES.palmGardens.name], absent: [PROPERTIES.marina.name] },
      '/owner/buildings': { present: ['Tower A', 'Block 1'], absent: ['Marina Tower'] },
      '/owner/units': { present: ['A-101'], absent: ['M-501', 'M-901'] },
      '/owner/payments': { present: ['PAY-2026-0001'], absent: ['PAY-2026-0019', 'PAY-2026-0020'] },
      '/owner/contracts': { present: [CUSTOMERS.omar.name], absent: [CUSTOMERS.youssef.name] },
      '/owner/purchase-requests': { present: [CUSTOMERS.salma.name], absent: [] },
    }

    for (const [path, { present, absent }] of Object.entries(pages)) {
      await page.goto(path)
      await page.waitForLoadState('networkidle')
      const text = await bodyText(page)

      for (const needle of present) {
        expect(text, `${path} should show ${needle}`).toContain(needle)
      }
      for (const needle of absent) {
        expect(text, `${path} LEAKED ${needle}`).not.toContain(needle)
      }
    }
  })

  test('Nadia sees only her own portfolio', async ({ page }) => {
    await loginAsOwner(page, OWNERS.nadia.email)

    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')
    let text = await bodyText(page)
    expect(text).toContain(PROPERTIES.marina.name)
    expect(text).not.toContain(PROPERTIES.nileView.name)
    expect(text).not.toContain(PROPERTIES.palmGardens.name)

    await page.goto('/owner/payments')
    await page.waitForLoadState('networkidle')
    text = await bodyText(page)
    expect(text).toContain('PAY-2026-0019')
    expect(text).not.toContain('PAY-2026-0001')
  })

  test('the owner customer list contains only related customers', async ({ page }) => {
    // Seeded relationships: Youssef deals only with Nadia; Salma and Dina only
    // with Hassan; Omar and Karim deal with both.
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/customers')
    await page.waitForLoadState('networkidle')

    let text = await bodyText(page)
    expect(text).toContain(CUSTOMERS.omar.name)
    expect(text).toContain(CUSTOMERS.salma.name)
    expect(text).toContain(CUSTOMERS.dina.name)
    expect(text).toContain(CUSTOMERS.karim.name)
    expect(text, 'Hassan must not see Nadia-only customer Youssef').not.toContain(CUSTOMERS.youssef.name)

    await loginAsOwner(page, OWNERS.nadia.email)
    await page.goto('/owner/customers')
    await page.waitForLoadState('networkidle')

    text = await bodyText(page)
    expect(text).toContain(CUSTOMERS.youssef.name)
    expect(text).toContain(CUSTOMERS.omar.name)
    expect(text, 'Nadia must not see Hassan-only customer Salma').not.toContain(CUSTOMERS.salma.name)
    expect(text, 'Nadia must not see Hassan-only customer Dina').not.toContain(CUSTOMERS.dina.name)
  })

  test('the owner dashboard aggregates only the signed-in owner', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.waitForLoadState('networkidle')
    let text = await bodyText(page)
    expect(text).toContain(PROPERTIES.nileView.name)
    expect(text).not.toContain(PROPERTIES.marina.name)

    await loginAsOwner(page, OWNERS.nadia.email)
    await page.waitForLoadState('networkidle')
    text = await bodyText(page)
    expect(text).toContain(PROPERTIES.marina.name)
    expect(text).not.toContain(PROPERTIES.nileView.name)
  })
})

test.describe('Owner isolation — direct API access', () => {
  test('one owner cannot read, edit or delete another owner records by id', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const nadia = await apiLogin(request, OWNERS.nadia.email)

    // Discover Nadia's ids from her own (authorised) responses.
    const nadiaProperty = (await (await apiGet(request, nadia, '/owner/properties')).json()).data[0]
    const nadiaPayment = (await (await apiGet(request, nadia, '/owner/payments')).json()).data[0]
    const nadiaContract = (await (await apiGet(request, nadia, '/owner/contracts')).json()).data[0]
    const nadiaUnit = (await (await apiGet(request, nadia, '/owner/units')).json()).data[0]
    const nadiaBuilding = (await (await apiGet(request, nadia, '/owner/buildings')).json()).data[0]

    const forbidden = [
      `/owner/properties/${nadiaProperty.id}`,
      `/owner/payments/${nadiaPayment.id}`,
      `/owner/contracts/${nadiaContract.id}`,
      `/owner/units/${nadiaUnit.id}`,
      `/owner/buildings/${nadiaBuilding.id}`,
    ]

    for (const path of forbidden) {
      const response = await apiGet(request, hassan, path)
      expect(response.status(), `GET ${path} as the wrong owner`).toBe(403)
    }

    const { API_URL } = await import('../playwright.config.js')
    const headers = { Authorization: `Bearer ${hassan}`, Accept: 'application/json' }

    expect((await request.put(`${API_URL}/api/owner/properties/${nadiaProperty.id}`, {
      headers, data: { name: 'Hijacked' },
    })).status()).toBe(403)

    expect((await request.delete(`${API_URL}/api/owner/payments/${nadiaPayment.id}`, { headers })).status()).toBe(403)

    // And the record really is untouched.
    const stillThere = await apiGet(request, nadia, `/owner/properties/${nadiaProperty.id}`)
    expect(stillThere.status()).toBe(200)
    expect((await stillThere.json()).data.name).not.toBe('Hijacked')
  })

  test('an unrelated customer cannot be read through the owner customer endpoint', async ({ request }) => {
    const hassan = await apiLogin(request, OWNERS.hassan.email)
    const nadia = await apiLogin(request, OWNERS.nadia.email)

    const nadiasCustomers = (await (await apiGet(request, nadia, '/owner/customers')).json()).data
    const youssef = nadiasCustomers.find((c) => c.email === CUSTOMERS.youssef.email)
    expect(youssef, 'Youssef should be one of Nadia customers').toBeTruthy()

    const response = await apiGet(request, hassan, `/owner/customers/${youssef.id}`)
    expect(response.status()).toBe(404)
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
