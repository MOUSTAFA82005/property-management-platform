<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePaymentsStore } from '../../../stores/payments'
import { useContractsStore } from '../../../stores/contracts'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'

const paymentsStore = usePaymentsStore()
const contractsStore = useContractsStore()

// ── State ──────────────────────────────────────────
const searchQuery = ref('')
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const editingPayment = ref(null)
const deletingPayment = ref(null)
const currentPage = ref(1)
let searchTimer = null
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

// The list is paginated server-side, so searching has to be too: filtering
// the rows already in hand would only ever search the page on screen.
const filteredPayments = computed(() => paymentsStore.payments)

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

const METHOD_LABELS = {
  cash: 'Cash',
  bank_transfer: 'Bank Transfer',
  cheque: 'Cheque',
  credit_card: 'Credit Card',
  instapay: 'InstaPay',
  other: 'Other',
}

function methodLabel(payment) {
  if (!payment.payment_method) return '—'
  return METHOD_LABELS[payment.payment_method] || payment.payment_method
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
  await paymentsStore.fetchOwnerPayments({
    page,
    search: searchQuery.value || undefined,
  })
}

// Search is served by the backend; debounce so typing doesn't spam the API.
watch(searchQuery, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => loadPayments(1).catch(() => {}), 350)
})

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
  <OwnerPageHeader
    title="Payments"
    subtitle="Track incoming installments and completed payments."
  >
    <template #actions>
      <button class="owner-btn owner-btn-primary" data-testid="payment-add" @click="openAddModal">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Add Payment
      </button>
    </template>
  </OwnerPageHeader>

  <div v-if="successMessage" class="sk-alert-success" role="status">{{ successMessage }}</div>

  <div class="owner-card">
    <div class="owner-card-head">
      <div class="owner-search-row">
        <label class="sr-only" for="payment-search">Search payments</label>
        <input
          id="payment-search"
          v-model="searchQuery"
          type="search"
          class="owner-search"
          placeholder="Search by customer, unit, reference, status..."
        />
      </div>
      <span v-if="paymentsStore.meta">{{ totalItems }} payments</span>
    </div>

    <!-- Loading -->
    <div v-if="paymentsStore.loading && paymentsStore.payments.length === 0" class="owner-loading" aria-busy="true">
      <div v-for="n in 6" :key="'skeleton-' + n" class="skel-line" style="height: 1.15rem; margin-bottom: .75rem;"></div>
    </div>

    <!-- Error -->
    <EmptyState
      v-else-if="paymentsStore.error && paymentsStore.payments.length === 0"
      tone="error"
      title="Failed to load payments"
      :message="paymentsStore.error"
    >
      <button class="owner-btn owner-btn-primary" @click="retryLoad">Retry</button>
    </EmptyState>

    <!-- Empty -->
    <EmptyState
      v-else-if="!paymentsStore.loading && paymentsStore.payments.length === 0"
      icon="💳"
      title="No payments found"
      message="Record your first payment against one of your contracts."
    >
      <button class="owner-btn owner-btn-primary" @click="openAddModal">Add Payment</button>
    </EmptyState>

    <!-- Data table -->
    <template v-else>
      <div class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th scope="col">Payment ID</th>
              <th scope="col">Customer</th>
              <th scope="col">Contract</th>
              <th scope="col">Unit</th>
              <th scope="col">Amount</th>
              <th scope="col">Due Date</th>
              <th scope="col">Paid Date</th>
              <th scope="col">Payment Method</th>
              <th scope="col">Status</th>
              <th scope="col">Reference</th>
              <th scope="col">Actions</th>
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
              <td>{{ methodLabel(pay) }}</td>
              <td><span class="sk-badge" :class="statusBadgeClass(pay.status)">{{ pay.status }}</span></td>
              <td>{{ pay.reference || '—' }}</td>
              <td>
                <button class="owner-btn owner-btn-light" @click="openEditModal(pay)">Edit</button>
                <button class="owner-btn owner-btn-danger" data-testid="payment-delete" @click="openDeleteModal(pay)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- No search results -->
      <div v-if="filteredPayments.length === 0 && searchQuery" class="owner-empty">
        <h3>No payments match your search</h3>
        <p>Try a different customer, unit or reference.</p>
      </div>
    </template>

    <!-- Pagination -->
    <div v-if="paymentsStore.meta && totalPages > 1" class="sk-pagination">
      <span>Showing page {{ currentPageNum }} of {{ totalPages }} ({{ totalItems }} total)</span>
      <div class="owner-pager">
        <button class="owner-btn owner-btn-light" :disabled="currentPageNum <= 1" @click="goToPage(currentPageNum - 1)">
          Previous
        </button>
        <button
          v-for="page in totalPages"
          :key="'page-' + page"
          class="owner-btn"
          :class="page === currentPageNum ? 'owner-btn-primary' : 'owner-btn-light'"
          :aria-current="page === currentPageNum ? 'page' : undefined"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
        <button class="owner-btn owner-btn-light" :disabled="currentPageNum >= totalPages" @click="goToPage(currentPageNum + 1)">
          Next
        </button>
      </div>
    </div>
  </div>

  <!-- Add / Edit payment -->
  <div v-if="showFormModal" class="owner-modal-backdrop" @click.self="closeFormModal">
    <div class="owner-modal" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
      <div class="owner-modal-head">
        <h3 id="payment-modal-title">{{ isEditing ? 'Edit Payment' : 'Add New Payment' }}</h3>
        <button class="owner-icon-btn" type="button" aria-label="Close" @click="closeFormModal">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>

      <div v-if="apiErrors.general" class="sk-alert-error">{{ apiErrors.general }}</div>

      <form @submit.prevent="submitForm">
        <div class="owner-form-grid">
          <div class="owner-field full">
            <label for="payment-contract-field">Contract *</label>
            <select
              id="payment-contract-field"
              v-model="form.contract_id"
              data-testid="payment-contract"
              class="owner-select"
              :class="{ 'is-invalid': formErrors.contract_id || apiErrors.contract_id }"
            >
              <option value="" disabled>Select a contract</option>
              <option v-for="c in contractsStore.contracts" :key="c.id" :value="c.id">
                CTR-{{ String(c.id).padStart(4, '0') }} — {{ c.user?.name || 'Customer #' + c.user_id }} — Unit {{ c.unit?.unit_number || c.unit_id }}
              </option>
            </select>
            <small v-if="formErrors.contract_id" class="owner-field-error">{{ formErrors.contract_id }}</small>
            <small v-else-if="apiErrors.contract_id" class="owner-field-error">{{ apiErrors.contract_id[0] }}</small>
          </div>

          <div class="owner-field">
            <label for="payment-amount-field">Amount *</label>
            <input
              id="payment-amount-field"
              v-model="form.amount"
              data-testid="payment-amount"
              type="number"
              step="0.01"
              min="0.01"
              class="owner-input"
              :class="{ 'is-invalid': formErrors.amount || apiErrors.amount }"
              placeholder="e.g. 150000"
            />
            <small v-if="formErrors.amount" class="owner-field-error">{{ formErrors.amount }}</small>
            <small v-else-if="apiErrors.amount" class="owner-field-error">{{ apiErrors.amount[0] }}</small>
          </div>

          <div class="owner-field">
            <label for="payment-status-field">Status *</label>
            <select
              id="payment-status-field"
              v-model="form.status"
              data-testid="payment-status"
              class="owner-select"
              :class="{ 'is-invalid': formErrors.status || apiErrors.status }"
            >
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <small v-if="formErrors.status" class="owner-field-error">{{ formErrors.status }}</small>
            <small v-else-if="apiErrors.status" class="owner-field-error">{{ apiErrors.status[0] }}</small>
          </div>

          <div class="owner-field">
            <label for="payment-due-field">Due Date *</label>
            <input
              id="payment-due-field"
              v-model="form.due_date"
              data-testid="payment-due-date"
              type="date"
              class="owner-input"
              :class="{ 'is-invalid': formErrors.due_date || apiErrors.due_date }"
            />
            <small v-if="formErrors.due_date" class="owner-field-error">{{ formErrors.due_date }}</small>
            <small v-else-if="apiErrors.due_date" class="owner-field-error">{{ apiErrors.due_date[0] }}</small>
          </div>

          <div class="owner-field">
            <label for="payment-paid-field">Paid Date</label>
            <input
              id="payment-paid-field"
              v-model="form.paid_date"
              type="date"
              class="owner-input"
              :class="{ 'is-invalid': apiErrors.paid_date }"
            />
            <small v-if="apiErrors.paid_date" class="owner-field-error">{{ apiErrors.paid_date[0] }}</small>
          </div>

          <div class="owner-field">
            <label for="payment-method-field">Payment Method</label>
            <select id="payment-method-field" v-model="form.payment_method" class="owner-select">
              <option value="">Select method</option>
              <option value="cash">Cash</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="credit_card">Credit Card</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="owner-field">
            <label for="payment-reference-field">Reference</label>
            <input
              id="payment-reference-field"
              v-model="form.reference"
              data-testid="payment-reference"
              type="text"
              class="owner-input"
              :class="{ 'is-invalid': apiErrors.reference }"
              placeholder="e.g. REF-001"
            />
            <small v-if="apiErrors.reference" class="owner-field-error">{{ apiErrors.reference[0] }}</small>
          </div>

          <div class="owner-field full">
            <label for="payment-notes-field">Notes</label>
            <textarea
              id="payment-notes-field"
              v-model="form.notes"
              class="owner-textarea"
              placeholder="Additional notes..."
              rows="3"
            ></textarea>
          </div>
        </div>

        <div class="owner-form-actions">
          <button type="submit" data-testid="payment-submit" class="owner-btn owner-btn-primary" :disabled="submitting">
            {{ submitting ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Payment' : 'Create Payment') }}
          </button>
          <button type="button" class="owner-btn owner-btn-light" :disabled="submitting" @click="closeFormModal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete confirmation -->
  <div v-if="showDeleteModal" class="owner-modal-backdrop" @click.self="closeDeleteModal">
    <div class="owner-modal" role="dialog" aria-modal="true" aria-labelledby="payment-delete-title">
      <h3 id="payment-delete-title">Delete Payment</h3>
      <p>
        Are you sure you want to delete payment
        <strong>PAY-{{ String(deletingPayment?.id).padStart(4, '0') }}</strong>? This action cannot be undone.
      </p>

      <div v-if="apiErrors.general" class="sk-alert-error">{{ apiErrors.general }}</div>

      <div class="owner-form-actions">
        <button class="owner-btn owner-btn-danger" data-testid="payment-delete-confirm" :disabled="submitting" @click="confirmDelete">
          {{ submitting ? 'Deleting...' : 'Delete' }}
        </button>
        <button class="owner-btn owner-btn-light" :disabled="submitting" @click="closeDeleteModal">Cancel</button>
      </div>
    </div>
  </div>
</template>
