<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useContractsStore } from '../../../stores/contracts'

const router = useRouter()
const contractsStore = useContractsStore()

onMounted(() => {
  contractsStore.fetchOwnerContracts()
})

const viewContract = (id) => {
  router.push(`/owner/contracts/${id}`)
}

const formatDate = (date) => {
  if (!date) return '-'

  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

const getStatusClass = (status) => {
  return status === 'active'
    ? 'sk-badge-active'
    : 'sk-badge-rejected'
}
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Contracts</h1>
      <p>Manage signed agreements and property contracts.</p>
    </div>

    <div v-if="contractsStore.loading">
      <p>Loading contracts...</p>
    </div>

    <div
      v-else-if="contractsStore.contracts.length === 0"
      class="sk-table-wrap"
    >
      <p>No contracts found.</p>
    </div>

    <div v-else class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Contract ID</th>
            <th>Customer</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Date Signed</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="contract in contractsStore.contracts"
            :key="contract.id"
          >
            <td>
              <strong>#{{ contract.id }}</strong>
            </td>

            <td>
              {{ contract.customer?.name ?? '-' }}
            </td>

            <td>
              {{ contract.unit?.building?.property?.name ?? '-' }}
            </td>

            <td>
              {{ contract.unit?.unit_number ?? '-' }}
            </td>

            <td>
              {{ formatDate(contract.start_date) }}
            </td>

            <td>
              <span
                class="sk-badge"
                :class="getStatusClass(contract.status)"
              >
                {{ contract.status }}
              </span>
            </td>

            <td>
              <button
                class="sk-btn sk-btn-secondary"
                @click="viewContract(contract.id)"
              >
                View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>