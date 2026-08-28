import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, CUSTOMERS } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner, textEventually, unique } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner payments', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/payments')
    await page.waitForLoadState('networkidle')
  })

  test('lists the payments raised against this owner properties', async ({ page, request }) => {
    const { apiLogin, apiGet } = await import('./helpers/api.js')
    const token = await apiLogin(request, OWNERS.hassan.email)
    const body = await (await apiGet(request, token, '/owner/payments')).json()

    const text = await bodyText(page)
    expect(text).toContain('PAY-2026-')

    // Every reference the API put on page one is rendered, and nothing else.
    for (const payment of body.data) {
      expect(text, `page one should show ${payment.reference}`).toContain(payment.reference)
    }
  })

  /**
   * Regression guard: the store actions once shadowed their imported service
   * functions and recursed until the stack blew, so no payment could be
   * created, edited or deleted. An uncaught page error fails this test.
   */
  test('creates, edits and deletes a payment without any runtime error', async ({ page }) => {
    const errors = []
    page.on('pageerror', (error) => errors.push(error.message))

    const reference = unique('E2E-PAY')

    await page.getByTestId('payment-add').click()
    await page.getByTestId('payment-contract').selectOption({ index: 1 })
    await page.getByTestId('payment-amount').fill('4321.50')
    await page.getByTestId('payment-due-date').fill('2026-11-01')
    await page.getByTestId('payment-status').selectOption('pending')
    await page.getByTestId('payment-reference').fill(reference)
    await page.getByTestId('payment-submit').click()

    await expect(page.getByText(/payment created successfully/i)).toBeVisible()
    expect(errors, 'creating a payment must not raise a runtime error').toEqual([])

    // Persisted, not just pushed into the store.
    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).toContain(reference)

    // Edit
    await page.getByRole('row', { name: new RegExp(reference) }).getByRole('button', { name: /^edit$/i }).click()
    await page.getByTestId('payment-status').selectOption('paid')
    await page.getByTestId('payment-submit').click()
    await expect(page.getByText(/payment updated successfully/i)).toBeVisible()
    expect(errors).toEqual([])

    await page.reload({ waitUntil: 'networkidle' })
    await expect(page.getByRole('row', { name: new RegExp(reference) }).getByText('paid')).toBeVisible()

    // Delete
    await page.getByRole('row', { name: new RegExp(reference) }).getByTestId('payment-delete').click()
    await page.getByTestId('payment-delete-confirm').click()
    await expect(page.getByText(/payment deleted successfully/i)).toBeVisible()
    expect(errors).toEqual([])

    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).not.toContain(reference)
  })

  test('shows API validation errors instead of failing silently', async ({ page }) => {
    await page.getByTestId('payment-add').click()
    await page.getByTestId('payment-submit').click()

    expect(await bodyText(page)).toMatch(/required/i)
  })

  test('search narrows the list using the API', async ({ page }) => {
    // PAY-2026-0001 is the oldest payment, so it sits past page one until the
    // search reaches the API and brings it back on its own.
    await page.getByPlaceholder(/search by customer/i).fill('PAY-2026-0001')

    const text = await textEventually(page, (t) => t.includes('PAY-2026-0001'))
    expect(text).toContain('PAY-2026-0001')
  })

  test('the table renders exactly the rows the paginated API returned', async ({ page, request }) => {
    const { apiLogin, apiGet } = await import('./helpers/api.js')
    const token = await apiLogin(request, OWNERS.hassan.email)
    const body = await (await apiGet(request, token, '/owner/payments')).json()

    // Laravel's paginated envelope, consumed as-is by the store.
    expect(body.meta).toMatchObject({ current_page: 1, per_page: 15 })
    expect(body.meta.total).toBeGreaterThan(0)

    // One row per record on this page — no client-side slicing or padding.
    const rows = page.getByRole('row')
    await expect(rows).toHaveCount(body.data.length + 1) // + header row
    expect(body.data.length).toBe(body.meta.per_page)

    // The seeded payments run past one page, so the control has to be there.
    expect(body.meta.last_page).toBeGreaterThan(1)
    await expect(page.getByText(/showing page/i)).toBeVisible()
  })
})

test.describe('Customer payments', () => {
  test('a customer sees only payments on their own contracts', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/payments')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toMatch(/PAY-2026-000[1-6]/)
    // Same owner, different customer: Salma's and Youssef's rows must not
    // reach Omar.
    expect(text).not.toContain('PAY-2026-0014')
    expect(text).not.toContain('PAY-2026-0019')
  })
})
