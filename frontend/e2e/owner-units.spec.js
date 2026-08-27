import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS } from './helpers/accounts.js'
import { bodyText, loginAsOwner, unique } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner units CRUD', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
  })

  test('lists only this owner units and never offers a sold status', async ({ page }) => {
    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain('A-101')
    expect(text).not.toContain('M-501')
    expect(text).not.toMatch(/\bSold\b/)

    const statuses = await page.locator('select.owner-select').first().locator('option').allInnerTexts()
    expect(statuses.map((s) => s.trim())).toEqual(['All statuses', 'Available', 'Occupied', 'Reserved'])
  })

  test('creates, edits and deletes a unit', async ({ page }) => {
    const number = unique('E2E')

    await page.goto('/owner/units/create')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Building').selectOption({ index: 1 })
    await page.getByLabel('Unit number').fill(number)
    await page.getByLabel('Monthly rent (EGP)').fill('11500')
    await page.getByRole('button', { name: /save unit/i }).click()

    await page.waitForURL(/\/owner\/units\/\d+\/edit$/)

    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toContain(number)

    // Edit the rent and confirm it persists.
    await page.getByRole('row', { name: new RegExp(number) }).getByRole('link', { name: /^edit$/i }).click()
    await page.waitForURL(/\/owner\/units\/\d+\/edit$/)
    await expect(page.getByLabel('Monthly rent (EGP)')).toHaveValue('11500')
    await page.getByLabel('Monthly rent (EGP)').fill('12750')
    await page.getByRole('button', { name: /update unit/i }).click()
    await expect(page.getByText(/unit updated/i)).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    await expect(page.getByLabel('Monthly rent (EGP)')).toHaveValue('12750')

    // Delete — no contract or request references it.
    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')
    await page.getByRole('row', { name: new RegExp(number) }).getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete unit/i }).click()
    await expect(page.getByText(/was deleted/i)).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).not.toContain(number)
  })

  test('rejects a duplicate unit number inside the same building', async ({ page }) => {
    await page.goto('/owner/units/create')
    await page.waitForLoadState('networkidle')

    // Tower A already contains A-101.
    const towerA = (await page.getByLabel('Building').locator('option').allInnerTexts())
      .find((label) => label.includes('Tower A'))
    await page.getByLabel('Building').selectOption({ label: towerA })
    await page.getByLabel('Unit number').fill('A-101')
    await page.getByLabel('Monthly rent (EGP)').fill('9000')
    await page.getByRole('button', { name: /save unit/i }).click()

    await expect(page.getByText(/already used in this building/i).first()).toBeVisible()
  })

  test('refuses to delete a unit that is under contract', async ({ page }) => {
    await page.goto('/owner/units')
    await page.waitForLoadState('networkidle')

    await page.getByRole('row', { name: /A-101/ }).getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete unit/i }).click()

    await expect(page.getByText(/has contracts against it|has purchase requests against it/i).first()).toBeVisible()
  })

  test('the building picker offers only this owner buildings', async ({ page }) => {
    await page.goto('/owner/units/create')
    await page.waitForLoadState('networkidle')

    const options = (await page.getByLabel('Building').locator('option').allInnerTexts()).join(' ')
    expect(options).toContain('Tower A')
    expect(options).not.toContain('Marina Tower')
  })
})
