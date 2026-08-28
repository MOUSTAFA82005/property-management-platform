<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import OwnerPageHeader from '../../components/owner/OwnerPageHeader.vue'
import { useAuthStore } from '../../stores/auth'
import { getProfile, updateProfile } from '../../services/profile'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const initial = computed(() => (user.value?.name?.trim()[0] || 'O').toUpperCase())
const roleLabel = computed(() => {
  const role = user.value?.role
  return role ? role.charAt(0).toUpperCase() + role.slice(1) : '—'
})

const editing = ref(false)
const saving = ref(false)
const errors = ref({})
const generalError = ref('')
const feedback = ref('')

const form = reactive({
  name: '',
  email: '',
  phone: '',
  current_password: '',
  password: '',
  password_confirmation: '',
})

const fieldError = (f) => errors.value[f]?.[0] || ''

function startEditing() {
  Object.assign(form, {
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || '',
    current_password: '',
    password: '',
    password_confirmation: '',
  })
  errors.value = {}
  generalError.value = ''
  feedback.value = ''
  editing.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''

  const payload = { name: form.name, email: form.email, phone: form.phone || null }
  if (form.password) {
    payload.current_password = form.current_password
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  try {
    const { data } = await updateProfile(payload)
    authStore.setUser(data.user)
    feedback.value = data.message || 'Profile updated.'
    editing.value = false
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
    } else {
      generalError.value = e.response?.data?.message || 'Could not save your profile.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await getProfile()
    authStore.setUser(data.user)
  } catch {
    // Fall back to the user already held in the auth store.
  }
})
</script>

<template>
  <OwnerPageHeader title="My Profile" subtitle="Your account details for the owner portal." />

  <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
  <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

  <div class="owner-card owner-form-card">
    <div class="owner-card-head">
      <h2>Account</h2>
      <button v-if="!editing" class="owner-btn owner-btn-light" @click="startEditing">Edit profile</button>
    </div>

    <!-- Who is signed in, as readable text. The fields below sit in readonly
         inputs, whose values are invisible to text-based tooling. -->
    <div class="owner-identity">
      <span class="owner-avatar" aria-hidden="true">{{ initial }}</span>
      <div class="owner-identity-text">
        <b>{{ user?.name || '—' }}</b>
        <small>{{ user?.email || '—' }}</small>
      </div>
      <span class="owner-identity-role">{{ roleLabel }}</span>
    </div>

    <!-- Read-only. Role and status are shown but never editable — the API
         rejects changing them and this form never sends them. -->
    <div v-if="!editing" class="owner-form">
      <div class="owner-form-grid">
        <div class="owner-field"><label for="ro-owner-name">Name</label><input id="ro-owner-name" class="owner-input" :value="user?.name || '—'" readonly /></div>
        <div class="owner-field"><label for="ro-owner-email">Email</label><input id="ro-owner-email" class="owner-input" :value="user?.email || '—'" readonly /></div>
        <div class="owner-field"><label for="ro-owner-phone">Phone</label><input id="ro-owner-phone" class="owner-input" :value="user?.phone || '—'" readonly /></div>
        <div class="owner-field"><label for="ro-owner-role">Role</label><input id="ro-owner-role" class="owner-input" :value="user?.role || '—'" readonly style="text-transform: capitalize;" /></div>
        <div class="owner-field"><label for="ro-owner-status">Status</label><input id="ro-owner-status" class="owner-input" :value="user?.status || '—'" readonly style="text-transform: capitalize;" /></div>
      </div>
    </div>

    <form v-else class="owner-form" @submit.prevent="save">
      <div class="owner-form-grid">
        <div class="owner-field">
          <label for="o-name">Name</label>
          <input id="o-name" v-model="form.name" class="owner-input" :class="{ 'is-invalid': fieldError('name') }" />
          <small v-if="fieldError('name')" class="owner-field-error">{{ fieldError('name') }}</small>
        </div>

        <div class="owner-field">
          <label for="o-email">Email</label>
          <input id="o-email" v-model="form.email" type="email" class="owner-input" :class="{ 'is-invalid': fieldError('email') }" />
          <small v-if="fieldError('email')" class="owner-field-error">{{ fieldError('email') }}</small>
        </div>

        <div class="owner-field">
          <label for="o-phone">Phone</label>
          <input id="o-phone" v-model="form.phone" type="tel" class="owner-input" :class="{ 'is-invalid': fieldError('phone') }" placeholder="01012345678" />
          <small v-if="fieldError('phone')" class="owner-field-error">{{ fieldError('phone') }}</small>
        </div>

        <div class="owner-field">
          <label for="o-current">Current password</label>
          <input id="o-current" v-model="form.current_password" type="password" class="owner-input" :class="{ 'is-invalid': fieldError('current_password') }" autocomplete="current-password" />
          <small v-if="fieldError('current_password')" class="owner-field-error">{{ fieldError('current_password') }}</small>
        </div>

        <div class="owner-field">
          <label for="o-new">New password</label>
          <input id="o-new" v-model="form.password" type="password" class="owner-input" :class="{ 'is-invalid': fieldError('password') }" autocomplete="new-password" />
          <small v-if="fieldError('password')" class="owner-field-error">{{ fieldError('password') }}</small>
        </div>

        <div class="owner-field">
          <label for="o-confirm">Confirm new password</label>
          <input id="o-confirm" v-model="form.password_confirmation" type="password" class="owner-input" autocomplete="new-password" />
        </div>
      </div>

      <div class="owner-form-actions">
        <button type="submit" class="owner-btn owner-btn-primary" :disabled="saving">
          {{ saving ? 'Saving...' : 'Save changes' }}
        </button>
        <button type="button" class="owner-btn owner-btn-light" :disabled="saving" @click="editing = false">Cancel</button>
      </div>
    </form>
  </div>
</template>
