import api from './api'

// Owner endpoints
export function getOwnerPurchaseRequests(params = {}) {
  return api.get('/owner/purchase-requests', { params })
}

export function getOwnerPurchaseRequest(id) {
  return api.get(`/owner/purchase-requests/${id}`)
}

// The backend exposes explicit transitions rather than a generic update.
export function approveOwnerPurchaseRequest(id) {
  return api.post(`/owner/purchase-requests/${id}/approve`)
}

export function rejectOwnerPurchaseRequest(id) {
  return api.post(`/owner/purchase-requests/${id}/reject`)
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
