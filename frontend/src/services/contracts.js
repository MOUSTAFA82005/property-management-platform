import api from './api'

export const getOwnerContracts = () => {
  return api.get('/owner/contracts')
}

export const createContract = (data) => {
  return api.post('/owner/contracts', data)
}

export const getOwnerContract = (id) => {
  return api.get(`/owner/contracts/${id}`)
}

export const updateContract = (id, data) => {
  return api.put(`/owner/contracts/${id}`, data)
}

export const deleteContract = (id) => {
  return api.delete(`/owner/contracts/${id}`)
}

export const getCustomerContracts = () => {
  return api.get('/contracts')
}

export const getCustomerContract = (id) => {
  return api.get(`/contracts/${id}`)
}