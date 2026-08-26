<script setup>
import { RouterLink, useRouter } from 'vue-router'
const router = useRouter()

const doRegister = () => {
  router.push('/')
}
</script>

<template>
  <div class="sk-page" style="display: flex; align-items: center; justify-content: center; min-height: 80vh;">
    <div class="sk-form" style="width: 100%; max-width: 400px; padding: 2rem;">
      <div class="sk-header" style="text-align: center; border: none;">
        <h1>Create Account</h1>
        <p>Join PropSpace today</p>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Full Name</label>
        <input type="text" class="sk-form-input" placeholder="John Doe" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Email Address</label>
        <input type="email" class="sk-form-input" placeholder="name@example.com" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Password</label>
        <input type="password" class="sk-form-input" placeholder="••••••••" />
      </div>

      <div class="sk-form-actions" style="margin-top: 1.5rem;">
        <button class="sk-btn sk-btn-primary" style="width: 100%; justify-content: center;" @click="doRegister">Register</button>
      </div>

      <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280;">
        Already have an account? <RouterLink to="/login" style="color: #864CFF; font-weight: 600;">Login here</RouterLink>
      </p>
    </div>
  </div>
</template>
