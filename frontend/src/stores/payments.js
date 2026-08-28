import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAuthStore } from './auth'
// Imported under a namespace on purpose. When these were imported by bare
// name, the store's own actions shadowed them and called themselves —
// creating, updating or deleting a payment recursed until the stack blew.
import * as paymentService from '../services/payments'
import { extractItem, extractList, normalizeError } from '../services/pagination'

export const usePaymentsStore = defineStore('payments', () => {
  const payments = ref([])
  const payment = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const meta = ref(null)
  const links = ref(null)

  function resetError() {
    error.value = null
  }

  function fail(e) {
    error.value = normalizeError(e).message
    throw e
  }

  // ── Owner ─────────────────────────────────────────────────────────

  async function fetchOwnerPayments(params = {}) {
    loading.value = true
    resetError()
    try {
      const extracted = extractList(await paymentService.getOwnerPayments(params))
      payments.value = extracted.data
      meta.value = extracted.meta
      links.value = extracted.links
      return extracted.data
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchOwnerPayment(id) {
    loading.value = true
    resetError()
    try {
      payment.value = extractItem(await paymentService.getOwnerPayment(id))
      return payment.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function createOwnerPayment(data) {
    loading.value = true
    resetError()
    try {
      const created = extractItem(await paymentService.createOwnerPayment(data))
      payments.value.unshift(created)
      return created
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function updateOwnerPayment(id, data) {
    loading.value = true
    resetError()
    try {
      const updated = extractItem(await paymentService.updateOwnerPayment(id, data))
      const index = payments.value.findIndex((p) => p.id === id)
      if (index !== -1) payments.value[index] = updated
      payment.value = updated
      return updated
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function deleteOwnerPayment(id) {
    loading.value = true
    resetError()
    try {
      await paymentService.deleteOwnerPayment(id)
      payments.value = payments.value.filter((p) => p.id !== id)
      if (payment.value?.id === id) payment.value = null
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  // ── Customer ──────────────────────────────────────────────────────

  async function fetchCustomerPayments(params = {}) {
    loading.value = true
    resetError()
    try {
      const extracted = extractList(await paymentService.getCustomerPayments(params))
      payments.value = extracted.data
      meta.value = extracted.meta
      links.value = extracted.links
      return extracted.data
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchCustomerPayment(id) {
    loading.value = true
    resetError()
    try {
      payment.value = extractItem(await paymentService.getCustomerPayment(id))
      return payment.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  // ── Role-aware entry points ───────────────────────────────────────

  async function fetchPayments(params = {}) {
    const auth = useAuthStore()
    return auth.isOwner() ? fetchOwnerPayments(params) : fetchCustomerPayments(params)
  }

  async function fetchPayment(id) {
    const auth = useAuthStore()
    return auth.isOwner() ? fetchOwnerPayment(id) : fetchCustomerPayment(id)
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
