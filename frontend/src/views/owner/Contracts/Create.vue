<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import { useContractsStore } from '../../../stores/contracts'
import { useCustomersStore } from '../../../stores/customers'
import { useUnitsStore } from '../../../stores/units'
import { formatMoney } from '../../../utils/format'

const router = useRouter()
const contractsStore = useContractsStore()
const customersStore = useCustomersStore()
const unitsStore = useUnitsStore()

const form = reactive({
  user_id: '',
  unit_id: '',
  start_date: '',
  end_date: '',
  monthly_rent: '',
  security_deposit: '',
  status: 'active',
  notes: '',
})

const errors = ref({})
const generalError = ref('')
const saving = ref(false)

const fieldError = (f) => errors.value[f]?.[0] || ''

// A contract can only be created against a unit that is free.
const availableUnits = computed(() => unitsStore.units.filter((u) => u.status === 'available'))

function onUnitChange() {
  const unit = availableUnits.value.find((u) => u.id === Number(form.unit_id))
  if (unit && !form.monthly_rent) {
    form.monthly_rent = unit.monthly_rent
    form.security_deposit = Number(unit.monthly_rent) * 2
  }
}

onMounted(async () => {
  await Promise.all([
    unitsStore.fetchOwnerUnits({ status: 'available', per_page: 100 }).catch(() => {}),
    customersStore.fetchCustomers({ per_page: 100 }).catch(() => {}),
  ])
})

async function save() {
  saving.value = true
  errors.value = {}
  generalError.value = ''

  try {
    const created = await contractsStore.addContract({
      ...form,
      user_id: Number(form.user_id),
      unit_id: Number(form.unit_id),
      monthly_rent: Number(form.monthly_rent),
      security_deposit: Number(form.security_deposit || 0),
    })
    router.push(created?.id ? `/owner/contracts/${created.id}` : '/owner/contracts')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
    } else {
      generalError.value = e.response?.data?.message || 'Could not create this contract.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <RouterLink to="/owner/contracts" class="owner-back">
    <i class="fa-solid fa-arrow-left"></i> Back to Contracts
  </RouterLink>

  <OwnerPageHeader title="Create Contract" subtitle="Put one of your available units under a lease." />

  <div v-if="generalError" class="sk-alert-error">{{ generalError }}</div>

  <div v-if="availableUnits.length === 0 && !unitsStore.loading" class="empty-box">
    <div class="empty-icon">🚪</div>
    <h3>No available units</h3>
    <p>Every unit you own is currently occupied or reserved.</p>
    <RouterLink to="/owner/units" class="owner-btn owner-btn-primary" style="display: inline-block; margin-top: 1rem;">
      View units
    </RouterLink>
  </div>

  <div v-else-if="customersStore.customers.length === 0 && !customersStore.loading" class="empty-box">
    <div class="empty-icon">👥</div>
    <h3>No customers connected yet</h3>
    <p>Contracts are created for customers who already have a request or contract with you.</p>
    <RouterLink to="/owner/purchase-requests" class="owner-btn owner-btn-primary" style="display: inline-block; margin-top: 1rem;">
      View purchase requests
    </RouterLink>
  </div>

  <div v-else class="owner-card owner-form-card">
    <form class="owner-form" @submit.prevent="save">
      <div class="owner-form-grid">
        <div class="owner-field">
          <label for="c-customer">Customer</label>
          <select id="c-customer" v-model="form.user_id" class="owner-select" :class="{ 'is-invalid': fieldError('user_id') }">
            <option value="">Select a customer</option>
            <option v-for="c in customersStore.customers" :key="c.id" :value="c.id">{{ c.name }} — {{ c.email }}</option>
          </select>
          <small v-if="fieldError('user_id')" class="owner-field-error">{{ fieldError('user_id') }}</small>
        </div>

        <div class="owner-field">
          <label for="c-unit">Unit</label>
          <select id="c-unit" v-model="form.unit_id" class="owner-select" :class="{ 'is-invalid': fieldError('unit_id') }" @change="onUnitChange">
            <option value="">Select an available unit</option>
            <option v-for="u in availableUnits" :key="u.id" :value="u.id">
              {{ u.property_name }} — {{ u.unit_number }} ({{ formatMoney(u.monthly_rent) }})
            </option>
          </select>
          <small v-if="fieldError('unit_id')" class="owner-field-error">{{ fieldError('unit_id') }}</small>
        </div>

        <div class="owner-field">
          <label for="c-start">Start date</label>
          <input id="c-start" v-model="form.start_date" type="date" class="owner-input" :class="{ 'is-invalid': fieldError('start_date') }" />
          <small v-if="fieldError('start_date')" class="owner-field-error">{{ fieldError('start_date') }}</small>
        </div>

        <div class="owner-field">
          <label for="c-end">End date</label>
          <input id="c-end" v-model="form.end_date" type="date" class="owner-input" :class="{ 'is-invalid': fieldError('end_date') }" />
          <small v-if="fieldError('end_date')" class="owner-field-error">{{ fieldError('end_date') }}</small>
        </div>

        <div class="owner-field">
          <label for="c-rent">Monthly rent (EGP)</label>
          <input id="c-rent" v-model="form.monthly_rent" type="number" min="0" step="0.01" class="owner-input" :class="{ 'is-invalid': fieldError('monthly_rent') }" />
          <small v-if="fieldError('monthly_rent')" class="owner-field-error">{{ fieldError('monthly_rent') }}</small>
        </div>

        <div class="owner-field">
          <label for="c-deposit">Security deposit (EGP)</label>
          <input id="c-deposit" v-model="form.security_deposit" type="number" min="0" step="0.01" class="owner-input" :class="{ 'is-invalid': fieldError('security_deposit') }" />
          <small v-if="fieldError('security_deposit')" class="owner-field-error">{{ fieldError('security_deposit') }}</small>
        </div>

        <div class="owner-field">
          <label for="c-status">Status</label>
          <select id="c-status" v-model="form.status" class="owner-select">
            <option value="active">Active</option>
            <option value="expired">Expired</option>
            <option value="terminated">Terminated</option>
          </select>
        </div>

        <div class="owner-field full">
          <label for="c-notes">Notes</label>
          <textarea id="c-notes" v-model="form.notes" class="owner-textarea" placeholder="Contract terms, payment dates..."></textarea>
        </div>
      </div>

      <div class="owner-form-actions">
        <button type="submit" class="owner-btn owner-btn-primary" :disabled="saving">
          {{ saving ? 'Creating...' : 'Create Contract' }}
        </button>
        <RouterLink to="/owner/contracts" class="owner-btn owner-btn-light">Cancel</RouterLink>
      </div>
    </form>
  </div>
</template>
