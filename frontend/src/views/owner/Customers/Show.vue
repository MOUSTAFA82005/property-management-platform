<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import api from '../../../services/api'

const route = useRoute()

const customer = ref(null)
const loading = ref(true)
const error = ref('')

const getCustomer = async () => {
  try {
    loading.value = true
    error.value = ''

    const response = await api.get(
      `/owner/customers/${route.params.id}`
    )

    customer.value = response.data?.data || response.data
  } catch (err) {
    console.error(err)
    error.value =
      err.response?.data?.message || 'Failed to load customer.'
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
  if (amount === null || amount === undefined) {
    return 'EGP 0'
  }

  return `EGP ${Number(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

const formatStatus = (status) => {
  if (!status) return 'Unknown'

  return status.charAt(0).toUpperCase() + status.slice(1)
}

onMounted(() => {
  getCustomer()
})
</script>

<template>
  <div>
    <RouterLink to="/owner/customers" class="owner-back">
      <i class="fa-solid fa-arrow-left"></i>
      Back to Customers
    </RouterLink>

    <OwnerPageHeader
      :title="customer?.name || 'Customer Details'"
      subtitle="Customer profile and purchase activity."
    />

    <!-- Loading -->
    <div v-if="loading" class="owner-card">
      <div class="owner-form">
        <p>Loading customer...</p>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="owner-card">
      <div class="owner-form">
        <p>{{ error }}</p>
      </div>
    </div>

    <template v-else-if="customer">
      <div class="owner-dashboard-grid">

        <!-- Customer Information -->
        <div class="owner-card">
          <div class="owner-card-head">
            <h2>Customer information</h2>

            <StatusBadge
              :status="formatStatus(customer.status || 'active')"
            />
          </div>

          <div class="owner-form">
            <div class="owner-form-grid">

              <div class="owner-field">
                <label>Customer ID</label>
                <input
                  class="owner-input"
                  :value="`#${customer.id}`"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Full name</label>
                <input
                  class="owner-input"
                  :value="customer.name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Email</label>
                <input
                  class="owner-input"
                  :value="customer.email || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label>Phone</label>
                <input
                  class="owner-input"
                  :value="customer.phone || '-'"
                  readonly
                >
              </div>

            </div>
          </div>
        </div>

        <!-- Contracts / Purchase Activity -->
        <div class="owner-card">
          <div class="owner-card-head">
            <h2>Contracts</h2>
          </div>

          <div class="owner-list">

            <div
              v-if="customer.contracts?.length"
              v-for="contract in customer.contracts"
              :key="contract.id"
              class="owner-list-item"
            >
              <div>
                <b>
                  {{
                    contract.unit?.building?.property?.name
                    || 'Property'
                  }}
                  ·
                  {{
                    contract.unit?.unit_number
                    || 'Unit'
                  }}
                </b>

                <small>
                  Contract #{{ contract.id }}
                  ·
                  {{ formatDate(contract.start_date) }}
                </small>
              </div>

              <StatusBadge
                :status="formatStatus(contract.status)"
              />
            </div>

            <div
              v-else
              class="owner-list-item"
            >
              <div>
                <b>No contracts found</b>
                <small>
                  This customer has no contracts yet.
                </small>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Contract Details -->
      <div
        v-if="customer.contracts?.length"
        class="owner-card"
        style="margin-top: 1.5rem;"
      >
        <div class="owner-card-head">
          <h2>Contract details</h2>
        </div>

        <div
          v-for="contract in customer.contracts"
          :key="contract.id"
          class="owner-list"
        >
          <div class="owner-list-item">
            <div>
              <b>Contract #{{ contract.id }}</b>

              <small>
                {{
                  contract.unit?.building?.property?.name
                  || '-'
                }}
                ·
                {{
                  contract.unit?.building?.name
                  || '-'
                }}
                ·
                {{
                  contract.unit?.unit_number
                  || '-'
                }}
              </small>
            </div>

            <strong>
              {{ formatMoney(contract.monthly_rent) }}
              / month
            </strong>
          </div>

          <div class="owner-list-item">
            <div>
              <b>Contract period</b>

              <small>
                {{ formatDate(contract.start_date) }}
                -
                {{ formatDate(contract.end_date) }}
              </small>
            </div>

            <StatusBadge
              :status="formatStatus(contract.status)"
            />
          </div>

          <div class="owner-list-item">
            <div>
              <b>Security deposit</b>

              <small>
                Contract security deposit
              </small>
            </div>

            <strong>
              {{ formatMoney(contract.security_deposit) }}
            </strong>
          </div>

          <div
            v-if="contract.notes"
            class="owner-list-item"
          >
            <div>
              <b>Notes</b>
              <small>{{ contract.notes }}</small>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>