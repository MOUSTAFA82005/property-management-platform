<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useNotificationsStore } from '../../stores/notifications'
import { useAuthStore } from '../../stores/auth'
import { relativeTime } from '../../utils/format'

/**
 * The notification bell, shared by both portals.
 *
 * One component serves the owner topbar and the public navbar — the API is
 * the same for both roles, and the payload already carries a role-correct
 * link, so nothing here needs to know which portal it is in.
 */
const props = defineProps({
  /** `owner` or `customer`; only used to pick the notifications route. */
  portal: { type: String, default: 'customer' },
})

const route = useRoute()
const router = useRouter()
const store = useNotificationsStore()
const auth = useAuthStore()

const open = ref(false)
const wrap = ref(null)
const trigger = ref(null)
const panel = ref(null)
const actionError = ref('')

const allRoute = computed(() =>
  props.portal === 'owner' ? '/owner/notifications' : '/notifications',
)

/** The dropdown shows the most recent handful; the page shows everything. */
const preview = computed(() => store.notifications.slice(0, 5))

async function openPanel() {
  open.value = true
  actionError.value = ''
  store.acknowledgeArrival()

  await nextTick()
  panel.value?.querySelector('[data-focus-first]')?.focus()

  store.fetchNotifications({ per_page: 5 }).catch(() => {})
}

/**
 * The store rethrows so callers can react. Every handler wired to a DOM
 * event has to catch: an uncaught rejection here surfaces as a Vue
 * "unhandled error during execution of native event handler" and the user
 * gets no idea the action failed.
 */
async function markAll() {
  actionError.value = ''

  try {
    await store.markAllRead()
  } catch {
    actionError.value = 'Could not mark those as read.'
  }
}

async function activateItem(notification) {
  try {
    await activate(notification)
  } catch {
    actionError.value = 'Could not open that notification.'
  }
}

function closePanel({ refocus = false } = {}) {
  if (!open.value) return
  open.value = false
  if (refocus) trigger.value?.focus()
}

const toggle = () => (open.value ? closePanel({ refocus: true }) : openPanel())

/**
 * Reading a notification marks it and, where one exists, follows its link.
 *
 * The mark is awaited before navigating so the read state is durable: fired
 * and forgotten, a full page load immediately afterwards can abort the
 * request in flight and the notification pops back up as unread. A failure
 * must not block the navigation, hence the catch rather than a throw.
 */
async function activate(notification) {
  closePanel()

  await store.markRead(notification.id).catch(() => {})

  if (notification.url) {
    await router.push(notification.url).catch(() => {})
  }
}

function onDocumentPointerDown(event) {
  if (open.value && !wrap.value?.contains(event.target)) closePanel()
}

function onDocumentKeydown(event) {
  if (event.key === 'Escape') closePanel({ refocus: true })
}

if (typeof window !== 'undefined') {
  document.addEventListener('pointerdown', onDocumentPointerDown)
  document.addEventListener('keydown', onDocumentKeydown)
}

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    document.removeEventListener('pointerdown', onDocumentPointerDown)
    document.removeEventListener('keydown', onDocumentKeydown)
  }
  store.stopPolling()
})

watch(() => route.path, () => closePanel())

// Poll only while somebody is signed in; stop the moment they are not.
watch(
  () => auth.isAuthenticated,
  (signedIn) => {
    if (signedIn) {
      store.startPolling()
    } else {
      store.stopPolling()
      store.reset()
    }
  },
  { immediate: true },
)
</script>

<template>
  <div ref="wrap" class="nbell">
    <button
      ref="trigger"
      type="button"
      class="nbell-trigger"
      data-testid="notification-bell"
      :class="{ 'has-arrival': store.hasNewArrival }"
      :aria-expanded="open"
      aria-haspopup="dialog"
      aria-controls="notification-panel"
      :aria-label="store.hasUnread
        ? `Notifications, ${store.unreadCount} unread`
        : 'Notifications'"
      @click="toggle"
      @animationend="store.acknowledgeArrival()"
    >
      <i class="fa-regular fa-bell" aria-hidden="true"></i>
      <span
        v-if="store.hasUnread"
        class="nbell-badge"
        data-testid="notification-badge"
        aria-hidden="true"
      >{{ store.badgeLabel }}</span>
    </button>

    <Transition name="nbell-pop">
      <div
        v-if="open"
        id="notification-panel"
        ref="panel"
        class="nbell-panel"
        role="dialog"
        aria-label="Notifications"
      >
        <header class="nbell-head">
          <h2>Notifications</h2>
          <button
            v-if="store.hasUnread"
            type="button"
            class="nbell-mark"
            data-testid="mark-all-read"
            @click="markAll"
          >
            Mark all read
          </button>
        </header>

        <p v-if="actionError" class="nbell-error" role="alert">{{ actionError }}</p>

        <div v-if="store.loading && preview.length === 0" class="nbell-loading">
          <div v-for="n in 3" :key="'nskel-' + n" class="skel-line nbell-skel"></div>
        </div>

        <p v-else-if="preview.length === 0" class="nbell-empty">
          You have no notifications yet.
        </p>

        <ul v-else class="nbell-list">
          <TransitionGroup name="nbell-item">
            <li v-for="item in preview" :key="item.id">
              <button
                type="button"
                class="nbell-item"
                :class="{ 'is-unread': !item.is_read }"
                data-focus-first
                @click="activateItem(item)"
              >
                <span class="nbell-dot" :class="{ 'is-on': !item.is_read }" aria-hidden="true"></span>
                <span class="nbell-body">
                  <span class="nbell-title">{{ item.title }}</span>
                  <span class="nbell-message">{{ item.message }}</span>
                  <span class="nbell-time">{{ relativeTime(item.created_at) }}</span>
                </span>
              </button>
            </li>
          </TransitionGroup>
        </ul>

        <RouterLink :to="allRoute" class="nbell-all" @click="closePanel()">
          View all notifications
        </RouterLink>
      </div>
    </Transition>
  </div>
</template>
