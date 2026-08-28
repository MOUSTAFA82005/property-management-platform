import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { TOKEN_KEY, USER_KEY } from '../services/api'
import {
  register as registerRequest,
  login as loginRequest,
  logout as logoutRequest,
  me as meRequest,
} from '../services/auth'

/**
 * The single source of truth for authentication state.
 *
 * Views and router guards read from here — never from localStorage directly.
 * localStorage is only the persistence layer behind this store, using the key
 * names exported by services/api.js so the Axios client stays in sync.
 */
export const useAuthStore = defineStore('auth', () => {
  const user = ref(readStoredUser())
  const token = ref(readStoredToken())

  // False until initializeAuth() has settled. Router guards wait on this so a
  // refresh never renders a page based on a half-known auth state.
  const initialized = ref(false)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  function readStoredToken() {
    try {
      return localStorage.getItem(TOKEN_KEY) || null
    } catch {
      return null
    }
  }

  function readStoredUser() {
    try {
      return JSON.parse(localStorage.getItem(USER_KEY) || 'null')
    } catch {
      return null
    }
  }

  function setAuth(userData, tokenData) {
    user.value = userData
    token.value = tokenData

    try {
      localStorage.setItem(USER_KEY, JSON.stringify(userData))
      localStorage.setItem(TOKEN_KEY, tokenData)
    } catch {
      // Private browsing can refuse writes; the session still works in memory.
    }
  }

  function setUser(userData) {
    user.value = userData
    try {
      localStorage.setItem(USER_KEY, JSON.stringify(userData))
    } catch {
      // ignore
    }
  }

  function clearAuth() {
    user.value = null
    token.value = null

    try {
      localStorage.removeItem(USER_KEY)
      localStorage.removeItem(TOKEN_KEY)
    } catch {
      // ignore
    }
  }

  // ── Actions ───────────────────────────────────────────────────────

  async function register(payload) {
    loading.value = true
    try {
      const { data } = await registerRequest(payload)
      setAuth(data.user, data.token)
      return data.user
    } finally {
      loading.value = false
    }
  }

  async function login(credentials) {
    loading.value = true
    try {
      const { data } = await loginRequest(credentials)
      setAuth(data.user, data.token)
      return data.user
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await logoutRequest()
      }
    } catch {
      // The token may already be revoked or expired server-side. Either way
      // the local session goes, so a failure here is not worth surfacing.
    } finally {
      clearAuth()
    }
  }

  /**
   * Re-read the authenticated user from the API. The token is the identity —
   * nothing about the user is trusted from localStorage once this returns.
   */
  async function fetchUser() {
    const { data } = await meRequest()
    setUser(data.user)
    return data.user
  }

  /**
   * Called once at startup. Restores the session behind a stored token, or
   * clears it if the API rejects it.
   */
  async function initializeAuth() {
    if (initialized.value) return

    if (!token.value) {
      clearAuth()
      initialized.value = true
      return
    }

    try {
      await fetchUser()
    } catch (error) {
      // 401 means the token is dead — the Axios interceptor has already
      // cleared state, but clear again so this is correct in isolation.
      // Anything else (server down, network) should not destroy a session
      // that may still be perfectly valid.
      if (error.response?.status === 401) {
        clearAuth()
      }
    } finally {
      initialized.value = true
    }
  }

  // ── Role helpers ──────────────────────────────────────────────────
  // Kept as functions: existing views and stores already call isOwner().

  const isOwner = () => user.value?.role === 'owner'
  const isCustomer = () => user.value?.role === 'customer'

  /** Where this user belongs after signing in. */
  const homeRoute = () => (isOwner() ? '/owner/dashboard' : '/')

  return {
    user,
    token,
    initialized,
    loading,
    isAuthenticated,

    setAuth,
    setUser,
    clearAuth,

    register,
    login,
    logout,
    fetchUser,
    initializeAuth,

    isOwner,
    isCustomer,
    homeRoute,
  }
})
