import api from './api'

// Owner endpoints
export function getOwnerPurchaseRequests(params = {}) {
  return api.get('/owner/purchase-requests', { params })
}

export function getOwnerPurchaseRequest(id) {
  return api.get(`/owner/purchase-requests/${id}`)
}

export function updateOwnerPurchaseRequest(id, data) {
  return api.put(`/owner/purchase-requests/${id}`, data)
}

// Customer endpoints
export function getCustomerPurchaseRequests(params = {}) {
  return api.get('/purchase-requests', { params })
}

export function getCustomerPurchaseRequest(id) {
  return api.get(`/purchase-requests/${id}`)
}

export function createCustomerPurchaseRequest(data) {
  return api.post('/purchase-requests', data)
}

export function cancelCustomerPurchaseRequest(id) {
  return api.delete(`/purchase-requests/${id}`)
}
