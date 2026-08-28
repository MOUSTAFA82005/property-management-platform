<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '../stores/notifications'
import { useAuthStore } from '../stores/auth'
import { relativeTime, formatDate } from '../utils/format'

/**
 * The full notification history for whoever is signed in.
 *
 * One view serves both portals: the owner route renders it inside the owner
 * layout and the customer route inside the public shell, but the data and
 * behaviour are identical because notifications belong to a user.
 */
const router = useRouter()
const store = useNotificationsStore()
const auth = useAuthStore()

const filter = ref('all')
const actionError = ref('')

const shown = computed(() =>
  filter.value === 'unread'
    ? store.notifications.filter((item) => !item.is_read)
    : store.notifications,
)

const humanType = (type) => String(type || '').replace(/[._]/g, ' ')

function load(page = 1) {
  return store.fetchNotifications({ page, per_page: 20 }).catch(() => {})
}

/**
 * The store rethrows, so anything wired to a DOM event catches here —
 * otherwise a failed request becomes an unhandled rejection and the user
 * sees nothing at all.
 */
async function activate(notification) {
  actionError.value = ''

  // Awaited, not fired and forgotten: a page load straight afterwards can
  // abort the request and the notification comes back unread.
  await store.markRead(notification.id).catch(() => {})

  if (notification.url) await router.push(notification.url).catch(() => {})
}

async function markOne(id) {
  actionError.value = ''

  try {
    await store.markRead(id)
  } catch {
    actionError.value = 'Could not mark that as read.'
  }
}

async function markAll() {
  actionError.value = ''

  try {
    await store.markAllRead()
  } catch {
    actionError.value = 'Could not mark those as read.'
  }
}

onMounted(() => {
  load()
  store.fetchUnreadCount()
})
</script>

<template>
  <div class="sk-page ps-fade-up">
    <div class="sk-header">
      <h1>Notifications</h1>
      <p>Activity on your {{ auth.isOwner() ? 'properties' : 'account' }}.</p>
    </div>

    <div class="sk-toolbar">
      <div class="sk-filters">
        <button
          class="sk-btn"
          :class="filter === 'all' ? 'sk-btn-primary' : 'sk-btn-secondary'"
          @click="filter = 'all'"
        >
          All
        </button>
        <button
          class="sk-btn"
          :class="filter === 'unread' ? 'sk-btn-primary' : 'sk-btn-secondary'"
          @click="filter = 'unread'"
        >
          Unread<template v-if="store.unreadCount"> ({{ store.unreadCount }})</template>
        </button>
      </div>

      <button
        v-if="store.hasUnread"
        class="sk-btn sk-btn-secondary"
        data-testid="mark-all-read-page"
        @click="markAll"
      >
        Mark all as read
      </button>
    </div>

    <div v-if="actionError" class="sk-alert-error" role="alert">{{ actionError }}</div>

    <!-- Loading -->
    <div v-if="store.loading && store.notifications.length === 0" class="sk-table-wrap" style="padding: 1.5rem;">
      <div v-for="n in 4" :key="'skel-' + n" class="skel-line" style="height: 3rem; margin-bottom: .9rem;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error">
      <h3>Could not load notifications</h3>
      <p>{{ store.error }}</p>
      <button class="sk-btn sk-btn-primary" @click="load()">Retry</button>
    </div>

    <div v-else-if="shown.length === 0" class="empty-box">
      <h3>{{ filter === 'unread' ? 'Nothing unread' : 'No notifications yet' }}</h3>
      <p>
        {{ filter === 'unread'
          ? 'You are all caught up.'
          : 'Activity on your account will show up here.' }}
      </p>
    </div>

    <div v-else class="sk-table-wrap">
      <TransitionGroup tag="ul" name="ps-list" class="nlist">
        <li
          v-for="item in shown"
          :key="item.id"
          class="nlist-row"
          :class="{ 'is-unread': !item.is_read }"
        >
          <span class="nbell-dot" :class="{ 'is-on': !item.is_read }" aria-hidden="true"></span>

          <button class="nlist-main" @click="activate(item)">
            <span class="nlist-title">{{ item.title }}</span>
            <span class="nlist-message">{{ item.message }}</span>
            <span class="nlist-meta">
              <span class="nlist-type">{{ humanType(item.type) }}</span>
              <span>{{ relativeTime(item.created_at) }}</span>
              <span>&middot;</span>
              <span>{{ formatDate(item.created_at) }}</span>
            </span>
          </button>

          <button
            v-if="!item.is_read"
            class="sk-btn sk-btn-secondary"
            @click="markOne(item.id)"
          >
            Mark read
          </button>
        </li>
      </TransitionGroup>
    </div>

    <div v-if="store.meta && store.meta.last_page > 1" class="sk-pagination">
      <button
        class="sk-btn sk-btn-secondary"
        :disabled="store.meta.current_page <= 1"
        @click="load(store.meta.current_page - 1)"
      >
        Previous
      </button>
      <span>Page {{ store.meta.current_page }} of {{ store.meta.last_page }}</span>
      <button
        class="sk-btn sk-btn-secondary"
        :disabled="store.meta.current_page >= store.meta.last_page"
        @click="load(store.meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
