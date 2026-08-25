import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePaymentsStore = defineStore('payments', () => {
  const payments = ref([])
  const payment  = ref(null)
  const loading  = ref(false)

  // TODO: Implement fetch and record actions
  // Owner: use /api/owner/payments
  // Customer: use /api/payments

  return { payments, payment, loading }
})
