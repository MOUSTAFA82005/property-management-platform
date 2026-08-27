import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { PROPERTIES } from './helpers/accounts.js'
import { bodyText } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Public catalog (no authentication)', () => {
  test('the application loads for an anonymous visitor', async ({ page }) => {
    await page.goto('/')
    await expect(page).toHaveTitle(/PropSpace/i)
    await expect(page.getByRole('link', { name: /^properties$/i }).first()).toBeVisible()
  })

  test('the home page features real seeded properties', async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain(PROPERTIES.nileView.name)
    // Guards against the mock listings that used to be hardcoded here.
    expect(text).not.toContain('Modern Family Villa')
    expect(text).not.toContain('Luxury Sky Apartment')
  })

  test('the catalog lists published properties and hides unpublished ones', async ({ page }) => {
    await page.goto('/properties')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toContain(PROPERTIES.nileView.name)
    expect(text).toContain(PROPERTIES.marina.name)
    expect(text).not.toContain(PROPERTIES.palmGardens.name)
  })

  test('a published property opens and shows its units', async ({ page }) => {
    await page.goto('/properties')
    await page.waitForLoadState('networkidle')
    await page.getByRole('link', { name: /view details/i }).first().click()

    await page.waitForURL(/\/properties\/\d+$/)
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('heading', { name: PROPERTIES.nileView.name })).toBeVisible()
    await expect(page.getByRole('cell', { name: 'A-101' })).toBeVisible()
    await expect(page.getByRole('cell', { name: 'A-102' })).toBeVisible()

    // `sold` is not a status this schema supports.
    expect(await bodyText(page)).not.toMatch(/\bSold\b/)
  })

  test('a public unit page shows real unit detail', async ({ page }) => {
    await page.goto('/properties/1')
    await page.waitForLoadState('networkidle')
    await page.getByRole('link', { name: /^view$/i }).first().click()

    await page.waitForURL(/\/units\/\d+$/)
    await expect(page.getByRole('heading', { name: /Unit A-\d+/ })).toBeVisible()
    await expect(page.getByText(PROPERTIES.nileView.name)).toBeVisible()
  })

  test('an unpublished property is not reachable publicly', async ({ page }) => {
    // Palm Gardens is seeded with is_published = false.
    await page.goto('/properties/2')
    await page.waitForLoadState('networkidle')

    const text = await bodyText(page)
    expect(text).toMatch(/could not load|not be found/i)
    expect(text).not.toContain(PROPERTIES.palmGardens.name)
  })

  test('units inside an unpublished property are not reachable publicly', async ({ page }) => {
    // G-01 belongs to Palm Gardens.
    await page.goto('/units/6')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toMatch(/could not load|not be found/i)
  })

  test('the catalog can be searched and filtered', async ({ page }) => {
    await page.goto('/properties')
    await page.waitForLoadState('networkidle')

    await page.getByPlaceholder(/search by name/i).fill('Alexandria')
    await expect(page.getByText(PROPERTIES.marina.name).first()).toBeVisible()
    await expect(page.getByText(PROPERTIES.nileView.name)).toHaveCount(0)

    await page.getByPlaceholder(/search by name/i).fill('')
    await expect(page.getByText(PROPERTIES.nileView.name).first()).toBeVisible()
  })

  test('an anonymous visitor is invited to sign in rather than shown a request button', async ({ page }) => {
    await page.goto('/properties/1')
    await page.waitForLoadState('networkidle')

    await expect(page.getByRole('link', { name: /sign in to request/i })).toBeVisible()
    await expect(page.getByRole('button', { name: /^request$/i })).toHaveCount(0)
  })

  test('the catalog never exposes owner contact details', async ({ page }) => {
    await page.goto('/properties')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).not.toContain('owner@propspace.com')

    await page.goto('/properties/1')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).not.toContain('owner@propspace.com')
  })
})
