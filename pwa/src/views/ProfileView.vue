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

const accessLevelName = computed(() => {
  const names: Record<number, string> = {
    0: 'Гость', 1: 'Пользователь', 2: 'Супервайзер', 3: 'Администратор', 4: 'Суперадминистратор',
  }
  return names[profile.value?.access_level ?? 0] ?? 'Неизвестно'
})

onMounted(async () => {
  try {
    profile.value = await api<User & { access_level_name: string }>('/profile')
  } catch {
    profile.value = auth.user ? { ...auth.user } : null
  } finally {
    loading.value = false
  }
})

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
