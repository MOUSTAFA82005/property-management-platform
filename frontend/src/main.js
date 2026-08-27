import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@fortawesome/fontawesome-free/css/all.min.css'
import './style.css'
import './style-skeleton.css'
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
