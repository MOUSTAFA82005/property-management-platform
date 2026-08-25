<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const router    = useRouter()
const authStore = useAuthStore()

const email    = ref('')
const password = ref('')
const error    = ref('')
const loading  = ref(false)

async function doLogin() {
  error.value   = ''
  loading.value = true

  try {
    const { data } = await api.post('/auth/login', {
      email:    email.value,
      password: password.value,
    })

    authStore.setAuth(data.user, data.token)
    router.push('/')
  } catch (e) {
    const msg = e.response?.data?.message
                || e.response?.data?.errors?.email?.[0]
                || 'Login failed. Please check your credentials.'
    error.value = msg
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="sk-page" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
    <div class="sk-form" style="width: 100%; max-width: 400px; padding: 2rem;">
      <div class="sk-header" style="text-align: center; border: none;">
        <h1>Welcome Back</h1>
        <p>Login to your PropSpace account</p>
      </div>

      <div v-if="error" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem;">
        {{ error }}
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Email Address</label>
        <input v-model="email" type="email" class="sk-form-input" placeholder="name@example.com" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Password</label>
        <input v-model="password" type="password" class="sk-form-input" placeholder="••••••••" />
      </div>

      <div class="sk-form-actions" style="flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
        <button class="sk-btn sk-btn-primary" style="justify-content: center;" :disabled="loading" @click="doLogin">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </div>

      <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280;">
        Don't have an account? <RouterLink to="/register" style="color: #864CFF; font-weight: 600;">Register here</RouterLink>
      </p>
    </div>
  </div>
</template>
