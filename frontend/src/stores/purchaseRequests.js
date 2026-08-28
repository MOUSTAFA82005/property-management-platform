import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'
import {
  getOwnerPurchaseRequests,
  getOwnerPurchaseRequest,
  approveOwnerPurchaseRequest,
  rejectOwnerPurchaseRequest,
  getCustomerPurchaseRequests,
  getCustomerPurchaseRequest,
  createCustomerPurchaseRequest,
  cancelCustomerPurchaseRequest,
} from '../services/purchaseRequests'
import { extractItem, extractList } from '../services/pagination'

export const usePurchaseRequestsStore = defineStore('purchaseRequests', () => {
  const purchaseRequests = ref([])
  const purchaseRequest  = ref(null)
  const loading          = ref(false)
  const error            = ref(null)

  const meta  = ref(null)
  const links = ref(null)

  function resetError() { error.value = null }

  // Owner
  async function fetchOwnerPurchaseRequests(params = {}) {
    loading.value = true
    resetError()
    try {
      const res = await getOwnerPurchaseRequests(params)
      const extracted = extractList(res)
      purchaseRequests.value = extracted.data
      meta.value  = extracted.meta
      links.value = extracted.links
      return extracted.data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchOwnerPurchaseRequest(id) {
    loading.value = true
    resetError()
    try {
      const res = await getOwnerPurchaseRequest(id)
      purchaseRequest.value = extractItem(res)
      return purchaseRequest.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * The backend exposes approve/reject as explicit transitions and returns the
   * updated request, so the response is what we store — never a guess.
   */
  async function transitionRequest(id, action) {
    loading.value = true
    resetError()
    try {
      const call = action === 'approve' ? approveOwnerPurchaseRequest : rejectOwnerPurchaseRequest
      const res = await call(id)
      const updated = extractItem(res)
      const idx = purchaseRequests.value.findIndex((r) => r.id === id)
      if (idx !== -1) purchaseRequests.value[idx] = updated
      purchaseRequest.value = updated
      return updated
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  const approveRequest = (id) => transitionRequest(id, 'approve')
  const rejectRequest = (id) => transitionRequest(id, 'reject')

  // Customer
  async function fetchCustomerPurchaseRequests(params = {}) {
    loading.value = true
    resetError()
    try {
      const res = await getCustomerPurchaseRequests(params)
      const extracted = extractList(res)
      purchaseRequests.value = extracted.data
      meta.value  = extracted.meta
      links.value = extracted.links
      return extracted.data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCustomerPurchaseRequest(id) {
    loading.value = true
    resetError()
    try {
      const res = await getCustomerPurchaseRequest(id)
      purchaseRequest.value = extractItem(res)
      return purchaseRequest.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function submitPurchaseRequest(data) {
    loading.value = true
    resetError()
    try {
      const res = await createCustomerPurchaseRequest(data)
      const created = extractItem(res)
      purchaseRequests.value.unshift(created)
      return created
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function cancelPurchaseRequest(id) {
    loading.value = true
    resetError()
    try {
      const res = await cancelCustomerPurchaseRequest(id)
      const updated = extractItem(res)
      const idx = purchaseRequests.value.findIndex((r) => r.id === id)
      if (idx !== -1 && updated?.id) purchaseRequests.value[idx] = updated
      if (purchaseRequest.value?.id === id && updated?.id) purchaseRequest.value = updated
      return updated
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // Unified
  async function fetchPurchaseRequests(params = {}) {
    const auth = useAuthStore()
    return auth.isOwner()
      ? fetchOwnerPurchaseRequests(params)
      : fetchCustomerPurchaseRequests(params)
  }

  async function fetchPurchaseRequest(id) {
    const auth = useAuthStore()
    return auth.isOwner()
      ? fetchOwnerPurchaseRequest(id)
      : fetchCustomerPurchaseRequest(id)
  }

  return {
    purchaseRequests,
    purchaseRequest,
    loading,
    error,
    meta,
    links,

    fetchOwnerPurchaseRequests,
    fetchOwnerPurchaseRequest,
    approveRequest,
    rejectRequest,

    fetchCustomerPurchaseRequests,
    fetchCustomerPurchaseRequest,
    submitPurchaseRequest,
    cancelPurchaseRequest,

    fetchPurchaseRequests,
    fetchPurchaseRequest,
  }
})
