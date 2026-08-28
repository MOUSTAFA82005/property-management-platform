<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { useContractsStore } from '../../../stores/contracts'
import { formatDate, formatMoney as money } from '../../../utils/format'

const route = useRoute()
const router = useRouter()
const contractsStore = useContractsStore()

const contract = ref(null)
const loading = ref(true)
const error = ref(null)

const confirmDelete = ref(false)
const deleting = ref(false)
const actionError = ref('')

// Contract money is shown to the cent on the detail page.
const formatMoney = (amount) => money(amount, { withDecimals: true })

const getContract = async () => {
  loading.value = true
  error.value = null

  try {
    contract.value = await contractsStore.fetchOwnerContract(route.params.id)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load contract.'
  } finally {
    loading.value = false
  }
}

const remove = async () => {
  deleting.value = true
  actionError.value = ''

  try {
    await contractsStore.removeContract(contract.value.id)
    router.push('/owner/contracts')
  } catch (err) {
    // A contract with payments is refused with a 409 and an explanation.
    actionError.value =
      err.response?.data?.message || 'Could not delete this contract.'
    confirmDelete.value = false
  } finally {
    deleting.value = false
  }
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
      :title="contract ? `Contract CTR-${String(contract.id).padStart(4, '0')}` : 'Contract Details'"
      subtitle="Signed agreement details and payment schedule."
    >
      <template v-if="contract" #actions>
        <RouterLink
          :to="`/owner/contracts/${contract.id}/edit`"
          class="owner-btn owner-btn-light"
        >
          Edit
        </RouterLink>
        <button class="owner-btn owner-btn-danger" @click="confirmDelete = true">
          Delete
        </button>
      </template>
    </OwnerPageHeader>

    <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

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
                <label for="ro-contracts-customer">Customer</label>
                <input id="ro-contracts-customer"
                  class="owner-input"
                  :value="contract.user?.name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-email">Email</label>
                <input id="ro-contracts-email"
                  class="owner-input"
                  :value="contract.user?.email || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-property">Property</label>
                <input id="ro-contracts-property"
                  class="owner-input"
                  :value="contract.unit?.property_name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-building">Building</label>
                <input id="ro-contracts-building"
                  class="owner-input"
                  :value="contract.unit?.building?.name || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-unit">Unit</label>
                <input id="ro-contracts-unit"
                  class="owner-input"
                  :value="contract.unit?.unit_number || '-'"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-date-signed">Date signed</label>
                <input id="ro-contracts-date-signed"
                  class="owner-input"
                  :value="formatDate(contract.start_date)"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-end-date">End date</label>
                <input id="ro-contracts-end-date"
                  class="owner-input"
                  :value="formatDate(contract.end_date)"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-monthly-rent">Monthly Rent</label>
                <input id="ro-contracts-monthly-rent"
                  class="owner-input"
                  :value="formatMoney(contract.monthly_rent)"
                  readonly
                >
              </div>

              <div class="owner-field">
                <label for="ro-contracts-security-deposit">Security Deposit</label>
                <input id="ro-contracts-security-deposit"
                  class="owner-input"
                  :value="formatMoney(contract.security_deposit)"
                  readonly
                >
              </div>

              <div
                v-if="contract.notes"
                class="owner-field full"
              >
                <label for="ro-contracts-notes">Notes</label>
                <textarea id="ro-contracts-notes" class="owner-textarea" readonly>{{ contract.notes }}</textarea>
              </div>

            </div>
          </div>
        </div>

        <!-- Payment Schedule -->
        <div class="owner-card">
          <div class="owner-card-head">
            <h2>Payment schedule</h2>
          </div>

          <div v-if="contract.payments?.length" class="owner-list">
            <div
              v-for="payment in contract.payments"
              :key="payment.id"
              class="owner-list-item"
            >
              <div>
                <b>{{ payment.reference || `Payment #${payment.id}` }}</b>
                <small>Due {{ formatDate(payment.due_date) }}</small>
              </div>

              <div class="owner-list-end">
                <StatusBadge :status="payment.status.charAt(0).toUpperCase() + payment.status.slice(1)" />
                <span class="owner-money">{{ formatMoney(payment.amount) }}</span>
              </div>
            </div>
          </div>

          <div v-else class="owner-empty">
            <h3>No payment schedule</h3>
            <p>No payments have been recorded against this contract yet.</p>
          </div>
        </div>
      </div>
    </template>

    <div
      v-if="confirmDelete"
      class="owner-modal-backdrop"
      @click.self="confirmDelete = false"
    >
      <div class="owner-modal">
        <h3>Delete this contract?</h3>
        <p>
          This lease for {{ contract?.user?.name ?? 'this customer' }} will be
          removed and the unit released. Contracts with payments recorded
          against them cannot be deleted.
        </p>

        <div class="owner-form-actions">
          <button class="owner-btn owner-btn-danger" :disabled="deleting" @click="remove">
            {{ deleting ? 'Deleting...' : 'Delete contract' }}
          </button>
          <button class="owner-btn owner-btn-light" @click="confirmDelete = false">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>