<template>
  <div class="page">
    <header class="app-header">
      <button class="btn-back" @click="$router.push('/')">&larr;</button>
      <h1>Сброс пароля</h1>
    </header>

    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="successMsg" class="success-banner">
      <div class="success-title">Пароль сброшен</div>
      <div class="cred-row">
        <span class="cred-label">Логин:</span>
        <code class="cred-value">{{ successData.username }}</code>
      </div>
      <div class="cred-row">
        <span class="cred-label">Пароль:</span>
        <code class="cred-value">{{ successData.password }}</code>
      </div>
      <div class="cred-warning">Передайте данные пользователю. Пароль показывается только один раз.</div>
      <button class="btn-secondary" @click="clearSuccess">Закрыть</button>
    </div>

    <!-- Search -->
    <div v-if="!selectedUser" class="field" style="margin-bottom: 12px;">
      <input
        v-model="search"
        class="search-input"
        type="text"
        placeholder="Поиск по ФИО..."
      />
    </div>

    <!-- User list -->
    <div v-if="loading" class="loading">Загрузка...</div>

    <div v-else-if="!selectedUser" class="option-list">
      <div v-if="filteredUsers.length === 0" class="empty">Пользователи не найдены</div>
      <button
        v-for="u in filteredUsers"
        :key="u.id"
        class="btn-option"
        @click="selectUser(u)"
      >
        <div>
          <div class="user-name">{{ u.fio }}</div>
          <div class="user-meta">{{ u.functional_direction || 'Без направления' }}</div>
        </div>
        <span class="chevron">&rsaquo;</span>
      </button>
    </div>

    <!-- User detail / reset -->
    <div v-if="selectedUser && !successMsg" class="user-detail">
      <div class="profile-card">
        <div class="profile-row">
          <span class="label">ФИО</span>
          <span class="value">{{ selectedUser.fio }}</span>
        </div>
        <div class="profile-row">
          <span class="label">Логин</span>
          <span class="value">{{ selectedUser.username }}</span>
        </div>
        <div class="profile-row">
          <span class="label">Направление</span>
          <span class="value">{{ selectedUser.functional_direction || 'Не указано' }}</span>
        </div>
      </div>

      <div class="section">
        <h2>Способ сброса</h2>

        <button
          class="btn-primary"
          :disabled="resetting"
          @click="resetWithGenerated"
        >
          {{ resetting ? 'Сброс...' : 'Сгенерировать временный пароль' }}
        </button>

        <div class="divider">или</div>

        <form @submit.prevent="resetWithCustom">
          <div class="field">
            <label>Ввести пароль вручную</label>
            <input v-model="customPassword" type="text" minlength="6" placeholder="Минимум 6 символов" />
          </div>
          <button type="submit" class="btn-primary" :disabled="resetting || customPassword.length < 6">
            {{ resetting ? 'Сброс...' : 'Установить пароль' }}
          </button>
        </form>

        <button class="btn-secondary" @click="selectedUser = null" style="margin-top: 12px;">
          &larr; К списку
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { api } from '../composables/useApi'

interface PwaUser {
  id: number
  username: string
  fio: string
  linked_telegram_id: number | null
  access_level: number
  functional_direction: string
}

interface ResetResult {
  success: boolean
  username: string
  fio: string
  password: string
  message: string
}

const users = ref<PwaUser[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const selectedUser = ref<PwaUser | null>(null)
const customPassword = ref('')
const resetting = ref(false)
const successMsg = ref(false)
const successData = ref<{ username: string; password: string }>({ username: '', password: '' })

const filteredUsers = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return users.value
  return users.value.filter(
    u => u.fio.toLowerCase().includes(q) || u.username.toLowerCase().includes(q)
  )
})

onMounted(async () => {
  await loadUsers()
})

async function loadUsers() {
  loading.value = true
  error.value = ''
  try {
    const data = await api<{ users: PwaUser[] }>('/admin/users')
    users.value = data.users
  } catch (e: unknown) {
    const msg = (e as { message?: string }).message || 'Ошибка загрузки'
    if (msg.includes('Insufficient access')) {
      error.value = 'Недостаточно прав доступа'
    } else {
      error.value = msg
    }
  } finally {
    loading.value = false
  }
}

function selectUser(u: PwaUser) {
  selectedUser.value = u
  customPassword.value = ''
  error.value = ''
  successMsg.value = false
}

async function resetWithGenerated() {
  if (!selectedUser.value) return
  await doReset(selectedUser.value.id, '')
}

async function resetWithCustom() {
  if (!selectedUser.value) return
  if (customPassword.value.length < 6) {
    error.value = 'Минимум 6 символов'
    return
  }
  await doReset(selectedUser.value.id, customPassword.value)
}

async function doReset(userId: number, password: string) {
  resetting.value = true
  error.value = ''
  try {
    const body: Record<string, string> = {}
    if (password) body.new_password = password
    const result = await api<ResetResult>(`/admin/users/${userId}/reset-password`, {
      method: 'PUT',
      body: JSON.stringify(body),
    })
    successData.value = { username: result.username, password: result.password }
    successMsg.value = true
    customPassword.value = ''
  } catch (e: unknown) {
    error.value = (e as { message?: string }).message || 'Ошибка сброса пароля'
  } finally {
    resetting.value = false
  }
}

function clearSuccess() {
  successMsg.value = false
  selectedUser.value = null
}
</script>

<style scoped>
.user-name {
  font-weight: 500;
  font-size: 0.9375rem;
}

.user-meta {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin-top: 2px;
}

.user-detail {
  margin-top: 8px;
}

.divider {
  text-align: center;
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 16px 0;
  position: relative;
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 35%;
  height: 1px;
  background: var(--border);
}

.divider::before { left: 0; }
.divider::after { right: 0; }

.success-banner {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  border-radius: var(--radius);
  padding: 16px;
  margin-bottom: 16px;
}

.success-title {
  font-weight: 600;
  color: var(--success);
  margin-bottom: 12px;
  font-size: 1rem;
}

.cred-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
}

.cred-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  min-width: 60px;
}

.cred-value {
  font-size: 1rem;
  font-weight: 600;
  background: #fff;
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid var(--border);
  user-select: all;
}

.cred-warning {
  font-size: 0.8125rem;
  color: #e65100;
  margin: 12px 0;
  line-height: 1.4;
}
</style>
