/**
 * Shared display helpers.
 *
 * The API returns decimal columns as strings ("14000.00") and float-cast
 * columns as numbers, so everything here coerces before formatting.
 */

export function formatMoney(value, { withDecimals = false } = {}) {
  const amount = Number(value)
  if (value === null || value === undefined || Number.isNaN(amount)) return '—'

  return `EGP ${amount.toLocaleString('en-EG', {
    minimumFractionDigits: withDecimals ? 2 : 0,
    maximumFractionDigits: withDecimals ? 2 : 0,
  })}`
}

export function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '—'

  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

export function formatNumber(value) {
  const number = Number(value)
  return Number.isNaN(number) ? '—' : number.toLocaleString('en-EG')
}

/** Title-case a backend status like `available` for display. */
export function humanStatus(status) {
  if (!status) return '—'
  return String(status).charAt(0).toUpperCase() + String(status).slice(1)
}

/** Map a backend status onto the existing skeleton badge classes. */
export function statusBadgeClass(status) {
  const map = {
    available: 'sk-badge-available',
    occupied: 'sk-badge-occupied',
    reserved: 'sk-badge-pending',
    active: 'sk-badge-active',
    expired: 'sk-badge-rejected',
    terminated: 'sk-badge-rejected',
    paid: 'sk-badge-paid',
    pending: 'sk-badge-pending',
    overdue: 'sk-badge-overdue',
    cancelled: 'sk-badge-rejected',
    approved: 'sk-badge-active',
    rejected: 'sk-badge-rejected',
  }
  return map[String(status || '').toLowerCase()] || 'sk-badge-pending'
}
