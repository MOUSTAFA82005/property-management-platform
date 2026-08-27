<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

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
  <div class="sk-page" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
    <form class="sk-form" style="width: 100%; max-width: 400px; padding: 2rem;" @submit.prevent="doRegister">
      <div class="sk-header" style="text-align: center; border: none;">
        <h1>Create Account</h1>
        <p>Join PropSpace today</p>
      </div>

      <div
        v-if="generalError"
        style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem;"
      >
        {{ generalError }}
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="name">Full Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="sk-form-input"
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
          autocomplete="email"
          placeholder="name@example.com"
        />
        <small v-if="fieldError('email')" class="sk-form-error">{{ fieldError('email') }}</small>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label" for="phone">Phone <span style="color: #9ca3af; font-weight: 400;">(optional)</span></label>
        <input
          id="phone"
          v-model="form.phone"
          type="tel"
          class="sk-form-input"
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

      <div class="sk-form-actions" style="margin-top: 1.5rem;">
        <button
          type="submit"
          class="sk-btn sk-btn-primary"
          style="width: 100%; justify-content: center;"
          :disabled="loading"
        >
          {{ loading ? 'Creating account...' : 'Register' }}
        </button>
      </div>

      <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280;">
        Already have an account?
        <RouterLink to="/login" style="color: #864CFF; font-weight: 600;">Login here</RouterLink>
      </p>
    </form>
  </div>
</template>
