import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getOwnerDashboard } from '../services/dashboard'
import { extractItem, normalizeError } from '../services/pagination'

/**
 * Everything on the owner dashboard comes from one scoped endpoint. The
 * frontend never adds figures up itself.
 */
export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchDashboard() {
    loading.value = true
    error.value = null
    try {
      stats.value = extractItem(await getOwnerDashboard())
      return stats.value
    } catch (e) {
      error.value = normalizeError(e).message
      throw e
    } finally {
      loading.value = false
    }
  }

  return { stats, loading, error, fetchDashboard }
})
