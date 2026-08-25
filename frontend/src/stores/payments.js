import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'
import {
  getOwnerPayments,
  getOwnerPayment,
  createOwnerPayment,
  updateOwnerPayment,
  deleteOwnerPayment,
  getCustomerPayments,
  getCustomerPayment,
} from '../services/payments'

export const usePaymentsStore = defineStore('payments', () => {
  const payments = ref([])
  const payment  = ref(null)
  const loading  = ref(false)
  const error    = ref(null)

  const meta = ref(null)
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

  // ── Owner actions ──────────────────────────────────

  async function fetchOwnerPayments(params = {}) {
    loading.value = true
    resetError()
    try {
      const res = await getOwnerPayments(params)
      const extracted = extractData(res)
      payments.value = extracted.data
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

  async function fetchOwnerPayment(id) {
    loading.value = true
    resetError()
    try {
      const res = await getOwnerPayment(id)
      payment.value = res.data?.data || res.data
      return payment.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createOwnerPayment(data) {
    loading.value = true
    resetError()
    try {
      const res = await createOwnerPayment(data)
      const created = res.data?.data || res.data
      payments.value.unshift(created)
      return created
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateOwnerPayment(id, data) {
    loading.value = true
    resetError()
    try {
      const res = await updateOwnerPayment(id, data)
      const updated = res.data?.data || res.data
      const idx = payments.value.findIndex((p) => p.id === id)
      if (idx !== -1) payments.value[idx] = updated
      payment.value = updated
      return updated
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteOwnerPayment(id) {
    loading.value = true
    resetError()
    try {
      await deleteOwnerPayment(id)
      payments.value = payments.value.filter((p) => p.id !== id)
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // ── Customer actions ───────────────────────────────

  async function fetchCustomerPayments() {
    loading.value = true
    resetError()
    try {
      const res = await getCustomerPayments()
      const extracted = extractData(res)
      payments.value = extracted.data
      return extracted.data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCustomerPayment(id) {
    loading.value = true
    resetError()
    try {
      const res = await getCustomerPayment(id)
      payment.value = res.data?.data || res.data
      return payment.value
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  // ── Unified fetch (auto-selects owner vs customer) ─

  async function fetchPayments(params = {}) {
    const auth = useAuthStore()
    if (auth.isOwner()) {
      return fetchOwnerPayments(params)
    }
    return fetchCustomerPayments()
  }

  async function fetchPayment(id) {
    const auth = useAuthStore()
    if (auth.isOwner()) {
      return fetchOwnerPayment(id)
    }
    return fetchCustomerPayment(id)
  }

  return {
    payments,
    payment,
    loading,
    error,
    meta,
    links,

    fetchOwnerPayments,
    fetchOwnerPayment,
    createOwnerPayment,
    updateOwnerPayment,
    deleteOwnerPayment,

    fetchCustomerPayments,
    fetchCustomerPayment,

    fetchPayments,
    fetchPayment,
  }
})
