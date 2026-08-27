<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { usePaymentsStore } from '../../stores/payments'
import { getProfile, updateProfile } from '../../services/profile'
import CustomerDashboardLayout from '../../components/customer/CustomerDashboardLayout.vue'

const authStore = useAuthStore()
const paymentsStore = usePaymentsStore()

const user = computed(() => authStore.user)

const editing = ref(false)
const saving = ref(false)
const errors = ref({})
const generalError = ref('')
const feedback = ref('')

const form = reactive({
  name: '',
  email: '',
  phone: '',
  current_password: '',
  password: '',
  password_confirmation: '',
})

function fieldError(field) {
  return errors.value[field]?.[0] || ''
}

function startEditing() {
  form.name = user.value?.name || ''
  form.email = user.value?.email || ''
  form.phone = user.value?.phone || ''
  form.current_password = ''
  form.password = ''
  form.password_confirmation = ''
  errors.value = {}
  generalError.value = ''
  feedback.value = ''
  editing.value = true
}

function cancelEditing() {
  editing.value = false
  errors.value = {}
  generalError.value = ''
}

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''

  // Only send a password when the user actually typed one.
  const payload = { name: form.name, email: form.email, phone: form.phone || null }
  if (form.password) {
    payload.current_password = form.current_password
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  try {
    const { data } = await updateProfile(payload)
    // Keep the auth store in step so the navbar and layouts update too.
    authStore.setUser(data.user)
    feedback.value = data.message || 'Profile updated.'
    editing.value = false
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      generalError.value = Object.keys(errors.value).length ? '' : 'Please check the form and try again.'
    } else {
      generalError.value = e.response?.data?.message || 'Could not save your profile right now.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  // The token is the source of truth for identity; refresh from the API so a
  // profile changed elsewhere shows up here.
  try {
    const { data } = await getProfile()
    authStore.setUser(data.user)
  } catch {
    // Non-fatal: fall back to the user already in the store.
  }

  try {
    await paymentsStore.fetchCustomerPayments()
  } catch {
    // Non-critical: the payment count simply stays hidden.
  }
})

const paymentCount = computed(() => {
  const list = paymentsStore.payments
  return Array.isArray(list) && list.length > 0 ? list.length : null
})
</script>

<template>
  <CustomerDashboardLayout>
    <!-- Welcome -->
    <div class="dash-welcome-section">
      <h2 class="dash-welcome">Welcome back, {{ user?.name || 'there' }}</h2>
      <p class="dash-subtitle">Here is a quick overview of your account.</p>
    </div>

    <!-- Personal Information -->
    <div class="dash-section">
      <div class="dash-section-header">
        <h3 class="dash-section-title">Personal Information</h3>
        <button v-if="!editing" class="sk-btn sk-btn-secondary" @click="startEditing">Edit profile</button>
      </div>

      <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
      <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

      <!-- Read-only view -->
      <div v-if="!editing" class="info-card">
        <div class="info-row">
          <span class="info-label">Full Name</span>
          <span class="info-value">{{ user?.name || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Email Address</span>
          <span class="info-value">{{ user?.email || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Phone</span>
          <span class="info-value">{{ user?.phone || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Account Type</span>
          <span class="info-value" style="text-transform: capitalize;">{{ user?.role || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Account Status</span>
          <span class="info-value">
            <span class="status-badge status-active">{{ user?.status || '—' }}</span>
          </span>
        </div>
      </div>

      <!-- Edit form. Role and status are shown above but never editable:
           the API refuses to change them and the form does not send them. -->
      <form v-else class="info-card" @submit.prevent="save">
        <div class="sk-form-group">
          <label class="sk-form-label" for="p-name">Full Name</label>
          <input id="p-name" v-model="form.name" type="text" class="sk-form-input" />
          <small v-if="fieldError('name')" class="sk-form-error">{{ fieldError('name') }}</small>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label" for="p-email">Email Address</label>
          <input id="p-email" v-model="form.email" type="email" class="sk-form-input" />
          <small v-if="fieldError('email')" class="sk-form-error">{{ fieldError('email') }}</small>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label" for="p-phone">Phone</label>
          <input id="p-phone" v-model="form.phone" type="tel" class="sk-form-input" placeholder="01012345678" />
          <small v-if="fieldError('phone')" class="sk-form-error">{{ fieldError('phone') }}</small>
        </div>

        <h4 style="margin: 1.5rem 0 0.75rem; font-size: 0.9rem; color: #374151;">
          Change password <span style="font-weight: 400; color: #9ca3af;">(optional)</span>
        </h4>

        <div class="sk-form-group">
          <label class="sk-form-label" for="p-current">Current Password</label>
          <input id="p-current" v-model="form.current_password" type="password" class="sk-form-input" autocomplete="current-password" />
          <small v-if="fieldError('current_password')" class="sk-form-error">{{ fieldError('current_password') }}</small>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label" for="p-new">New Password</label>
          <input id="p-new" v-model="form.password" type="password" class="sk-form-input" autocomplete="new-password" />
          <small v-if="fieldError('password')" class="sk-form-error">{{ fieldError('password') }}</small>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label" for="p-confirm">Confirm New Password</label>
          <input id="p-confirm" v-model="form.password_confirmation" type="password" class="sk-form-input" autocomplete="new-password" />
        </div>

        <div class="sk-form-actions" style="margin-top: 1.25rem;">
          <button type="submit" class="sk-btn sk-btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save changes' }}
          </button>
          <button type="button" class="sk-btn sk-btn-secondary" :disabled="saving" @click="cancelEditing">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Quick Access -->
    <div class="dash-section">
      <div class="dash-section-header">
        <h3 class="dash-section-title">Quick Access</h3>
      </div>

      <div class="dash-quick-grid">
        <RouterLink to="/purchase-requests" class="quick-card">
          <div class="quick-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
              <path d="M3 9h18M9 9v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="quick-info">
            <div class="quick-title">Purchase Requests</div>
            <div class="quick-desc">View and track your requests</div>
          </div>
          <svg class="quick-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </RouterLink>

        <RouterLink to="/contracts" class="quick-card">
          <div class="quick-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
              <path d="M14 2v6h6M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="quick-info">
            <div class="quick-title">Contracts</div>
            <div class="quick-desc">View your active contracts</div>
          </div>
          <svg class="quick-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </RouterLink>

        <RouterLink to="/payments" class="quick-card">
          <div class="quick-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
              <path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/>
            </svg>
          </div>
          <div class="quick-info">
            <div class="quick-title">Payments</div>
            <div class="quick-desc">
              <span v-if="paymentCount !== null">{{ paymentCount }} payment{{ paymentCount !== 1 ? 's' : '' }} on record</span>
              <span v-else>View your payment history</span>
            </div>
          </div>
          <svg class="quick-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </RouterLink>
      </div>
    </div>
  </CustomerDashboardLayout>
</template>

<style scoped>
.dash-welcome-section {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.dash-welcome {
  font-size: 1.4rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.3rem;
  letter-spacing: -0.02em;
}

.dash-subtitle {
  color: #64748b;
  font-size: 0.925rem;
  margin: 0;
}

.dash-section {
  margin-bottom: 2.5rem;
}

.dash-section-header {
  margin-bottom: 1.25rem;
}

.dash-section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
}

/* Info Card */
.info-card {
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  gap: 1rem;
}

.info-row + .info-row {
  border-top: 1px solid #f1f5f9;
}

.info-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  flex-shrink: 0;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #0f172a;
  text-align: right;
}

.status-badge {
  display: inline-block;
  padding: 0.2rem 0.7rem;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.status-active {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

/* Quick Access */
.dash-quick-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.quick-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  text-decoration: none;
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}

.quick-card:hover {
  border-color: rgba(134, 76, 255, 0.3);
  box-shadow: 0 4px 16px rgba(134, 76, 255, 0.08);
  transform: translateX(3px);
}

.quick-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: rgba(134, 76, 255, 0.08);
  color: #864CFF;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.2s;
}

.quick-card:hover .quick-icon {
  background: rgba(134, 76, 255, 0.14);
}

.quick-info {
  flex: 1;
  min-width: 0;
}

.quick-title {
  font-size: 0.925rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.15rem;
}

.quick-desc {
  font-size: 0.8rem;
  color: #64748b;
}

.quick-arrow {
  color: #94a3b8;
  flex-shrink: 0;
  transition: color 0.2s, transform 0.2s;
}

.quick-card:hover .quick-arrow {
  color: #864CFF;
  transform: translateX(3px);
}

@media (max-width: 600px) {
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
  }

  .info-value {
    text-align: left;
  }
}
</style>
