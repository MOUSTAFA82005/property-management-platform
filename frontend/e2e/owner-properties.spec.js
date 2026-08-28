import { test, expect } from '@playwright/test'
import { prepareDatabase } from './helpers/database.js'
import { OWNERS, PROPERTIES } from './helpers/accounts.js'
import { bodyText, loginAsOwner, unique } from './helpers/app.js'

test.beforeAll(() => prepareDatabase())

test.describe('Owner properties CRUD', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsOwner(page, OWNERS.hassan.email)
  })

  test('creates a property, then reads it back from the API', async ({ page }) => {
    const name = unique('E2E Tower')

    await page.goto('/owner/properties/create')
    await page.getByLabel('Property name').fill(name)
    await page.getByLabel('City').fill('Giza')
    await page.getByLabel('Address').fill('12 Test Street')
    await page.getByRole('button', { name: /save property/i }).click()

    // Redirects to the detail page for the created record.
    await page.waitForURL(/\/owner\/properties\/\d+$/)
    await expect(page.getByRole('heading', { name })).toBeVisible()

    // And it is really persisted, not just held in the store.
    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')
    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).toContain(name)
  })

  test('surfaces validation errors and keeps what was typed', async ({ page }) => {
    await page.goto('/owner/properties/create')
    await page.getByLabel('City').fill('Giza')
    await page.getByRole('button', { name: /save property/i }).click()
    await page.waitForLoadState('networkidle')

    // A locator assertion rather than a one-shot bodyText() read: the errors
    // arrive with the 422 response, and a plain read can win the race against
    // the re-render on a loaded machine.
    await expect(page.getByText(/required/i).first()).toBeVisible()
    await expect(page.getByLabel('City')).toHaveValue('Giza')
    expect(page.url()).toContain('/owner/properties/create')
  })

  test('edits a property and the change persists', async ({ page }) => {
    const original = unique('E2E Editable')
    const renamed = unique('Renamed')

    // Rename a property this test owns. Picking whichever row happens to be
    // first would rename a seeded property that later tests still look for.
    await page.goto('/owner/properties/create')
    await page.getByLabel('Property name').fill(original)
    await page.getByLabel('City').fill('Giza')
    await page.getByLabel('Address').fill('3 Editable Street')
    await page.getByRole('button', { name: /save property/i }).click()
    await page.waitForURL(/\/owner\/properties\/\d+$/)

    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')
    await page.getByRole('row', { name: new RegExp(original) })
      .getByRole('link', { name: /^edit$/i }).click()
    await page.waitForURL(/\/owner\/properties\/\d+\/edit$/)
    await page.waitForLoadState('networkidle')

    await page.getByLabel('Property name').fill(renamed)
    await page.getByRole('button', { name: /update property/i }).click()
    await expect(page.getByText(/property updated/i)).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    await expect(page.getByLabel('Property name')).toHaveValue(renamed)
  })

  test('publishes and unpublishes, and the public catalog follows', async ({ page }) => {
    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')

    // Palm Gardens is seeded unpublished.
    const row = page.getByRole('row', { name: new RegExp(PROPERTIES.palmGardens.name) })
    await row.getByRole('button', { name: /^publish$/i }).click()
    await expect(page.getByText(/now published/i)).toBeVisible()

    await page.goto('/properties')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).toContain(PROPERTIES.palmGardens.name)

    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')
    await page.getByRole('row', { name: new RegExp(PROPERTIES.palmGardens.name) })
      .getByRole('button', { name: /^unpublish$/i }).click()
    await expect(page.getByText(/now unpublished/i)).toBeVisible()

    await page.goto('/properties')
    await page.waitForLoadState('networkidle')
    expect(await bodyText(page)).not.toContain(PROPERTIES.palmGardens.name)
  })

  test('deletes a property that has nothing depending on it', async ({ page }) => {
    const name = unique('Disposable')

    await page.goto('/owner/properties/create')
    await page.getByLabel('Property name').fill(name)
    await page.getByLabel('City').fill('Cairo')
    await page.getByLabel('Address').fill('1 Nowhere Road')
    await page.getByRole('button', { name: /save property/i }).click()
    await page.waitForURL(/\/owner\/properties\/\d+$/)

    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')
    await page.getByRole('row', { name: new RegExp(name) }).getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete property/i }).click()

    await expect(page.getByText(/was deleted/i)).toBeVisible()
    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).not.toContain(name)
  })

  test('refuses to delete a property whose units are under contract', async ({ page }) => {
    await page.goto('/owner/properties')
    await page.waitForLoadState('networkidle')

    await page.getByRole('row', { name: new RegExp(PROPERTIES.nileView.name) })
      .getByRole('button', { name: /^delete$/i }).click()
    await page.getByRole('button', { name: /delete property/i }).click()

    // The API answers 409 rather than cascading into contracts and payments.
    await expect(page.getByText(/still has units under contract|still has purchase requests/i).first()).toBeVisible()

    await page.reload({ waitUntil: 'networkidle' })
    expect(await bodyText(page)).toContain(PROPERTIES.nileView.name)
  })
})
