import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { CUSTOMERS, OWNERS } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner } from './helpers/app.js'
import { apiGet, apiLogin } from './helpers/api.js'
import { API_URL } from '../playwright.config.js'

/**
 * The notification system, driven by real application events.
 *
 * Nothing here seeds a notification directly: each test performs the action
 * that raises one — a customer requesting a unit, an owner approving it — and
 * then checks the other party is told.
 */
test.beforeAll(() => prepareDatabase())

const bell = (page) => page.getByTestId('notification-bell')
const badge = (page) => page.getByTestId('notification-badge')
const panel = (page) => page.getByRole('dialog', { name: /notifications/i })

/** An available unit in the owner's published stock. */
async function availableUnit(request) {
  const token = await apiLogin(request, OWNERS.hassan.email)
  const units = (await (await apiGet(request, token, '/owner/units?status=available')).json()).data
  expect(units.length).toBeGreaterThan(0)
  return units[0]
}

test.describe('Notifications', () => {
  test('a guest sees no bell at all', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    await expect(bell(page)).toHaveCount(0)
  })

  test('a customer request notifies the owner, not the customer', async ({ page, request }) => {
    const unit = await availableUnit(request)

    // The customer raises a real purchase request through the UI.
    await loginAsCustomer(page, CUSTOMERS.nour.email)
    await page.goto(`/units/${unit.id}`)
    await page.waitForLoadState('networkidle')
    await page.getByRole('button', { name: /request this unit/i }).click()
    await expect(page.getByText(/has been submitted/i)).toBeVisible()

    // The owner is told.
    const owner = await apiLogin(request, OWNERS.hassan.email)
    const count = (await (await apiGet(request, owner, '/notifications/unread-count')).json()).count
    expect(count).toBeGreaterThan(0)

    const list = (await (await apiGet(request, owner, '/notifications')).json()).data
    expect(list[0].type).toBe('purchase_request.submitted')
    expect(list[0].message).toContain(CUSTOMERS.nour.name)
    expect(list[0].url).toContain('/owner/purchase-requests/')

    // The customer who acted is not notified about their own action.
    const customer = await apiLogin(request, CUSTOMERS.nour.email)
    const own = (await (await apiGet(request, customer, '/notifications/unread-count')).json()).count
    expect(own).toBe(0)
  })

  test('the owner bell shows a badge and the dropdown lists the notification', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.waitForLoadState('networkidle')

    await expect(bell(page)).toBeVisible()
    await expect(badge(page)).toBeVisible()

    await bell(page).click()
    await expect(panel(page)).toBeVisible()
    await expect(panel(page)).toContainText(/new purchase request/i)
  })

  test('opening a notification marks it read and follows its link', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.waitForLoadState('networkidle')

    await bell(page).click()
    await panel(page).getByRole('button').filter({ hasText: /new purchase request/i }).first().click()

    // It navigates to the record the notification is about.
    await page.waitForURL(/\/owner\/purchase-requests\/\d+$/)

    // And the badge is gone once nothing is unread.
    await page.reload({ waitUntil: 'networkidle' })
    await expect(badge(page)).toHaveCount(0)
  })

  test('escape closes the panel and mark-all clears the badge', async ({ page, request }) => {
    // Raise a fresh notification through the real endpoint. Youssef holds no
    // seeded purchase request, so he cannot collide with one.
    const unit = await availableUnit(request)
    const customer = await apiLogin(request, CUSTOMERS.youssef.email)

    const created = await request.post(`${API_URL}/api/purchase-requests`, {
      headers: { Authorization: `Bearer ${customer}`, Accept: 'application/json' },
      data: { unit_id: unit.id },
    })
    expect(created.status()).toBe(201)

    await loginAsOwner(page, OWNERS.hassan.email)
    await page.waitForLoadState('networkidle')
    await expect(badge(page)).toBeVisible()

    await bell(page).click()
    await expect(panel(page)).toBeVisible()
    await page.keyboard.press('Escape')
    await expect(panel(page)).toHaveCount(0)

    await bell(page).click()
    await page.getByTestId('mark-all-read').click()
    await expect(badge(page)).toHaveCount(0)
  })

  test('the notifications page lists history and is scoped to the user', async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
    await page.goto('/owner/notifications')
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('heading', { name: /^notifications$/i })).toBeVisible()

    const text = await bodyText(page)
    expect(text).toContain('New purchase request')
    // Another user's activity never appears here.
    expect(text).not.toContain('Your request for')
  })

  test('an owner decision notifies the customer who raised the request', async ({ page, request }) => {
    const owner = await apiLogin(request, OWNERS.hassan.email)
    const pending = (await (await apiGet(request, owner, '/owner/purchase-requests?status=pending')).json()).data[0]
    expect(pending).toBeTruthy()

    const response = await request.post(
      `${API_URL}/api/owner/purchase-requests/${pending.id}/reject`,
      { headers: { Authorization: `Bearer ${owner}`, Accept: 'application/json' } },
    )
    expect(response.status()).toBe(200)

    // The customer sees it on their own bell, on the public site.
    await loginAsCustomer(page, pending.customer.email)
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    await expect(bell(page)).toBeVisible()
    await bell(page).click()
    await expect(panel(page)).toContainText(/request declined/i)
  })
})
