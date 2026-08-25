import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUnitsStore = defineStore('units', () => {
  const units   = ref([])
  const unit    = ref(null)
  const loading = ref(false)

  // TODO: Implement fetch, create, update, delete actions
  // Owner: use /api/owner/units
  // Customer: use /api/units/:id and /api/properties/:id/units

  return { units, unit, loading }
})
