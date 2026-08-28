import api from './api'

// Only customers connected to the authenticated owner's properties.
export function getOwnerCustomers(params = {}) {
  return api.get('/owner/customers', { params })
}

export function getOwnerCustomer(id) {
  return api.get(`/owner/customers/${id}`)
}
