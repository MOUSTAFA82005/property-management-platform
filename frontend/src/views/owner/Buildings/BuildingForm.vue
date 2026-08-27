<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import { useBuildingsStore } from '../../../stores/buildings'
import { usePropertiesStore } from '../../../stores/properties'

const props = defineProps({ mode: { type: String, default: 'create' } })

const route = useRoute()
const router = useRouter()
const buildingsStore = useBuildingsStore()
const propertiesStore = usePropertiesStore()

const isEdit = computed(() => props.mode === 'edit')
const id = route.params.id

const form = reactive({
  property_id: '',
  name: '',
  floors_count: 1,
  description: '',
})

const errors = ref({})
const generalError = ref('')
const feedback = ref('')
const saving = ref(false)
const loadingBuilding = ref(false)

const fieldError = (f) => errors.value[f]?.[0] || ''

onMounted(async () => {
  // Only the owner's own properties are returned, so the picker cannot point
  // at somebody else's portfolio.
  await propertiesStore.fetchOwnerProperties({ per_page: 100 }).catch(() => {})

  if (!isEdit.value) {
    if (route.query.property_id) form.property_id = Number(route.query.property_id)
    return
  }

  loadingBuilding.value = true
  try {
    const building = await buildingsStore.fetchBuilding(id)
    Object.assign(form, {
      property_id: building.property_id,
      name: building.name ?? '',
      floors_count: building.floors_count ?? 1,
      description: building.description ?? '',
    })
  } catch {
    // buildingsStore.error drives the template.
  } finally {
    loadingBuilding.value = false
  }
})

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''
  feedback.value = ''

  const payload = {
    ...form,
    property_id: Number(form.property_id),
    floors_count: Number(form.floors_count),
  }

  try {
    if (isEdit.value) {
      await buildingsStore.updateBuilding(Number(id), payload)
      feedback.value = 'Building updated.'
    } else {
      await buildingsStore.createBuilding(payload)
      router.push('/owner/buildings')
    }
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
    } else {
      generalError.value = e.response?.data?.message || 'Could not save this building.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <RouterLink to="/owner/buildings" class="owner-back">
    <i class="fa-solid fa-arrow-left"></i> Back to Buildings
  </RouterLink>

  <OwnerPageHeader
    :title="isEdit ? `Edit ${form.name}` : 'Add New Building'"
    :subtitle="isEdit ? 'Update this building\'s details.' : 'Buildings group units inside a property.'"
  />

  <div v-if="loadingBuilding" class="owner-card" style="padding: 2rem;">
    <div v-for="n in 3" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
  </div>

  <div v-else-if="isEdit && buildingsStore.error && !form.name" class="empty-box empty-box-error">
    <h3>Could not load this building</h3>
    <p>{{ buildingsStore.error }}</p>
  </div>

  <template v-else>
    <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
    <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

    <div v-if="propertiesStore.properties.length === 0 && !propertiesStore.loading" class="empty-box">
      <div class="empty-icon">🏢</div>
      <h3>You need a property first</h3>
      <p>Buildings belong to a property. Create one, then come back.</p>
      <RouterLink to="/owner/properties/create" class="owner-btn owner-btn-primary" style="display: inline-block; margin-top: 1rem;">
        Add Property
      </RouterLink>
    </div>

    <div v-else class="owner-card owner-form-card">
      <form class="owner-form" @submit.prevent="save">
        <div class="owner-form-grid">
          <div class="owner-field">
            <label for="b-property">Property</label>
            <select id="b-property" v-model="form.property_id" class="owner-select" :class="{ 'is-invalid': fieldError('property_id') }">
              <option value="">Select a property</option>
              <option v-for="p in propertiesStore.properties" :key="p.id" :value="p.id">{{ p.name }} — {{ p.city }}</option>
            </select>
            <small v-if="fieldError('property_id')" class="owner-field-error">{{ fieldError('property_id') }}</small>
          </div>

          <div class="owner-field">
            <label for="b-name">Building name</label>
            <input id="b-name" v-model="form.name" class="owner-input" :class="{ 'is-invalid': fieldError('name') }" placeholder="e.g. Tower A" />
            <small v-if="fieldError('name')" class="owner-field-error">{{ fieldError('name') }}</small>
          </div>

          <div class="owner-field">
            <label for="b-floors">Floors</label>
            <input id="b-floors" v-model="form.floors_count" type="number" min="1" class="owner-input" :class="{ 'is-invalid': fieldError('floors_count') }" />
            <small v-if="fieldError('floors_count')" class="owner-field-error">{{ fieldError('floors_count') }}</small>
          </div>

          <div class="owner-field full">
            <label for="b-desc">Description</label>
            <textarea id="b-desc" v-model="form.description" class="owner-textarea" placeholder="Optional notes about this building..."></textarea>
            <small v-if="fieldError('description')" class="owner-field-error">{{ fieldError('description') }}</small>
          </div>
        </div>

        <div class="owner-form-actions">
          <button type="submit" class="owner-btn owner-btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : isEdit ? 'Update Building' : 'Save Building' }}
          </button>
          <RouterLink to="/owner/buildings" class="owner-btn owner-btn-light">Cancel</RouterLink>
        </div>
      </form>
    </div>
  </template>
</template>
