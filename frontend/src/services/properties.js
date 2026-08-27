import api from './api'

// Owner — manage the properties you own.
export function getOwnerProperties(params = {}) {
  return api.get('/owner/properties', { params })
}

export function getOwnerProperty(id) {
  return api.get(`/owner/properties/${id}`)
}

export function createOwnerProperty(data) {
  return api.post('/owner/properties', data)
}

export function updateOwnerProperty(id, data) {
  return api.put(`/owner/properties/${id}`, data)
}

export function deleteOwnerProperty(id) {
  return api.delete(`/owner/properties/${id}`)
}

export function publishOwnerProperty(id) {
  return api.post(`/owner/properties/${id}/publish`)
}

export function unpublishOwnerProperty(id) {
  return api.post(`/owner/properties/${id}/unpublish`)
}

// Public catalog — no authentication required.
export function getPublicProperties(params = {}) {
  return api.get('/properties', { params })
}

export function getPublicProperty(id) {
  return api.get(`/properties/${id}`)
}
