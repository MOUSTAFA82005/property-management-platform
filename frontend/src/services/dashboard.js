import api from './api'

/** Scoped aggregates for the authenticated owner. */
export function getOwnerDashboard() {
  return api.get('/owner/dashboard')
}
