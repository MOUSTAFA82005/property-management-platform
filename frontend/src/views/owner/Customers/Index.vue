<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import api from '../../../services/api'

const customers = ref([])
const loading = ref(true)
const error = ref('')

const customerCount = computed(() => customers.value.length)

const getCustomers = async () => {
  try {
    loading.value = true
    error.value = ''

    const response = await api.get('/owner/customers')

    customers.value = response.data?.data || []
  } catch (err) {
    console.error(err)
    error.value =
      err.response?.data?.message || 'Failed to load customers.'
  } finally {
    loading.value = false
  }
}

const getCustomerStatus = (customer) => {
  return customer.status
    ? customer.status.charAt(0).toUpperCase() + customer.status.slice(1)
    : 'Active'
}

onMounted(() => {
  getCustomers()
})
</script>

<template>
  <div>
    <OwnerPageHeader
      title="Customers"
      subtitle="Manage prospective and current property buyers."
    />

    <div class="owner-card">
      <div class="owner-card-head">
        <input
          class="owner-search"
          placeholder="Search customers..."
        />

        <span>
          {{ customerCount }}
          {{ customerCount === 1 ? 'customer' : 'customers' }}
        </span>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="owner-form">
        <p>Loading customers...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="owner-form">
        <p>{{ error }}</p>
      </div>

      <!-- Customers -->
      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Requests</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="customer in customers"
              :key="customer.id"
            >
              <td>
                <strong>{{ customer.name }}</strong>
              </td>

              <td>
                {{ customer.email || '-' }}
              </td>

              <td>
                {{ customer.phone || '-' }}
              </td>

              <td>
                {{ customer.purchase_requests_count ?? 0 }}
              </td>

              <td>
                <StatusBadge
                  :status="getCustomerStatus(customer)"
                />
              </td>

              <td>
                <RouterLink
                  :to="`/owner/customers/${customer.id}`"
                  class="owner-btn owner-btn-light"
                >
                  View
                </RouterLink>
              </td>
            </tr>

            <tr v-if="customers.length === 0">
              <td
                colspan="6"
                style="text-align: center"
              >
                No customers found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>