<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePaymentsStore } from '../../../stores/payments'
import { useContractsStore } from '../../../stores/contracts'

const paymentsStore = usePaymentsStore()
const contractsStore = useContractsStore()

// ── State ──────────────────────────────────────────
const searchQuery = ref('')
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const editingPayment = ref(null)
const deletingPayment = ref(null)
const currentPage = ref(1)
const submitting = ref(false)
const apiErrors = ref({})
const successMessage = ref('')

// ── Form state ─────────────────────────────────────
const form = ref({
  contract_id: '',
  amount: '',
  due_date: '',
  paid_date: '',
  payment_method: '',
  status: 'pending',
  reference: '',
  notes: '',
})

const formErrors = ref({})

// ── Computed ───────────────────────────────────────
const isEditing = computed(() => editingPayment.value !== null)

const filteredPayments = computed(() => {
  if (!searchQuery.value) return paymentsStore.payments
  const q = searchQuery.value.toLowerCase()
  return paymentsStore.payments.filter((p) => {
    const customer = p.contract?.user?.name?.toLowerCase() || ''
    const unit = p.contract?.unit?.unit_number?.toLowerCase() || ''
    const ref = p.reference?.toLowerCase() || ''
    const status = p.status?.toLowerCase() || ''
    return customer.includes(q) || unit.includes(q) || ref.includes(q) || status.includes(q)
  })
})

const totalPages = computed(() => paymentsStore.meta?.last_page || 1)
const currentPageNum = computed(() => paymentsStore.meta?.current_page || 1)
const totalItems = computed(() => paymentsStore.meta?.total || 0)

// ── Helpers ────────────────────────────────────────
function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatCurrency(amount) {
  if (amount == null || isNaN(amount)) return '—'
  const num = Number(amount)
  return 'EGP ' + num.toLocaleString('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function statusBadgeClass(status) {
  const map = {
    pending: 'sk-badge-pending',
    paid: 'sk-badge-paid',
    overdue: 'sk-badge-overdue',
    cancelled: 'sk-badge-rejected',
  }
  return map[status] || 'sk-badge-pending'
}

function customerName(payment) {
  return payment.contract?.user?.name || '—'
}

function contractLabel(payment) {
  return payment.contract ? `CTR-${String(payment.contract.id).padStart(4, '0')}` : '—'
}

function unitLabel(payment) {
  return payment.contract?.unit?.unit_number || '—'
}

// ── Actions ────────────────────────────────────────
async function loadPayments(page = 1) {
  currentPage.value = page
  await paymentsStore.fetchOwnerPayments({ page })
}

async function loadContracts() {
  await contractsStore.fetchOwnerContracts({ per_page: 100 })
}

async function retryLoad() {
  await loadPayments(currentPage.value)
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  loadPayments(page)
}

// ── Add / Edit modal ──────────────────────────────
function openAddModal() {
  editingPayment.value = null
  form.value = {
    contract_id: '',
    amount: '',
    due_date: '',
    paid_date: '',
    payment_method: '',
    status: 'pending',
    reference: '',
    notes: '',
  }
  formErrors.value = {}
  apiErrors.value = {}
  successMessage.value = ''
  showFormModal.value = true
}

function openEditModal(payment) {
  editingPayment.value = payment
  form.value = {
    contract_id: payment.contract_id || '',
    amount: payment.amount || '',
    due_date: payment.due_date ? payment.due_date.split('T')[0] : '',
    paid_date: payment.paid_date ? payment.paid_date.split('T')[0] : '',
    payment_method: payment.payment_method || '',
    status: payment.status || 'pending',
    reference: payment.reference || '',
    notes: payment.notes || '',
  }
  formErrors.value = {}
  apiErrors.value = {}
  successMessage.value = ''
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
  editingPayment.value = null
}

function validateForm() {
  const errors = {}
  if (!form.value.contract_id) errors.contract_id = 'Contract is required.'
  if (!form.value.amount || Number(form.value.amount) <= 0) errors.amount = 'Amount must be greater than 0.'
  if (!form.value.due_date) errors.due_date = 'Due date is required.'
  if (!form.value.status) errors.status = 'Status is required.'
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

async function submitForm() {
  if (!validateForm()) return

  submitting.value = true
  apiErrors.value = {}
  successMessage.value = ''

  const payload = {
    contract_id: Number(form.value.contract_id),
    amount: Number(form.value.amount),
    due_date: form.value.due_date,
    paid_date: form.value.paid_date || null,
    payment_method: form.value.payment_method || null,
    status: form.value.status,
    reference: form.value.reference || null,
    notes: form.value.notes || null,
  }

  try {
    if (isEditing.value) {
      await paymentsStore.updateOwnerPayment(editingPayment.value.id, payload)
      successMessage.value = 'Payment updated successfully.'
    } else {
      await paymentsStore.createOwnerPayment(payload)
      successMessage.value = 'Payment created successfully.'
    }
    closeFormModal()
    await loadPayments(currentPage.value)
  } catch (e) {
    if (e.response?.status === 422 && e.response?.data?.errors) {
      apiErrors.value = e.response.data.errors
    } else {
      apiErrors.value = { general: e.response?.data?.message || 'An unexpected error occurred.' }
    }
  } finally {
    submitting.value = false
  }
}

// ── Delete modal ───────────────────────────────────
function openDeleteModal(payment) {
  deletingPayment.value = payment
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  deletingPayment.value = null
}

async function confirmDelete() {
  if (!deletingPayment.value) return
  submitting.value = true
  try {
    await paymentsStore.deleteOwnerPayment(deletingPayment.value.id)
    closeDeleteModal()
    successMessage.value = 'Payment deleted successfully.'
    if (filteredPayments.value.length === 0 && currentPage.value > 1) {
      await loadPayments(currentPage.value - 1)
    }
  } catch (e) {
    apiErrors.value = { general: e.response?.data?.message || 'Failed to delete payment.' }
  } finally {
    submitting.value = false
  }
}

// ── Init ───────────────────────────────────────────
onMounted(async () => {
  await Promise.all([loadPayments(), loadContracts()])
})
</script>

<template>
  <div>
    <!-- Header + toolbar -->
    <div class="sk-toolbar">
      <div class="sk-header" style="border: none; padding: 0; margin: 0;">
        <h1 style="margin-bottom: 0;">Payments</h1>
        <p style="margin-top: 0.25rem;">Track incoming installments and completed payments.</p>
      </div>
      <button class="sk-btn sk-btn-primary" @click="openAddModal">+ Add Payment</button>
    </div>

    <!-- Search -->
    <div style="margin-bottom: 1rem;">
      <input
        v-model="searchQuery"
        type="text"
        class="sk-search"
        placeholder="Search by customer, unit, reference, status..."
        style="width: 100%; max-width: 360px;"
      />
    </div>

    <!-- Success message -->
    <div v-if="successMessage" style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; font-size: 0.875rem; font-weight: 500;">
      {{ successMessage }}
    </div>

    <!-- Loading state -->
    <div v-if="paymentsStore.loading && paymentsStore.payments.length === 0" class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Customer</th>
            <th>Contract</th>
            <th>Unit</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Paid Date</th>
            <th>Payment Method</th>
            <th>Status</th>
            <th>Reference</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="n in 5" :key="'skeleton-' + n">
            <td><div style="height: 14px; width: 60px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 100px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 80px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 50px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 90px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 90px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 90px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 80px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 60px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 70px; background: #e5e7eb; border-radius: 4px;"></div></td>
            <td><div style="height: 14px; width: 80px; background: #e5e7eb; border-radius: 4px;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Error state -->
    <div v-else-if="paymentsStore.error && paymentsStore.payments.length === 0" style="text-align: center; padding: 3rem 1rem;">
      <div style="font-size: 1.1rem; font-weight: 600; color: #dc2626; margin-bottom: 0.5rem;">Failed to load payments</div>
      <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem;">{{ paymentsStore.error }}</div>
      <button class="sk-btn sk-btn-primary" @click="retryLoad">Retry</button>
    </div>

    <!-- Empty state -->
    <div v-else-if="!paymentsStore.loading && paymentsStore.payments.length === 0" class="sk-table-wrap">
      <div style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
        <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.25rem;">No payments found.</div>
        <div style="font-size: 0.875rem;">Get started by adding your first payment.</div>
      </div>
    </div>

    <!-- Data table -->
    <div v-else class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Customer</th>
            <th>Contract</th>
            <th>Unit</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Paid Date</th>
            <th>Payment Method</th>
            <th>Status</th>
            <th>Reference</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pay in filteredPayments" :key="pay.id">
            <td><strong>PAY-{{ String(pay.id).padStart(4, '0') }}</strong></td>
            <td>{{ customerName(pay) }}</td>
            <td>{{ contractLabel(pay) }}</td>
            <td>{{ unitLabel(pay) }}</td>
            <td>{{ formatCurrency(pay.amount) }}</td>
            <td>{{ formatDate(pay.due_date) }}</td>
            <td>{{ formatDate(pay.paid_date) }}</td>
            <td>{{ pay.payment_method || '—' }}</td>
            <td>
              <span class="sk-badge" :class="statusBadgeClass(pay.status)">{{ pay.status }}</span>
            </td>
            <td>{{ pay.reference || '—' }}</td>
            <td>
              <div style="display: flex; gap: 0.25rem;">
                <button class="sk-btn sk-btn-secondary" @click="openEditModal(pay)">Edit</button>
                <button class="sk-btn sk-btn-danger" @click="openDeleteModal(pay)">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- No search results -->
      <div v-if="filteredPayments.length === 0 && searchQuery" style="text-align: center; padding: 2rem 1rem; color: #6b7280;">
        <div style="font-size: 0.925rem; font-weight: 600;">No payments match your search.</div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="paymentsStore.meta && totalPages > 1" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1rem; flex-wrap: wrap; gap: 0.5rem;">
      <div style="font-size: 0.825rem; color: #6b7280;">
        Showing page {{ currentPageNum }} of {{ totalPages }} ({{ totalItems }} total)
      </div>
      <div style="display: flex; gap: 0.25rem;">
        <button
          class="sk-btn sk-btn-secondary"
          :disabled="currentPageNum <= 1"
          @click="goToPage(currentPageNum - 1)"
        >
          &laquo; Previous
        </button>
        <button
          v-for="page in totalPages"
          :key="'page-' + page"
          class="sk-btn"
          :class="page === currentPageNum ? 'sk-btn-primary' : 'sk-btn-secondary'"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
        <button
          class="sk-btn sk-btn-secondary"
          :disabled="currentPageNum >= totalPages"
          @click="goToPage(currentPageNum + 1)"
        >
          Next &raquo;
        </button>
      </div>
    </div>

    <!-- ── Add / Edit Payment Modal ───────────────── -->
    <div v-if="showFormModal" style="position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;">
      <!-- Backdrop -->
      <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.4);" @click="closeFormModal"></div>

      <!-- Modal -->
      <div style="position: relative; background: #fff; border-radius: 12px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <div style="padding: 1.5rem 1.5rem 0;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #111827;">{{ isEditing ? 'Edit Payment' : 'Add New Payment' }}</h2>
            <button @click="closeFormModal" style="background: none; border: none; font-size: 1.5rem; color: #6b7280; cursor: pointer; line-height: 1;">&times;</button>
          </div>

          <!-- API general error -->
          <div v-if="apiErrors.general" style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; font-size: 0.85rem;">
            {{ apiErrors.general }}
          </div>
        </div>

        <form @submit.prevent="submitForm" style="padding: 0 1.5rem 1.5rem;">
          <!-- Contract -->
          <div class="sk-form-group">
            <label class="sk-form-label">Contract *</label>
            <select v-model="form.contract_id" class="sk-form-select" :class="{ 'is-invalid': formErrors.contract_id || apiErrors.contract_id }">
              <option value="" disabled>Select a contract</option>
              <option v-for="c in contractsStore.contracts" :key="c.id" :value="c.id">
                CTR-{{ String(c.id).padStart(4, '0') }} — {{ c.user?.name || 'Customer #' + c.user_id }} — Unit {{ c.unit?.unit_number || c.unit_id }}
              </option>
            </select>
            <div v-if="formErrors.contract_id" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ formErrors.contract_id }}</div>
            <div v-else-if="apiErrors.contract_id" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ apiErrors.contract_id[0] }}</div>
          </div>

          <!-- Amount -->
          <div class="sk-form-group">
            <label class="sk-form-label">Amount *</label>
            <input v-model="form.amount" type="number" step="0.01" min="0.01" class="sk-form-input" :class="{ 'is-invalid': formErrors.amount || apiErrors.amount }" placeholder="e.g. 150000" />
            <div v-if="formErrors.amount" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ formErrors.amount }}</div>
            <div v-else-if="apiErrors.amount" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ apiErrors.amount[0] }}</div>
          </div>

          <!-- Due Date -->
          <div class="sk-form-group">
            <label class="sk-form-label">Due Date *</label>
            <input v-model="form.due_date" type="date" class="sk-form-input" :class="{ 'is-invalid': formErrors.due_date || apiErrors.due_date }" />
            <div v-if="formErrors.due_date" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ formErrors.due_date }}</div>
            <div v-else-if="apiErrors.due_date" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ apiErrors.due_date[0] }}</div>
          </div>

          <!-- Paid Date -->
          <div class="sk-form-group">
            <label class="sk-form-label">Paid Date</label>
            <input v-model="form.paid_date" type="date" class="sk-form-input" :class="{ 'is-invalid': apiErrors.paid_date }" />
            <div v-if="apiErrors.paid_date" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ apiErrors.paid_date[0] }}</div>
          </div>

          <!-- Payment Method -->
          <div class="sk-form-group">
            <label class="sk-form-label">Payment Method</label>
            <select v-model="form.payment_method" class="sk-form-select">
              <option value="">Select method</option>
              <option value="cash">Cash</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="credit_card">Credit Card</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Status -->
          <div class="sk-form-group">
            <label class="sk-form-label">Status *</label>
            <select v-model="form.status" class="sk-form-select" :class="{ 'is-invalid': formErrors.status || apiErrors.status }">
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <div v-if="formErrors.status" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ formErrors.status }}</div>
            <div v-else-if="apiErrors.status" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ apiErrors.status[0] }}</div>
          </div>

          <!-- Reference -->
          <div class="sk-form-group">
            <label class="sk-form-label">Reference</label>
            <input v-model="form.reference" type="text" class="sk-form-input" :class="{ 'is-invalid': apiErrors.reference }" placeholder="e.g. REF-001" />
            <div v-if="apiErrors.reference" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ apiErrors.reference[0] }}</div>
          </div>

          <!-- Notes -->
          <div class="sk-form-group">
            <label class="sk-form-label">Notes</label>
            <textarea v-model="form.notes" class="sk-form-textarea" placeholder="Additional notes..." rows="3"></textarea>
          </div>

          <!-- Actions -->
          <div class="sk-form-actions" style="padding-top: 0.5rem;">
            <button type="submit" class="sk-btn sk-btn-primary" :disabled="submitting">
              {{ submitting ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Payment' : 'Create Payment') }}
            </button>
            <button type="button" class="sk-btn sk-btn-secondary" @click="closeFormModal" :disabled="submitting">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Delete Confirmation Modal ───────────────── -->
    <div v-if="showDeleteModal" style="position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;">
      <!-- Backdrop -->
      <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.4);" @click="closeDeleteModal"></div>

      <!-- Modal -->
      <div style="position: relative; background: #fff; border-radius: 12px; width: 100%; max-width: 420px; padding: 1.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Delete Payment</h2>
        <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.25rem;">
          Are you sure you want to delete payment <strong>PAY-{{ String(deletingPayment?.id).padStart(4, '0') }}</strong>? This action cannot be undone.
        </p>

        <!-- API error -->
        <div v-if="apiErrors.general" style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; font-size: 0.85rem;">
          {{ apiErrors.general }}
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
          <button class="sk-btn sk-btn-secondary" @click="closeDeleteModal" :disabled="submitting">Cancel</button>
          <button class="sk-btn sk-btn-danger" @click="confirmDelete" :disabled="submitting">
            {{ submitting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
