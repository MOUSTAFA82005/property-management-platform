import api from './api'

// Owner — manage units in your own buildings.
export function getOwnerUnits(params = {}) {
  return api.get('/owner/units', { params })
}

export function getOwnerUnit(id) {
  return api.get(`/owner/units/${id}`)
}

export function createOwnerUnit(data) {
  return api.post('/owner/units', data)
}

export function updateOwnerUnit(id, data) {
  return api.put(`/owner/units/${id}`, data)
}

export function deleteOwnerUnit(id) {
  return api.delete(`/owner/units/${id}`)
}

// Public — browse units in a published property.
export function getPublicPropertyUnits(propertyId, params = {}) {
  return api.get(`/properties/${propertyId}/units`, { params })
}

export function getPublicUnit(id) {
  return api.get(`/units/${id}`)
}
