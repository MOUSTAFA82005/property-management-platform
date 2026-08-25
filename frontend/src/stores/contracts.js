import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useContractsStore = defineStore('contracts', () => {
  const contracts = ref([])
  const contract  = ref(null)
  const loading   = ref(false)

  // TODO: Implement fetch and create actions
  // Owner: use /api/owner/contracts
  // Customer: use /api/contracts

  return { contracts, contract, loading }
})
