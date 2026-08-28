import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsOwner, unique } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner buildings CRUD', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
  })

  test('lists the buildings across every property the owner holds', async ({ page }) => {
    await page.goto('/owner/buildings')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain('Tower A')
    expect(text).toContain('Block 1')
    expect(text).toContain('Marina Tower')
  })

  test('the property picker offers this owner properties', async ({ page }) => {
    await page.goto('/owner/buildings/create')
    await page.waitForLoadState('networkidle')

    const options = (await page.getByLabel('Property').locator('option').allInnerTexts()).join(' ')
    expect(options).toContain(PROPERTIES.nileView.name)
    expect(options).toContain(PROPERTIES.marina.name)
  })

  test('creates, edits and deletes a building', async ({ page }) => {
    const name = unique('E2E Block')

    await page.goto('/owner/buildings/create')
    await page.waitForLoadState('networkidle')
    await page.getByLabel('Property').selectOption({ index: 1 })
    await page.getByLabel('Building name').fill(name)
    await page.getByLabel('Floors').fill('5')
    await page.getByRole('button', { name: /save building/i }).click()

    await page.waitForURL('**/owner/buildings')
    await expect(page.getByRole('cell', { name })).toBeVisible()

    // Edit
    const renamed = `${name} Renamed`
    await page.getByRole('row', { name: new RegExp(name) }).getByRole('link', { name: /^edit$/i }).click()
    await page.waitForURL(/\/owner\/buildings\/\d+\/edit$/)
    await expect(page.getByLabel('Building name')).toHaveValue(name)
    await page.getByLabel('Building name').fill(renamed)
    await page.getByRole('button', { name: /update building/i }).click()
    await expect(page.getByText(/building updated/i)).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    await expect(page.getByLabel('Building name')).toHaveValue(renamed)

    // Delete — nothing depends on this building.
    await page.goto('/owner/buildings')
    await page.waitForLoadState('networkidle')
    await page.getByRole('row', { name: new RegExp(renamed) }).getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete building/i }).click()
    await expect(page.getByText(/was deleted/i)).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).not.toContain(renamed)
  })

  test('shows validation errors from the API', async ({ page }) => {
    await page.goto('/owner/buildings/create')
    await page.waitForLoadState('networkidle')
    await page.getByRole('button', { name: /save building/i }).click()

    await expect(page.getByText(/field is required/i).first()).toBeVisible()
  })

  test('refuses to delete a building whose units are under contract', async ({ page }) => {
    await page.goto('/owner/buildings')
    await page.waitForLoadState('networkidle')

    // Tower A holds A-101, which the seeder puts under an active contract.
    // Match on the name cell: "Marina Tower / Alexandria Marina Towers" also
    // contains the substring "Tower A" once a row's cells are concatenated.
    const towerA = page.getByRole('row').filter({
      has: page.getByRole('cell', { name: 'Tower A', exact: true }),
    })
    await towerA.getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete building/i }).click()

    await expect(page.getByText(/still has units under contract|still has purchase requests/i).first()).toBeVisible()
  })
})
