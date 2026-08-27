import { API_URL } from '../../playwright.config.js'

/**
 * Thin direct-API helper.
 *
 * Used only where a check is about the API contract itself (for example that
 * one owner's record 403s for another owner). UI behaviour is always asserted
 * through the browser.
 */
export async function apiLogin(request, email, password = 'password') {
  const response = await request.post(`${API_URL}/api/auth/login`, {
    data: { email, password },
    headers: { Accept: 'application/json' },
  })

  if (!response.ok()) {
    throw new Error(`Login failed for ${email}: ${response.status()}`)
  }

  return (await response.json()).token
}

export function authHeaders(token) {
  return { Authorization: `Bearer ${token}`, Accept: 'application/json' }
}

export async function apiGet(request, token, path) {
  return request.get(`${API_URL}/api${path}`, { headers: authHeaders(token) })
}
