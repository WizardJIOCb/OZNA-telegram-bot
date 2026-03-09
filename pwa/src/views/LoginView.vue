<template>
  <div class="login-page">
    <div class="login-card">
      <h1>Компания ОЗНА</h1>
      <p class="subtitle">Вход в систему</p>

      <form @submit.prevent="handleLogin">
        <div class="field">
          <label for="username">Логин</label>
          <input
            id="username"
            v-model="username"
            type="text"
            autocomplete="username"
            placeholder="Фамилия Имя Отчество"
            required
          />
        </div>

        <div class="field">
          <label for="password">Пароль</label>
          <input
            id="password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            placeholder="Введите пароль"
            required
          />
        </div>

        <div v-if="error" class="error">{{ error }}</div>

        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Вход...' : 'Войти' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(username.value, password.value)
    router.push({ name: 'home' })
  } catch (e: unknown) {
    error.value = (e as { message?: string }).message || 'Ошибка входа'
  } finally {
    loading.value = false
  }
}
</script>
