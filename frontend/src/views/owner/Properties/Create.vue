<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import { usePropertiesStore } from '../../../stores/properties'

const router = useRouter()
const store = usePropertiesStore()

const form = reactive({
  name: '',
  address: '',
  city: '',
  property_type: 'Apartment Building',
  status: 'active',
  description: '',
  is_published: false,
})

const errors = ref({})
const generalError = ref('')
const saving = ref(false)

const fieldError = (f) => errors.value[f]?.[0] || ''

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''

  try {
    const created = await store.createProperty({ ...form })
    router.push(`/owner/properties/${created.id}`)
  } catch (e) {
    if (e.response?.status === 422) {
      // Entered values stay in `form`, so nothing the owner typed is lost.
      errors.value = e.response.data?.errors || {}
    } else {
      generalError.value = e.response?.data?.message || 'Could not save this property.'
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

  <OwnerPageHeader title="Add New Property" subtitle="Create a new property listing for your portfolio." />

  <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

  <div class="owner-card owner-form-card">
    <form class="owner-form" @submit.prevent="save">
      <div class="owner-form-grid">
        <div class="owner-field">
          <label for="name">Property name</label>
          <input id="name" v-model="form.name" class="owner-input" :class="{ 'is-invalid': fieldError('name') }" placeholder="e.g. Nile View Residences" />
          <small v-if="fieldError('name')" class="owner-field-error">{{ fieldError('name') }}</small>
        </div>

        <div class="owner-field">
          <label for="city">City</label>
          <input id="city" v-model="form.city" class="owner-input" :class="{ 'is-invalid': fieldError('city') }" placeholder="e.g. Cairo" />
          <small v-if="fieldError('city')" class="owner-field-error">{{ fieldError('city') }}</small>
        </div>

        <div class="owner-field full">
          <label for="address">Address</label>
          <input id="address" v-model="form.address" class="owner-input" :class="{ 'is-invalid': fieldError('address') }" placeholder="e.g. 18 Corniche El Nil, Garden City" />
          <small v-if="fieldError('address')" class="owner-field-error">{{ fieldError('address') }}</small>
        </div>

        <div class="owner-field">
          <label for="type">Property type</label>
          <input id="type" v-model="form.property_type" class="owner-input" :class="{ 'is-invalid': fieldError('property_type') }" list="property-types" />
          <datalist id="property-types">
            <option value="Apartment Building"></option>
            <option value="Residential Compound"></option>
            <option value="Villa"></option>
            <option value="Commercial Tower"></option>
          </datalist>
          <small v-if="fieldError('property_type')" class="owner-field-error">{{ fieldError('property_type') }}</small>
        </div>

        <div class="owner-field">
          <label for="status">Status</label>
          <select id="status" v-model="form.status" class="owner-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="owner-field full">
          <label for="description">Description</label>
          <textarea id="description" v-model="form.description" class="owner-textarea" placeholder="Describe the property..."></textarea>
          <small v-if="fieldError('description')" class="owner-field-error">{{ fieldError('description') }}</small>
        </div>

        <div class="owner-field full">
          <label style="display: flex; align-items: center; gap: .5rem; cursor: pointer;">
            <input v-model="form.is_published" type="checkbox" />
            Publish immediately so customers can browse it
          </label>
        </div>
      </div>

      <div class="owner-form-actions">
        <button type="submit" class="owner-btn owner-btn-primary" :disabled="saving">
          {{ saving ? 'Saving...' : 'Save Property' }}
        </button>
        <RouterLink to="/owner/properties" class="owner-btn owner-btn-light">Cancel</RouterLink>
      </div>
    </form>
  </div>
</template>
