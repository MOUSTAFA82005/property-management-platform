import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import * as notificationService from '../services/notifications'
import { extractList, normalizeError } from '../services/pagination'
import { useAuthStore } from './auth'

/** How often the bell asks for a new unread count. */
const POLL_INTERVAL = 60_000

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const error = ref(null)
  const meta = ref(null)

  /** Drives the bell's one-shot nudge when something new lands. */
  const hasNewArrival = ref(false)

  let pollTimer = null

  const hasUnread = computed(() => unreadCount.value > 0)

  /** The count the bell shows; anything past 9 is just "lots". */
  const badgeLabel = computed(() => (unreadCount.value > 9 ? '9+' : String(unreadCount.value)))

  function reset() {
    notifications.value = []
    unreadCount.value = 0
    meta.value = null
    error.value = null
    hasNewArrival.value = false
  }

  async function fetchUnreadCount() {
    const auth = useAuthStore()
    if (!auth.isAuthenticated) return 0

    try {
      const { data } = await notificationService.getUnreadCount()
      const next = Number(data?.count ?? 0)

      // Only a rise counts as an arrival — marking things read must not
      // trigger the bell animation.
      if (next > unreadCount.value) hasNewArrival.value = true

      unreadCount.value = next
      return next
    } catch {
      // A failed poll is not worth surfacing; the next one will retry.
      return unreadCount.value
    }
  }

  async function fetchNotifications(params = {}) {
    const auth = useAuthStore()
    if (!auth.isAuthenticated) return []

    loading.value = true
    error.value = null

    try {
      const extracted = extractList(await notificationService.getNotifications(params))
      notifications.value = extracted.data
      meta.value = extracted.meta
      return extracted.data
    } catch (e) {
      error.value = normalizeError(e).message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function markRead(id) {
    const target = notifications.value.find((item) => item.id === id)
    if (target?.is_read) return target

    // Update in place first so the row settles immediately; the count is
    // corrected from the server response either way.
    if (target) target.is_read = true
    unreadCount.value = Math.max(0, unreadCount.value - 1)

    try {
      const { data } = await notificationService.markNotificationRead(id)
      return data?.data ?? null
    } catch (e) {
      if (target) target.is_read = false
      await fetchUnreadCount()
      throw e
    }
  }

  async function markAllRead() {
    if (unreadCount.value === 0) return

    const previous = notifications.value.map((item) => item.is_read)
    notifications.value.forEach((item) => { item.is_read = true })
    unreadCount.value = 0

    try {
      const { data } = await notificationService.markAllNotificationsRead()
      unreadCount.value = Number(data?.count ?? 0)
    } catch (e) {
      notifications.value.forEach((item, index) => { item.is_read = previous[index] })
      await fetchUnreadCount()
      throw e
    }
  }

  function acknowledgeArrival() {
    hasNewArrival.value = false
  }

  /**
   * Poll while somebody is signed in.
   *
   * One small request a minute, and only the count — the list itself is
   * fetched when the dropdown is actually opened.
   */
  function startPolling() {
    const auth = useAuthStore()
    if (pollTimer || !auth.isAuthenticated) return

    fetchUnreadCount()
    pollTimer = setInterval(fetchUnreadCount, POLL_INTERVAL)
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  return {
    notifications,
    unreadCount,
    loading,
    error,
    meta,
    hasUnread,
    badgeLabel,
    hasNewArrival,

    fetchNotifications,
    fetchUnreadCount,
    markRead,
    markAllRead,
    acknowledgeArrival,
    startPolling,
    stopPolling,
    reset,
  }
})
