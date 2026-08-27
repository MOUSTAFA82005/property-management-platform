import api from './api'

export function getOwnerPayments(params = {}) {
  return api.get('/owner/payments', { params })
}

export function getOwnerPayment(id) {
  return api.get(`/owner/payments/${id}`)
}

export function createOwnerPayment(data) {
  return api.post('/owner/payments', data)
}

export function updateOwnerPayment(id, data) {
  return api.put(`/owner/payments/${id}`, data)
}

export function deleteOwnerPayment(id) {
  return api.delete(`/owner/payments/${id}`)
}

export function getCustomerPayments() {
  return api.get('/payments')
}

export function getCustomerPayment(id) {
  return api.get(`/payments/${id}`)
}
