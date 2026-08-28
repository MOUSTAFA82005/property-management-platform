<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import AuthShell from '../../components/auth/AuthShell.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const generalError = ref('')
const loading = ref(false)

function fieldError(field) {
  return errors.value[field]?.[0] || ''
}

async function doRegister() {
  errors.value = {}
  generalError.value = ''
  loading.value = true

  try {
    const user = await authStore.register({ ...form })
    router.push(user.role === 'owner' ? '/owner/dashboard' : '/')
  } catch (e) {
    const status = e.response?.status

    if (status === 422) {
      errors.value = e.response.data?.errors || {}
      generalError.value = Object.keys(errors.value).length
        ? ''
        : e.response.data?.message || 'Please check the form and try again.'
    } else {
      generalError.value =
        e.response?.data?.message ||
        'We could not create your account right now. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AuthShell title="Create your account" subtitle="Join PropSpace to browse properties and manage your requests.">
    <form class="auth-form" @submit.prevent="doRegister">
      <div v-if="generalError" class="sk-alert-error" role="alert">{{ generalError }}</div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="name">Full Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="sk-form-input"
          :class="{ 'is-invalid': fieldError('name') }"
          autocomplete="name"
          placeholder="Your full name"
        />
        <small v-if="fieldError('name')" class="sk-form-error">{{ fieldError('name') }}</small>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="email">Email Address</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="sk-form-input"
          :class="{ 'is-invalid': fieldError('email') }"
          autocomplete="email"
          placeholder="name@example.com"
        />
        <small v-if="fieldError('email')" class="sk-form-error">{{ fieldError('email') }}</small>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="phone">Phone <span class="sk-form-optional">(optional)</span></label>
        <input
          id="phone"
          v-model="form.phone"
          type="tel"
          class="sk-form-input"
          :class="{ 'is-invalid': fieldError('phone') }"
          autocomplete="tel"
          placeholder="01012345678"
        />
        <small v-if="fieldError('phone')" class="sk-form-error">{{ fieldError('phone') }}</small>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="sk-form-input"
          :class="{ 'is-invalid': fieldError('password') }"
          autocomplete="new-password"
          placeholder="At least 8 characters"
        />
        <small v-if="fieldError('password')" class="sk-form-error">{{ fieldError('password') }}</small>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="password_confirmation">Confirm Password</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="sk-form-input"
          autocomplete="new-password"
          placeholder="Re-enter your password"
        />
      </div>

      <button type="submit" class="sk-btn sk-btn-primary auth-submit" :disabled="loading">
        {{ loading ? 'Creating account...' : 'Register' }}
      </button>

      <p class="auth-alt">
        Already have an account?
        <RouterLink to="/login">Login here</RouterLink>
      </p>
    </form>
  </AuthShell>
</template>
