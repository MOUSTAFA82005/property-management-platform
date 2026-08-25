import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePurchaseRequestsStore = defineStore('purchaseRequests', () => {
  const purchaseRequests = ref([])
  const purchaseRequest  = ref(null)
  const loading          = ref(false)

  // TODO: Implement fetch, submit, approve, reject, cancel actions
  // Owner: use /api/owner/purchase-requests
  // Customer: use /api/purchase-requests

  return { purchaseRequests, purchaseRequest, loading }
})
