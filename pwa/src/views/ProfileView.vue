<template>
  <div class="page">
    <header class="app-header">
      <button class="btn-back" @click="router.push('/')">&larr;</button>
      <h1>Профиль</h1>
    </header>

    <div class="profile-card" v-if="profile">
      <div class="profile-row">
        <span class="label">ФИО</span>
        <span class="value">{{ profile.fio || 'Без ФИО' }}</span>
      </div>
      <div class="profile-row">
        <span class="label">Функциональное направление</span>
        <span class="value">{{ profile.functional_direction || 'Не указано' }}</span>
      </div>
      <div class="profile-row">
        <span class="label">Уровень доступа</span>
        <span class="value">{{ profile.access_level_name || accessLevelName }}</span>
      </div>
      <div class="profile-row toggle-row">
        <span class="label">Делиться местоположением</span>
        <label class="toggle">
          <input type="checkbox" :checked="shareLocation" @change="toggleShareLocation" :disabled="locationToggling" />
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>

    <div v-if="loading" class="loading">Загрузка...</div>

    <div class="section">
      <h2>Изменить пароль</h2>
      <form @submit.prevent="changePassword">
        <div class="field">
          <label>Текущий пароль</label>
          <input v-model="oldPwd" type="password" required />
        </div>
        <div class="field">
          <label>Новый пароль</label>
          <input v-model="newPwd" type="password" minlength="6" required />
        </div>
        <div class="field">
          <label>Подтверждение</label>
          <input v-model="confirmPwd" type="password" minlength="6" required />
        </div>
        <div v-if="pwdError" class="error">{{ pwdError }}</div>
        <div v-if="pwdSuccess" class="success">{{ pwdSuccess }}</div>
        <button type="submit" class="btn-primary" :disabled="pwdLoading">
          {{ pwdLoading ? 'Сохранение...' : 'Сохранить' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { api } from '../composables/useApi'
import type { User } from '../types'

const router = useRouter()
const auth = useAuthStore()

const profile = ref<(User & { access_level_name?: string }) | null>(null)
const loading = ref(true)
const oldPwd = ref('')
const newPwd = ref('')
const confirmPwd = ref('')
const pwdError = ref('')
const pwdSuccess = ref('')
const pwdLoading = ref(false)
const shareLocation = ref(true)
const locationToggling = ref(false)

const accessLevelName = computed(() => {
  const names: Record<number, string> = {
    0: 'Гость', 1: 'Пользователь', 2: 'Супервайзер', 3: 'Администратор', 4: 'Суперадминистратор',
  }
  return names[profile.value?.access_level ?? 0] ?? 'Неизвестно'
})

onMounted(async () => {
  try {
    profile.value = await api<User & { access_level_name: string }>('/profile')
    shareLocation.value = profile.value?.share_location !== false
  } catch {
    profile.value = auth.user ? { ...auth.user } : null
    shareLocation.value = auth.user?.share_location !== false
  } finally {
    loading.value = false
  }
})

async function toggleShareLocation() {
  const newValue = !shareLocation.value
  locationToggling.value = true
  try {
    await api('/profile/location', {
      method: 'PUT',
      body: JSON.stringify({ share_location: newValue }),
    })
    shareLocation.value = newValue
    // Update cached user data
    if (auth.user) {
      auth.user.share_location = newValue
      localStorage.setItem('user', JSON.stringify(auth.user))
    }
  } catch {
    // Revert on error — checkbox stays in previous state
  } finally {
    locationToggling.value = false
  }
}

async function changePassword() {
  pwdError.value = ''
  pwdSuccess.value = ''

  if (newPwd.value !== confirmPwd.value) {
    pwdError.value = 'Пароли не совпадают'
    return
  }
  if (newPwd.value.length < 6) {
    pwdError.value = 'Минимум 6 символов'
    return
  }

  pwdLoading.value = true
  try {
    await api('/profile/password', {
      method: 'PUT',
      body: JSON.stringify({ old_password: oldPwd.value, new_password: newPwd.value }),
    })
    pwdSuccess.value = 'Пароль успешно изменен'
    oldPwd.value = ''
    newPwd.value = ''
    confirmPwd.value = ''
  } catch (e: unknown) {
    pwdError.value = (e as { message?: string }).message || 'Ошибка'
  } finally {
    pwdLoading.value = false
  }
}
</script>

<style scoped>
.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.toggle-row .label {
  flex: 1;
}

.toggle {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 26px;
  flex-shrink: 0;
}

.toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--border);
  border-radius: 26px;
  transition: background 0.25s;
}

.toggle-slider::before {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  left: 3px;
  bottom: 3px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.25s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle input:checked + .toggle-slider {
  background: var(--primary);
}

.toggle input:checked + .toggle-slider::before {
  transform: translateX(22px);
}

.toggle input:disabled + .toggle-slider {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
