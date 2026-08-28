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
import { extractItem, extractList } from '../services/pagination'

export const useContractsStore = defineStore('contracts', () => {
  const contracts = ref([])
  const contract = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const meta = ref(null)
  const links = ref(null)

  function resetError() {
    error.value = null
  }

  // Owner actions

  async function fetchOwnerContracts(params = {}) {
    loading.value = true
    resetError()

    try {
      const res = await getOwnerContracts(params)
      const extracted = extractList(res)

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

      contract.value = extractItem(res)

      return contract.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function addContract(data) {
    loading.value = true
    resetError()

    try {
      const res = await createOwnerContract(data)

      const newContract = extractItem(res)

      contracts.value.unshift(newContract)

      return newContract
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function editContract(id, data) {
    loading.value = true
    resetError()

    try {
      const res = await updateOwnerContract(id, data)

      const updatedContract = extractItem(res)

      const index = contracts.value.findIndex(
        (item) => item.id === id
      )

      if (index !== -1) {
        contracts.value[index] = updatedContract
      }

      contract.value = updatedContract

      // Every other action returns the resource, not the envelope.
      return updatedContract
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function removeContract(id) {
    loading.value = true
    resetError()

    try {
      await deleteOwnerContract(id)

      contracts.value = contracts.value.filter(
        (item) => item.id !== id
      )

      if (contract.value?.id === id) {
        contract.value = null
      }
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // Customer actions

  async function fetchCustomerContracts(params = {}) {
    loading.value = true
    resetError()

    try {
      const res = await getCustomerContracts(params)
      const extracted = extractList(res)

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

      contract.value = extractItem(res)

      return contract.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // Unified actions

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
    addContract,
    editContract,
    removeContract,

    fetchCustomerContracts,
    fetchCustomerContract,

    fetchContracts,
    fetchContract,
  }
})