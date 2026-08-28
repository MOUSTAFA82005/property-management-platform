<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import AuthShell from '../../components/auth/AuthShell.vue'

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
  <AuthShell title="Welcome back" subtitle="Sign in to your PropSpace account.">
    <form class="auth-form" @submit.prevent="doLogin">
      <div v-if="generalError" class="sk-alert-error" role="alert">{{ generalError }}</div>

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
        <label class="sk-form-label" for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="sk-form-input"
          :class="{ 'is-invalid': fieldError('password') }"
          autocomplete="current-password"
          placeholder="Your password"
        />
        <small v-if="fieldError('password')" class="sk-form-error">{{ fieldError('password') }}</small>
      </div>

      <button type="submit" class="sk-btn sk-btn-primary auth-submit" :disabled="loading">
        {{ loading ? 'Logging in...' : 'Login' }}
      </button>

      <p class="auth-alt">
        Don't have an account?
        <RouterLink to="/register">Register here</RouterLink>
      </p>
    </form>
  </AuthShell>
</template>
