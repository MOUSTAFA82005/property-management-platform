import axios from 'axios'

/**
 * The single Axios client for the whole application.
 *
 * Every service imports this instance — there is no second client and no
 * per-service base URL. Authentication is attached here so no service or
 * view ever sets an Authorization header by hand.
 */

// The one place these key names are defined. The auth store imports them so
// that the client and the store can never drift onto different keys.
export const TOKEN_KEY = 'token'
export const USER_KEY = 'user'

export function getStoredToken() {
  try {
    return localStorage.getItem(TOKEN_KEY)
  } catch {
    return null
  }
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// ── Request: attach the bearer token ────────────────────────────────
api.interceptors.request.use((config) => {
  const token = getStoredToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ── Response: handle expired/revoked tokens in one place ────────────

// Login and register legitimately answer 401/422 for bad credentials. Clearing
// state and redirecting on those would fight the form, so they opt out.
const AUTH_ENTRY_ROUTES = ['/auth/login', '/auth/register']

function isAuthEntryRequest(config) {
  const url = config?.url || ''
  return AUTH_ENTRY_ROUTES.some((route) => url.endsWith(route))
}

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status

    if (status === 401 && !isAuthEntryRequest(error.config)) {
      // Imported lazily: this module is a dependency of the store and the
      // router, so a static import would close a cycle.
      const [{ useAuthStore }, { default: router }] = await Promise.all([
        import('../stores/auth'),
        import('../router'),
      ])

      const auth = useAuthStore()
      auth.clearAuth()

      const current = router.currentRoute.value

      // During startup rehydration the router has not navigated yet (the
      // initial route has no name). Redirecting here would race the first
      // navigation, and it is unnecessary: auth state is already cleared, so
      // the route guard will send the user to login if the page needs it.
      const routerHasNavigated = current.name != null

      // Guard against redirect loops when we are already on the login page.
      if (routerHasNavigated && current.name !== 'login' && current.name !== 'register') {
        router.push({ name: 'login', query: { redirect: current.fullPath } })
      }
    }

    return Promise.reject(error)
  },
)

export default api
