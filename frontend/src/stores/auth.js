import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || null)

  function setAuth(userData, tokenData) {
    // TODO: Persist user + token to state and localStorage
    // TODO: Set api.defaults.headers.common['Authorization']
  }

  function clearAuth() {
    // TODO: Clear state, localStorage, and Authorization header
  }

  function initAuth() {
    // TODO: Re-attach token header on app boot if token exists
  }

  async function logout() {
    // TODO: POST /api/auth/logout then call clearAuth()
  }

  // Helper: check if authenticated user is an owner
  const isOwner = () => user.value?.role === 'owner'

  // Helper: check if authenticated user is a customer
  const isCustomer = () => user.value?.role === 'customer'

  return { user, token, setAuth, clearAuth, initAuth, logout, isOwner, isCustomer }
})
