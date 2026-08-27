<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const errors = ref({})
const generalError = ref('')
const loading = ref(false)

function fieldError(field) {
  return errors.value[field]?.[0] || ''
}

async function doLogin() {
  errors.value = {}
  generalError.value = ''
  loading.value = true

  try {
    const user = await authStore.login({ ...form })

    // Honour ?redirect= when the guard bounced the user here, but never send
    // a customer into the owner portal (or the reverse) on the way back.
    const requested = route.query.redirect
    const home = user.role === 'owner' ? '/owner/dashboard' : '/'
    const allowed =
      typeof requested === 'string' &&
      requested.startsWith('/') &&
      (user.role === 'owner') === requested.startsWith('/owner')

    router.push(allowed ? requested : home)
  } catch (e) {
    const status = e.response?.status

    if (status === 422) {
      errors.value = e.response.data?.errors || {}
    } else if (status === 401) {
      generalError.value =
        e.response.data?.message || 'The provided credentials do not match our records.'
    } else if (status === 403) {
      generalError.value = e.response.data?.message || 'This account has been deactivated.'
    } else {
      generalError.value = 'We could not sign you in right now. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="sk-page" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
    <form class="sk-form" style="width: 100%; max-width: 400px; padding: 2rem;" @submit.prevent="doLogin">
      <div class="sk-header" style="text-align: center; border: none;">
        <h1>Welcome Back</h1>
        <p>Login to your PropSpace account</p>
      </div>

      <div
        v-if="generalError"
        style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem;"
      >
        {{ generalError }}
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="email">Email Address</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="sk-form-input"
          autocomplete="email"
          placeholder="name@example.com"
        />
        <small v-if="fieldError('email')" class="sk-form-error">{{ fieldError('email') }}</small>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="sk-form-input"
          autocomplete="current-password"
          placeholder="••••••••"
        />
        <small v-if="fieldError('password')" class="sk-form-error">{{ fieldError('password') }}</small>
      </div>

      <div class="sk-form-actions" style="flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
        <button
          type="submit"
          class="sk-btn sk-btn-primary"
          style="justify-content: center;"
          :disabled="loading"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </div>

      <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280;">
        Don't have an account?
        <RouterLink to="/register" style="color: #864CFF; font-weight: 600;">Register here</RouterLink>
      </p>
    </form>
  </div>
</template>
