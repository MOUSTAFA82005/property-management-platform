import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as buildingService from '../services/buildings'
import { extractItem, extractList, normalizeError } from '../services/pagination'

export const useBuildingsStore = defineStore('buildings', () => {
  const buildings = ref([])
  const building = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const meta = ref(null)

  function resetError() {
    error.value = null
  }

  function fail(e) {
    error.value = normalizeError(e).message
    throw e
  }

  async function fetchBuildings(params = {}) {
    loading.value = true
    resetError()
    try {
      const extracted = extractList(await buildingService.getOwnerBuildings(params))
      buildings.value = extracted.data
      meta.value = extracted.meta
      return extracted.data
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchBuilding(id) {
    loading.value = true
    resetError()
    building.value = null
    try {
      building.value = extractItem(await buildingService.getOwnerBuilding(id))
      return building.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function createBuilding(data) {
    loading.value = true
    resetError()
    try {
      const created = extractItem(await buildingService.createOwnerBuilding(data))
      buildings.value.unshift(created)
      return created
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function updateBuilding(id, data) {
    loading.value = true
    resetError()
    try {
      const updated = extractItem(await buildingService.updateOwnerBuilding(id, data))
      const index = buildings.value.findIndex((b) => b.id === id)
      if (index !== -1) buildings.value[index] = updated
      building.value = updated
      return updated
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function deleteBuilding(id) {
    loading.value = true
    resetError()
    try {
      await buildingService.deleteOwnerBuilding(id)
      buildings.value = buildings.value.filter((b) => b.id !== id)
      if (building.value?.id === id) building.value = null
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  return {
    buildings,
    building,
    loading,
    error,
    meta,
    fetchBuildings,
    fetchBuilding,
    createBuilding,
    updateBuilding,
    deleteBuilding,
  }
})
