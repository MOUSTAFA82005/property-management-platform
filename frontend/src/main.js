// Bootstrap's stylesheet stays for its reset and base typography; the UI
// itself is the project's own owner-*/sk-* system in the two files below.
// Bootstrap's JS bundle is deliberately not imported — nothing uses a
// data-bs-* component, and the menus and modals here are plain Vue state.
import 'bootstrap/dist/css/bootstrap.min.css'
import '@fortawesome/fontawesome-free/css/all.min.css'
import './style.css'
import './style-skeleton.css'
// Last, so its transition tokens and reduced-motion guard win.
import './style-motion.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)

app.use(createPinia())

// Restore the session before the first route resolves, so a refresh never
// flashes the signed-out version of a page the user is allowed to see.
const authStore = useAuthStore()

authStore.initializeAuth().finally(() => {
  app.use(router)
  app.mount('#app')
})
