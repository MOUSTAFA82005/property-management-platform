<script setup>
import { onMounted, computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { useContractsStore } from '../../../stores/contracts'
import { formatDate } from '../../../utils/format'

const contractsStore = useContractsStore()
const search = ref('')

const confirmDelete = ref(null)
const busy = ref(false)
const feedback = ref('')
const actionError = ref('')

onMounted(() => {
  contractsStore.fetchOwnerContracts()
})

const contractRef = (contract) => `CTR-${String(contract.id).padStart(4, '0')}`

async function remove() {
  busy.value = true
  feedback.value = ''
  actionError.value = ''

  const target = confirmDelete.value

  try {
    await contractsStore.removeContract(target.id)
    feedback.value = `${contractRef(target)} was deleted.`
    confirmDelete.value = null
  } catch (e) {
    // A contract with payments against it is refused with a 409 and a
    // message explaining why — surface that rather than a generic failure.
    actionError.value =
      e.response?.data?.message || 'Could not delete that contract.'
    confirmDelete.value = null
  } finally {
    busy.value = false
  }
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
        contract.unit?.property_name ?? ''
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

  <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
  <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

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
            <th>Actions</th>
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
              {{ contract.unit?.property_name ?? '-' }}
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

              <RouterLink
                :to="`/owner/contracts/${contract.id}/edit`"
                class="owner-btn owner-btn-light"
              >
                Edit
              </RouterLink>

              <button
                class="owner-btn owner-btn-danger"
                @click="confirmDelete = contract"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div
    v-if="confirmDelete"
    class="owner-modal-backdrop"
    @click.self="confirmDelete = null"
  >
    <div class="owner-modal">
      <h3>Delete {{ contractRef(confirmDelete) }}?</h3>
      <p>
        This lease for {{ confirmDelete.user?.name ?? 'this customer' }} will be
        removed and the unit released. Contracts with payments recorded against
        them cannot be deleted.
      </p>

      <div class="owner-form-actions">
        <button class="owner-btn owner-btn-danger" :disabled="busy" @click="remove">
          {{ busy ? 'Deleting...' : 'Delete contract' }}
        </button>
        <button class="owner-btn owner-btn-light" @click="confirmDelete = null">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>