import api from './api'

/**
 * Notifications for the authenticated user.
 *
 * One set of endpoints serves both roles — the backend scopes every query to
 * the token holder, so there is no owner/customer split here.
 */

export function getNotifications(params = {}) {
  return api.get('/notifications', { params })
}

export function getUnreadCount() {
  return api.get('/notifications/unread-count')
}

export function markNotificationRead(id) {
  return api.post(`/notifications/${id}/read`)
}

export function markAllNotificationsRead() {
  return api.post('/notifications/read-all')
}
