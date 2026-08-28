import api from './api'

/**
 * Authentication API calls.
 * POST /api/auth/register → { message, user, token }
 * POST /api/auth/login    → { message, user, token }
 * POST /api/auth/logout   → { message }
 * GET  /api/auth/me       → { user }
 */

export function register(payload) {
  return api.post('/auth/register', payload)
}

export function login(credentials) {
  return api.post('/auth/login', credentials)
}

export function logout() {
  return api.post('/auth/logout')
}

export function me() {
  return api.get('/auth/me')
}
