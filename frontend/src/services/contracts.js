import api from './api'

export function getOwnerContracts(params = {}) {
  return api.get('/owner/contracts', { params })
}

export function getOwnerContract(id) {
  return api.get(`/owner/contracts/${id}`)
}

export function createOwnerContract(data) {
  return api.post('/owner/contracts', data)
}

export function updateOwnerContract(id, data) {
  return api.put(`/owner/contracts/${id}`, data)
}

export function deleteOwnerContract(id) {
  return api.delete(`/owner/contracts/${id}`)
}

export function getCustomerContracts() {
  return api.get('/contracts')
}

export function getCustomerContract(id) {
  return api.get(`/contracts/${id}`)
}