<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { usePaymentsStore } from '../../stores/payments'
import CustomerDashboardLayout from '../../components/customer/CustomerDashboardLayout.vue'

const authStore     = useAuthStore()
const paymentsStore = usePaymentsStore()

const user = computed(() => authStore.user)

onMounted(async () => {
  try {
    await paymentsStore.fetchCustomerPayments()
  } catch {
    // non-critical — payment count stays minimal if request fails
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
      </div>

      <div class="info-card">
        <div class="info-row">
          <span class="info-label">Full Name</span>
          <span class="info-value">{{ user?.name || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Email Address</span>
          <span class="info-value">{{ user?.email || '—' }}</span>
        </div>
        <div class="info-row" v-if="user?.phone">
          <span class="info-label">Phone</span>
          <span class="info-value">{{ user.phone }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Account Status</span>
          <span class="info-value">
            <span class="status-badge status-active">Active</span>
          </span>
        </div>
      </div>
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
