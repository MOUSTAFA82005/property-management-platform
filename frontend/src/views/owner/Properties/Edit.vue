<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import { usePropertiesStore } from '../../../stores/properties'

const route = useRoute()
const router = useRouter()
const store = usePropertiesStore()

const id = route.params.id
const form = reactive({
  name: '',
  address: '',
  city: '',
  property_type: '',
  status: 'active',
  description: '',
  is_published: false,
})

const errors = ref({})
const generalError = ref('')
const feedback = ref('')
const saving = ref(false)

const fieldError = (f) => errors.value[f]?.[0] || ''

onMounted(async () => {
  try {
    const property = await store.fetchOwnerProperty(id)
    Object.assign(form, {
      name: property.name ?? '',
      address: property.address ?? '',
      city: property.city ?? '',
      property_type: property.property_type ?? '',
      status: property.status ?? 'active',
      description: property.description ?? '',
      is_published: !!property.is_published,
    })
  } catch {
    // store.error already carries the message for the template.
  }
})

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''
  feedback.value = ''

  try {
    await store.updateProperty(Number(id), { ...form })
    feedback.value = 'Property updated.'
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
    } else {
      generalError.value = e.response?.data?.message || 'Could not update this property.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <RouterLink to="/owner/properties" class="owner-back">
    <i class="fa-solid fa-arrow-left"></i> Back to Properties
  </RouterLink>

  <OwnerPageHeader :title="form.name ? `Edit ${form.name}` : 'Edit Property'" subtitle="Update property information and availability." />

  <div v-if="store.loading && !form.name" class="owner-card" style="padding: 2rem;">
    <div v-for="n in 4" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
  </div>

  <div v-else-if="store.error && !form.name" class="empty-box empty-box-error">
    <h3>Could not load this property</h3>
    <p>{{ store.error }}</p>
    <RouterLink to="/owner/properties" class="owner-btn owner-btn-primary" style="display: inline-block; margin-top: 1rem;">
      Back to Properties
    </RouterLink>
  </div>

  <template v-else>
    <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
    <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

    <div class="owner-card owner-form-card">
      <form class="owner-form" @submit.prevent="save">
        <div class="owner-form-grid">
          <div class="owner-field">
            <label for="e-name">Property name</label>
            <input id="e-name" v-model="form.name" class="owner-input" :class="{ 'is-invalid': fieldError('name') }" />
            <small v-if="fieldError('name')" class="owner-field-error">{{ fieldError('name') }}</small>
          </div>

          <div class="owner-field">
            <label for="e-city">City</label>
            <input id="e-city" v-model="form.city" class="owner-input" :class="{ 'is-invalid': fieldError('city') }" />
            <small v-if="fieldError('city')" class="owner-field-error">{{ fieldError('city') }}</small>
          </div>

          <div class="owner-field full">
            <label for="e-address">Address</label>
            <input id="e-address" v-model="form.address" class="owner-input" :class="{ 'is-invalid': fieldError('address') }" />
            <small v-if="fieldError('address')" class="owner-field-error">{{ fieldError('address') }}</small>
          </div>

          <div class="owner-field">
            <label for="e-type">Property type</label>
            <input id="e-type" v-model="form.property_type" class="owner-input" :class="{ 'is-invalid': fieldError('property_type') }" />
            <small v-if="fieldError('property_type')" class="owner-field-error">{{ fieldError('property_type') }}</small>
          </div>

          <div class="owner-field">
            <label for="e-status">Status</label>
            <select id="e-status" v-model="form.status" class="owner-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="owner-field full">
            <label for="e-desc">Description</label>
            <textarea id="e-desc" v-model="form.description" class="owner-textarea"></textarea>
          </div>

          <div class="owner-field full">
            <label style="display: flex; align-items: center; gap: .5rem; cursor: pointer;">
              <input v-model="form.is_published" type="checkbox" />
              Published in the public catalog
            </label>
          </div>
        </div>

        <div class="owner-form-actions">
          <button type="submit" class="owner-btn owner-btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : 'Update Property' }}
          </button>
          <RouterLink to="/owner/properties" class="owner-btn owner-btn-light">Cancel</RouterLink>
        </div>
      </form>
    </div>
  </template>
</template>
