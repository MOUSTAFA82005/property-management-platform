<script setup>
import { onMounted, computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { useContractsStore } from '../../../stores/contracts'

const contractsStore = useContractsStore()
const search = ref('')

onMounted(() => {
  contractsStore.fetchOwnerContracts()
})

const formatDate = (date) => {
  if (!date) return '-'

  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

const filteredContracts = computed(() => {
  const query = search.value.toLowerCase().trim()

  if (!query) {
    return contractsStore.contracts
  }

  return contractsStore.contracts.filter((contract) => {
    return (
      String(contract.id ?? '').toLowerCase().includes(query) ||
      String(contract.user?.name ?? '').toLowerCase().includes(query) ||
      String(
        contract.unit?.building?.property?.name ?? ''
      ).toLowerCase().includes(query) ||
      String(contract.unit?.unit_number ?? '').toLowerCase().includes(query) ||
      String(contract.status ?? '').toLowerCase().includes(query)
    )
  })
})
</script>

<template>
  <OwnerPageHeader
    title="Contracts"
    subtitle="Manage signed agreements and property contracts."
    action-text="Create Contract"
    action-to="/owner/contracts/create"
  />

  <div class="owner-card">
    <div class="owner-card-head">
      <input
        v-model="search"
        class="owner-search"
        placeholder="Search contracts..."
      />

      <span>
        {{ filteredContracts.length }}
        {{ filteredContracts.length === 1 ? 'contract' : 'contracts' }}
      </span>
    </div>

    <div v-if="contractsStore.loading" class="owner-table-wrap">
      <p>Loading contracts...</p>
    </div>

    <div
      v-else-if="filteredContracts.length === 0"
      class="owner-table-wrap"
    >
      <p>
        {{ search ? 'No contracts match your search.' : 'No contracts found.' }}
      </p>
    </div>

    <div v-else class="owner-table-wrap">
      <table class="owner-table">
        <thead>
          <tr>
            <th>Contract ID</th>
            <th>Customer</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Date Signed</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="contract in filteredContracts"
            :key="contract.id"
          >
            <td>
              <strong>#{{ contract.id }}</strong>
            </td>

            <td>
              {{ contract.user?.name ?? '-' }}
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
              <StatusBadge
                :status="contract.status"
              />
            </td>

            <td>
              <RouterLink
                :to="`/owner/contracts/${contract.id}`"
                class="owner-btn owner-btn-light"
              >
                View
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>