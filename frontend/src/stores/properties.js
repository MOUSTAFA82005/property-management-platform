import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as propertyService from '../services/properties'
import { extractItem, extractList, normalizeError } from '../services/pagination'

export const usePropertiesStore = defineStore('properties', () => {
  const properties = ref([])
  const property = ref(null)
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
    properties.value = extracted.data
    meta.value = extracted.meta
    return extracted.data
  }

  // ── Public catalog (no authentication) ────────────────────────────

  async function fetchPublicProperties(params = {}) {
    loading.value = true
    resetError()
    try {
      return applyList(extractList(await propertyService.getPublicProperties(params)))
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchPublicProperty(id) {
    loading.value = true
    resetError()
    property.value = null
    try {
      property.value = extractItem(await propertyService.getPublicProperty(id))
      return property.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  // ── Owner ─────────────────────────────────────────────────────────

  async function fetchOwnerProperties(params = {}) {
    loading.value = true
    resetError()
    try {
      return applyList(extractList(await propertyService.getOwnerProperties(params)))
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function fetchOwnerProperty(id) {
    loading.value = true
    resetError()
    property.value = null
    try {
      property.value = extractItem(await propertyService.getOwnerProperty(id))
      return property.value
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function createProperty(data) {
    loading.value = true
    resetError()
    try {
      const created = extractItem(await propertyService.createOwnerProperty(data))
      properties.value.unshift(created)
      return created
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function updateProperty(id, data) {
    loading.value = true
    resetError()
    try {
      const updated = extractItem(await propertyService.updateOwnerProperty(id, data))
      replaceInList(updated)
      property.value = updated
      return updated
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  async function deleteProperty(id) {
    loading.value = true
    resetError()
    try {
      await propertyService.deleteOwnerProperty(id)
      properties.value = properties.value.filter((p) => p.id !== id)
      if (property.value?.id === id) property.value = null
    } catch (e) {
      fail(e)
    } finally {
      loading.value = false
    }
  }

  /** Publish/unpublish return the updated property, so trust the response. */
  async function setPublished(id, published) {
    resetError()
    try {
      const call = published
        ? propertyService.publishOwnerProperty
        : propertyService.unpublishOwnerProperty
      const updated = extractItem(await call(id))
      replaceInList(updated)
      if (property.value?.id === id) property.value = updated
      return updated
    } catch (e) {
      fail(e)
    }
  }

  function replaceInList(updated) {
    if (!updated) return
    const index = properties.value.findIndex((p) => p.id === updated.id)
    if (index !== -1) properties.value[index] = updated
  }

  return {
    properties,
    property,
    loading,
    error,
    meta,

    fetchPublicProperties,
    fetchPublicProperty,

    fetchOwnerProperties,
    fetchOwnerProperty,
    createProperty,
    updateProperty,
    deleteProperty,
    setPublished,
  }
})
