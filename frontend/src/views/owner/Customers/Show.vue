<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { useCustomersStore } from '../../../stores/customers'
import { formatDate, formatMoney as money } from '../../../utils/format'

const route = useRoute()
const store = useCustomersStore()

const customer = ref(null)
const loading = ref(true)
const error = ref('')

const getCustomer = async () => {
  try {
    loading.value = true
    error.value = ''

    customer.value = await store.fetchCustomer(route.params.id)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load customer.'
  } finally {
    loading.value = false
  }
}

// Contract and payment figures are shown to the cent on this page.
const formatMoney = (amount) => money(amount, { withDecimals: true })

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
                <label for="ro-customers-customer-id">Customer ID</label>
                <input id="ro-customers-customer-id"
                  class="owner-input"
                  :value="`#${customer.id}`"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-customers-full-name">Full name</label>
                <input id="ro-customers-full-name"
                  class="owner-input"
                  :value="customer.name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-customers-email">Email</label>
                <input id="ro-customers-email"
                  class="owner-input"
                  :value="customer.email || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-customers-phone">Phone</label>
                <input id="ro-customers-phone"
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

          <div v-if="customer.contracts?.length" class="owner-list">

            <div
              v-for="contract in customer.contracts"
              :key="contract.id"
              class="owner-list-item"
            >
              <div>
                <b>
                  {{
                    contract.unit?.property_name
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

          </div>

          <div v-else class="owner-empty">
            <h3>No contracts found</h3>
            <p>This customer has no contracts yet.</p>
          </div>
        </div>

      </div>

      <!-- Contract Details -->
      <div
        v-if="customer.contracts?.length"
        class="owner-card"
        
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
                  contract.unit?.property_name
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