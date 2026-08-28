import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { CUSTOMERS, OWNERS } from './helpers/accounts.js'
import { bodyText, loginAsCustomer, loginAsOwner, storedToken } from './helpers/app.js'

/**
 * The topbar account control.
 *
 * It replaced a decorative user block, so these cover the behaviour a menu
 * has to have rather than just its appearance: it opens, it dismisses the
 * three ways a menu should, and both of its items do the real thing.
 */
test.beforeAll(() => prepareDatabase())

const trigger = (page) => page.getByTestId('account-menu')
const menu = (page) => page.getByRole('menu')

test.describe('Owner account menu', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
  })

  test('the user block is a button that opens a menu', async ({ page }) => {
    await expect(trigger(page)).toHaveAttribute('aria-haspopup', 'menu')
    await expect(trigger(page)).toHaveAttribute('aria-expanded', 'false')
    await expect(menu(page)).toHaveCount(0)

    await trigger(page).click()

    await expect(trigger(page)).toHaveAttribute('aria-expanded', 'true')
    await expect(menu(page)).toBeVisible()
    await expect(page.getByRole('menuitem', { name: /profile/i })).toBeVisible()
    await expect(page.getByRole('menuitem', { name: /logout/i })).toBeVisible()
  })

  test('escape closes it and hands focus back to the trigger', async ({ page }) => {
    await trigger(page).click()
    await expect(menu(page)).toBeVisible()

    await page.keyboard.press('Escape')

    await expect(menu(page)).toHaveCount(0)
    await expect(trigger(page)).toHaveAttribute('aria-expanded', 'false')
    await expect(trigger(page)).toBeFocused()
  })

  test('clicking outside closes it', async ({ page }) => {
    await trigger(page).click()
    await expect(menu(page)).toBeVisible()

    await page.getByRole('heading', { level: 1 }).click()

    await expect(menu(page)).toHaveCount(0)
  })

  test('it is operable from the keyboard alone', async ({ page }) => {
    // Space activates a native button, and opening moves focus into the menu.
    await trigger(page).focus()
    await page.keyboard.press('Space')

    await expect(menu(page)).toBeVisible()
    await expect(page.getByRole('menuitem', { name: /profile/i })).toBeFocused()

    await page.keyboard.press('ArrowDown')
    await expect(page.getByRole('menuitem', { name: /logout/i })).toBeFocused()

    // And wraps back round to the first item.
    await page.keyboard.press('ArrowDown')
    await expect(page.getByRole('menuitem', { name: /profile/i })).toBeFocused()
  })

  test('profile navigates to the existing owner profile and closes the menu', async ({ page }) => {
    await trigger(page).click()
    await page.getByRole('menuitem', { name: /profile/i }).click()

    await page.waitForURL('**/owner/profile')
    await expect(menu(page)).toHaveCount(0)
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  })

  test('it works on a narrow viewport', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 780 })

    // The label collapses away but the control itself stays operable.
    await trigger(page).click()
    await expect(menu(page)).toBeVisible()

    const box = await menu(page).boundingBox()
    expect(box.x).toBeGreaterThanOrEqual(0)
    expect(box.x + box.width).toBeLessThanOrEqual(390)
  })

  test('logout ends the session through the existing auth flow', async ({ page }) => {
    expect(await storedToken(page)).not.toBeNull()

    await trigger(page).click()
    await page.getByRole('menuitem', { name: /logout/i }).click()

    await page.waitForURL('**/login')
    expect(await storedToken(page)).toBeNull()
  })
})

/**
 * The public navbar's account control.
 *
 * A signed-in customer should be recognised on the landing page rather than
 * still being offered Login/Register, and the same menu behaviour applies.
 */
test.describe('Customer account menu on the public site', () => {
  test('a guest is offered login and register, with no account menu', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('link', { name: /^login$/i }).first()).toBeVisible()
    await expect(page.getByRole('link', { name: /^register$/i }).first()).toBeVisible()
    await expect(page.getByTestId('account-menu')).toHaveCount(0)
  })

  test('a signed-in customer is greeted by name instead of being asked to log in', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain('Welcome,')
    expect(text).toContain(CUSTOMERS.omar.name.split(' ')[0])

    await expect(trigger(page)).toBeVisible()
  })

  test('the menu shows the account and reaches the existing profile page', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    await trigger(page).click()
    await expect(menu(page)).toBeVisible()
    await expect(menu(page)).toContainText(CUSTOMERS.omar.name)
    await expect(menu(page)).toContainText(CUSTOMERS.omar.email)

    await page.getByRole('menuitem', { name: /profile/i }).click()
    await page.waitForURL('**/profile')
    await expect(menu(page)).toHaveCount(0)
  })

  test('escape closes it and logout ends the session', async ({ page }) => {
    await loginAsCustomer(page, CUSTOMERS.omar.email)
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    await trigger(page).click()
    await page.keyboard.press('Escape')
    await expect(menu(page)).toHaveCount(0)

    await trigger(page).click()
    await page.getByRole('menuitem', { name: /logout/i }).click()

    await page.waitForURL('**/login')
    expect(await storedToken(page)).toBeNull()
  })
})
