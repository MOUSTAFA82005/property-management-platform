<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import api from '../../../services/api'

const route = useRoute()

const contract = ref(null)
const loading = ref(true)
const error = ref(null)

const getContract = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.get(`/owner/contracts/${route.params.id}`)
    contract.value = response.data?.data || response.data
  } catch (err) {
    console.error(err)
    error.value =
      err.response?.data?.message || 'Failed to load contract.'
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '-'

  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

const formatMoney = (amount) => {
  if (amount === null || amount === undefined) return 'EGP 0'

  return `EGP ${Number(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

onMounted(() => {
  getContract()
})
</script>

<template>
  <div>
    <RouterLink to="/owner/contracts" class="owner-back">
      <i class="fa-solid fa-arrow-left"></i>
      Back to Contracts
    </RouterLink>

    <OwnerPageHeader
      :title="contract ? `Contract ${contract.id}` : 'Contract Details'"
      subtitle="Signed agreement details and payment schedule."
    />

    <div v-if="loading" class="owner-card">
      <div class="owner-form">
        <p>Loading contract...</p>
      </div>
    </div>

    <div v-else-if="error" class="owner-card">
      <div class="owner-form">
        <p>{{ error }}</p>
      </div>
    </div>

    <template v-else-if="contract">
      <div class="owner-dashboard-grid">
        <!-- Contract Information -->
        <div class="owner-card">
          <div class="owner-card-head">
            <h2>Contract information</h2>

            <StatusBadge
              :status="
                contract.status
                  ? contract.status.charAt(0).toUpperCase() +
                    contract.status.slice(1)
                  : 'Unknown'
              "
            />
          </div>

          <div class="owner-form">
            <div class="owner-form-grid">

              <div class="owner-field">
                <label>Customer</label>
                <input
                  class="owner-input"
                  :value="contract.user?.name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Email</label>
                <input
                  class="owner-input"
                  :value="contract.user?.email || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Property</label>
                <input
                  class="owner-input"
                  :value="
                    contract.unit?.building?.property?.name || '-'
                  "
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Building</label>
                <input
                  class="owner-input"
                  :value="contract.unit?.building?.name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Unit</label>
                <input
                  class="owner-input"
                  :value="contract.unit?.unit_number || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Date signed</label>
                <input
                  class="owner-input"
                  :value="formatDate(contract.start_date)"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>End date</label>
                <input
                  class="owner-input"
                  :value="formatDate(contract.end_date)"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Monthly Rent</label>
                <input
                  class="owner-input"
                  :value="formatMoney(contract.monthly_rent)"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Security Deposit</label>
                <input
                  class="owner-input"
                  :value="formatMoney(contract.security_deposit)"
                  readonly
                >
              </div>

              <div
                v-if="contract.notes"
                class="owner-field"
              >
                <label>Notes</label>
                <input
                  class="owner-input"
                  :value="contract.notes"
                  readonly
                >
              </div>

            </div>
          </div>
        </div>

        <!-- Payment Schedule -->
        <div class="owner-card">
          <div class="owner-card-head">
            <h2>Payment schedule</h2>
          </div>

          <div class="owner-list">

            <div
              v-if="contract.payments?.length"
              v-for="payment in contract.payments"
              :key="payment.id"
              class="owner-list-item"
            >
              <div>
                <b>
                  {{ payment.description || `Payment #${payment.id}` }}
                </b>

                <small>
                  {{ formatDate(payment.due_date) }}
                </small>
              </div>

              <strong>
                {{ formatMoney(payment.amount) }}
              </strong>
            </div>

            <div
              v-else
              class="owner-list-item"
            >
              <div>
                <b>No payment schedule</b>
                <small>No payments have been recorded yet.</small>
              </div>
            </div>

          </div>
        </div>
      </div>
    </template>
  </div>
</template>