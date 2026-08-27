import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as unitService from '../services/units'
import { extractItem, extractList, normalizeError } from '../services/pagination'

/** The only statuses the schema supports. There is no `sold`. */
export const UNIT_STATUSES = ['available', 'occupied', 'reserved']

export const useUnitsStore = defineStore('units', () => {
  const units = ref([])
  const unit = ref(null)
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

  function applyList(extracted) {
    units.value = extracted.data
    meta.value = extracted.meta
    return extracted.data
  }

  // ── Public ────────────────────────────────────────────────────────

  async function fetchPropertyUnits(propertyId, params = {}) {
    loading.value = true
    resetError()
    try {
      return applyList(extractList(await unitService.getPublicPropertyUnits(propertyId, params)))
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchPublicUnit(id) {
    loading.value = true
    resetError()
    unit.value = null
    try {
      unit.value = extractItem(await unitService.getPublicUnit(id))
      return unit.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  // ── Owner ─────────────────────────────────────────────────────────

  async function fetchOwnerUnits(params = {}) {
    loading.value = true
    resetError()
    try {
      return applyList(extractList(await unitService.getOwnerUnits(params)))
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchOwnerUnit(id) {
    loading.value = true
    resetError()
    unit.value = null
    try {
      unit.value = extractItem(await unitService.getOwnerUnit(id))
      return unit.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function createUnit(data) {
    loading.value = true
    resetError()
    try {
      const created = extractItem(await unitService.createOwnerUnit(data))
      units.value.unshift(created)
      return created
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function updateUnit(id, data) {
    loading.value = true
    resetError()
    try {
      const updated = extractItem(await unitService.updateOwnerUnit(id, data))
      const index = units.value.findIndex((u) => u.id === id)
      if (index !== -1) units.value[index] = updated
      unit.value = updated
      return updated
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function deleteUnit(id) {
    loading.value = true
    resetError()
    try {
      await unitService.deleteOwnerUnit(id)
      units.value = units.value.filter((u) => u.id !== id)
      if (unit.value?.id === id) unit.value = null
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  return {
    units,
    unit,
    loading,
    error,
    meta,

    fetchPropertyUnits,
    fetchPublicUnit,

    fetchOwnerUnits,
    fetchOwnerUnit,
    createUnit,
    updateUnit,
    deleteUnit,
  }
})
