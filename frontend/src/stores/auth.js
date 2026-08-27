import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || null)

  function setAuth(userData, tokenData) {
    user.value  = userData
    token.value = tokenData
    localStorage.setItem('user',  JSON.stringify(userData))
    localStorage.setItem('token', tokenData)
  }

  function clearAuth() {
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  function initAuth() {
    const stored = localStorage.getItem('token')
    if (stored) {
      token.value = stored
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // proceed even if request fails (token may already be invalid)
    }
    clearAuth()
  }

  // Helper: check if authenticated user is an owner
  const isOwner = () => user.value?.role === 'owner'

  // Helper: check if authenticated user is a customer
  const isCustomer = () => user.value?.role === 'customer'

  return { user, token, setAuth, clearAuth, initAuth, logout, isOwner, isCustomer }
})
