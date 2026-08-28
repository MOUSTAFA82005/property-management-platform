import api from './api'

// Buildings are owner-only; there is no public buildings endpoint.
export function getOwnerBuildings(params = {}) {
  return api.get('/owner/buildings', { params })
}

export function getOwnerBuilding(id) {
  return api.get(`/owner/buildings/${id}`)
}

export function createOwnerBuilding(data) {
  return api.post('/owner/buildings', data)
}

export function updateOwnerBuilding(id, data) {
  return api.put(`/owner/buildings/${id}`, data)
}

export function deleteOwnerBuilding(id) {
  return api.delete(`/owner/buildings/${id}`)
}
