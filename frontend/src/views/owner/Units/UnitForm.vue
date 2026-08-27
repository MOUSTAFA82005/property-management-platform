<script setup>
/**
 * Shared create/edit form for units.
 *
 * Create and Edit differ only in what they load and which store action they
 * call, so the fields live here rather than being written twice and drifting.
 */
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import { useBuildingsStore } from '../../../stores/buildings'
import { useUnitsStore, UNIT_STATUSES } from '../../../stores/units'
import { humanStatus } from '../../../utils/format'

const props = defineProps({
  mode: { type: String, default: 'create' },
})

const route = useRoute()
const router = useRouter()
const unitsStore = useUnitsStore()
const buildingsStore = useBuildingsStore()

const isEdit = computed(() => props.mode === 'edit')
const id = route.params.id

const form = reactive({
  building_id: '',
  unit_number: '',
  floor: 0,
  unit_type: 'Apartment',
  area: '',
  bedrooms: 0,
  bathrooms: 1,
  monthly_rent: '',
  status: 'available',
})

const errors = ref({})
const generalError = ref('')
const feedback = ref('')
const saving = ref(false)
const loadingUnit = ref(false)

const fieldError = (f) => errors.value[f]?.[0] || ''

onMounted(async () => {
  // Only the owner's own buildings come back, so the picker cannot be used
  // to attach a unit to someone else's property.
  await buildingsStore.fetchBuildings({ per_page: 100 }).catch(() => {})

  if (!isEdit.value) {
    if (route.query.building_id) form.building_id = Number(route.query.building_id)
    return
  }

  loadingUnit.value = true
  try {
    const unit = await unitsStore.fetchOwnerUnit(id)
    Object.assign(form, {
      building_id: unit.building_id,
      unit_number: unit.unit_number ?? '',
      floor: unit.floor ?? 0,
      unit_type: unit.unit_type ?? '',
      area: unit.area ?? '',
      bedrooms: unit.bedrooms ?? 0,
      bathrooms: unit.bathrooms ?? 0,
      monthly_rent: unit.monthly_rent ?? '',
      status: unit.status ?? 'available',
    })
  } catch {
    // unitsStore.error drives the template.
  } finally {
    loadingUnit.value = false
  }
})

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''
  feedback.value = ''

  const payload = {
    ...form,
    building_id: Number(form.building_id),
    floor: Number(form.floor),
    bedrooms: Number(form.bedrooms),
    bathrooms: Number(form.bathrooms),
    monthly_rent: Number(form.monthly_rent),
    area: form.area === '' ? null : Number(form.area),
  }

  try {
    if (isEdit.value) {
      await unitsStore.updateUnit(Number(id), payload)
      feedback.value = 'Unit updated.'
    } else {
      const created = await unitsStore.createUnit(payload)
      router.push(`/owner/units/${created.id}/edit`)
    }
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
    } else {
      generalError.value = e.response?.data?.message || 'Could not save this unit.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <RouterLink to="/owner/units" class="owner-back">
    <i class="fa-solid fa-arrow-left"></i> Back to Units
  </RouterLink>

  <OwnerPageHeader
    :title="isEdit ? `Edit Unit ${form.unit_number}` : 'Add New Unit'"
    :subtitle="isEdit ? 'Update unit pricing, status and details.' : 'Create a unit inside one of your buildings.'"
  />

  <div v-if="loadingUnit" class="owner-card" style="padding: 2rem;">
    <div v-for="n in 4" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
  </div>

  <div v-else-if="isEdit && unitsStore.error && !form.unit_number" class="empty-box empty-box-error">
    <h3>Could not load this unit</h3>
    <p>{{ unitsStore.error }}</p>
  </div>

  <template v-else>
    <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
    <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

    <div v-if="buildingsStore.buildings.length === 0 && !buildingsStore.loading" class="empty-box">
      <div class="empty-icon">🏗️</div>
      <h3>You need a building first</h3>
      <p>Units belong to a building. Create one, then come back.</p>
      <RouterLink to="/owner/buildings/create" class="owner-btn owner-btn-primary" style="display: inline-block; margin-top: 1rem;">
        Add Building
      </RouterLink>
    </div>

    <div v-else class="owner-card owner-form-card">
      <form class="owner-form" @submit.prevent="save">
        <div class="owner-form-grid">
          <div class="owner-field">
            <label for="u-building">Building</label>
            <select id="u-building" v-model="form.building_id" class="owner-select" :class="{ 'is-invalid': fieldError('building_id') }">
              <option value="">Select a building</option>
              <option v-for="b in buildingsStore.buildings" :key="b.id" :value="b.id">
                {{ b.property?.name }} — {{ b.name }}
              </option>
            </select>
            <small v-if="fieldError('building_id')" class="owner-field-error">{{ fieldError('building_id') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-number">Unit number</label>
            <input id="u-number" v-model="form.unit_number" class="owner-input" :class="{ 'is-invalid': fieldError('unit_number') }" placeholder="e.g. A-101" />
            <small v-if="fieldError('unit_number')" class="owner-field-error">{{ fieldError('unit_number') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-type">Unit type</label>
            <input id="u-type" v-model="form.unit_type" class="owner-input" :class="{ 'is-invalid': fieldError('unit_type') }" list="unit-types" />
            <datalist id="unit-types">
              <option value="Apartment"></option>
              <option value="Studio"></option>
              <option value="Townhouse"></option>
              <option value="Villa"></option>
              <option value="Penthouse"></option>
            </datalist>
            <small v-if="fieldError('unit_type')" class="owner-field-error">{{ fieldError('unit_type') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-rent">Monthly rent (EGP)</label>
            <input id="u-rent" v-model="form.monthly_rent" type="number" min="0" step="0.01" class="owner-input" :class="{ 'is-invalid': fieldError('monthly_rent') }" placeholder="e.g. 14000" />
            <small v-if="fieldError('monthly_rent')" class="owner-field-error">{{ fieldError('monthly_rent') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-floor">Floor</label>
            <input id="u-floor" v-model="form.floor" type="number" min="0" class="owner-input" :class="{ 'is-invalid': fieldError('floor') }" />
            <small v-if="fieldError('floor')" class="owner-field-error">{{ fieldError('floor') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-area">Area (m²)</label>
            <input id="u-area" v-model="form.area" type="number" min="0" step="0.01" class="owner-input" :class="{ 'is-invalid': fieldError('area') }" />
            <small v-if="fieldError('area')" class="owner-field-error">{{ fieldError('area') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-beds">Bedrooms</label>
            <input id="u-beds" v-model="form.bedrooms" type="number" min="0" class="owner-input" :class="{ 'is-invalid': fieldError('bedrooms') }" />
            <small v-if="fieldError('bedrooms')" class="owner-field-error">{{ fieldError('bedrooms') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-baths">Bathrooms</label>
            <input id="u-baths" v-model="form.bathrooms" type="number" min="0" class="owner-input" :class="{ 'is-invalid': fieldError('bathrooms') }" />
            <small v-if="fieldError('bathrooms')" class="owner-field-error">{{ fieldError('bathrooms') }}</small>
          </div>

          <div class="owner-field">
            <label for="u-status">Status</label>
            <select id="u-status" v-model="form.status" class="owner-select" :class="{ 'is-invalid': fieldError('status') }">
              <option v-for="s in UNIT_STATUSES" :key="s" :value="s">{{ humanStatus(s) }}</option>
            </select>
            <small v-if="fieldError('status')" class="owner-field-error">{{ fieldError('status') }}</small>
          </div>
        </div>

        <div class="owner-form-actions">
          <button type="submit" class="owner-btn owner-btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : isEdit ? 'Update Unit' : 'Save Unit' }}
          </button>
          <RouterLink to="/owner/units" class="owner-btn owner-btn-light">Cancel</RouterLink>
        </div>
      </form>
    </div>
  </template>
</template>
