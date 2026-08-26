<script setup>
import { RouterLink, useRouter } from 'vue-router'
const router = useRouter()

const doLogin = (role) => {
  if (role === 'owner') {
    router.push('/owner/dashboard')
  } else {
    router.push('/')
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

      <div class="sk-form-group">
        <label class="sk-form-label">Email Address</label>
        <input type="email" class="sk-form-input" placeholder="name@example.com" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Password</label>
        <input type="password" class="sk-form-input" placeholder="••••••••" />
      </div>

      <div class="sk-form-actions" style="flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
        <button class="sk-btn sk-btn-primary" style="justify-content: center;" @click="doLogin('customer')">Login as Customer</button>
        <button class="sk-btn sk-btn-secondary" style="justify-content: center;" @click="doLogin('owner')">Login as Owner (Demo)</button>
      </div>

      <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280;">
        Don't have an account? <RouterLink to="/register" style="color: #864CFF; font-weight: 600;">Register here</RouterLink>
      </p>
    </div>
  </div>
</template>
