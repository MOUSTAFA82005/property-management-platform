import { defineStore } from 'pinia'
import { ref } from 'vue'

export const usePropertiesStore = defineStore('properties', () => {
  const properties = ref([])
  const property   = ref(null)
  const loading    = ref(false)

  // TODO: Implement fetch, create, update, delete, publish, unpublish actions
  // Owner: use /api/owner/properties
  // Customer: use /api/properties

  return { properties, property, loading }
})
