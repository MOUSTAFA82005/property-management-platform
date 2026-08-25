import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'
import {
  getOwnerContracts,
  getOwnerContract,
  createOwnerContract,
  updateOwnerContract,
  deleteOwnerContract,
  getCustomerContracts,
  getCustomerContract,
} from '../services/contracts'

export const useContractsStore = defineStore('contracts', () => {
  const contracts = ref([])
  const contract  = ref(null)
  const loading   = ref(false)
  const error     = ref(null)

  const meta  = ref(null)
  const links = ref(null)

  function resetError() {
    error.value = null
  }

  function extractData(response) {
    const body = response.data

    if (Array.isArray(body)) {
      return { data: body, meta: null, links: null }
    }

    if (body?.data && Array.isArray(body.data)) {
      return {
        data: body.data,
        meta: body.meta || null,
        links: body.links || null,
      }
    }

    return { data: body ? [body] : [], meta: null, links: null }
  }

  // -- Owner actions ------------------------------------------

  async function fetchOwnerContracts(params = {}) {
    loading.value = true
    resetError()
    try {
      const res = await getOwnerContracts(params)
      const extracted = extractData(res)
      contracts.value = extracted.data
      meta.value = extracted.meta
      links.value = extracted.links
      return extracted.data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchOwnerContract(id) {
    loading.value = true
    resetError()
    try {
      const res = await getOwnerContract(id)
      contract.value = res.data?.data || res.data
      return contract.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // -- Customer actions ----------------------------------------

  async function fetchCustomerContracts(params = {}) {
    loading.value = true
    resetError()
    try {
      const res = await getCustomerContracts(params)
      const extracted = extractData(res)
      contracts.value = extracted.data
      meta.value = extracted.meta
      links.value = extracted.links
      return extracted.data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCustomerContract(id) {
    loading.value = true
    resetError()
    try {
      const res = await getCustomerContract(id)
      contract.value = res.data?.data || res.data
      return contract.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // -- Unified fetch ------------------------------------------

  async function fetchContracts(params = {}) {
    const auth = useAuthStore()
    if (auth.isOwner()) {
      return fetchOwnerContracts(params)
    }
    return fetchCustomerContracts(params)
  }

  async function fetchContract(id) {
    const auth = useAuthStore()
    if (auth.isOwner()) {
      return fetchOwnerContract(id)
    }
    return fetchCustomerContract(id)
  }

  return {
    contracts,
    contract,
    loading,
    error,
    meta,
    links,

    fetchOwnerContracts,
    fetchOwnerContract,

    fetchCustomerContracts,
    fetchCustomerContract,

    fetchContracts,
    fetchContract,
  }
})
