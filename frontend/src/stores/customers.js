import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as customerService from '../services/customers'
import { extractItem, extractList, normalizeError } from '../services/pagination'

export const useCustomersStore = defineStore('customers', () => {
  const customers = ref([])
  const customer = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const meta = ref(null)

  async function fetchCustomers(params = {}) {
    loading.value = true
    error.value = null
    try {
      const extracted = extractList(await customerService.getOwnerCustomers(params))
      customers.value = extracted.data
      meta.value = extracted.meta
      return extracted.data
    } catch (e) {
      error.value = normalizeError(e).message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCustomer(id) {
    loading.value = true
    error.value = null
    customer.value = null
    try {
      customer.value = extractItem(await customerService.getOwnerCustomer(id))
      return customer.value
    } catch (e) {
      error.value = normalizeError(e).message
      throw e
    } finally {
      loading.value = false
    }
  }

  return { customers, customer, loading, error, meta, fetchCustomers, fetchCustomer }
})
