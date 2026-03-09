import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api, clearTokens } from '../composables/useApi'
import type { User, AuthTokens } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = computed(() => !!localStorage.getItem('access_token'))

  // Restore user from localStorage on init
  const savedUser = localStorage.getItem('user')
  if (savedUser) {
    try { user.value = JSON.parse(savedUser) } catch { /* skip */ }
  }

  async function login(username: string, password: string) {
    const data = await api<AuthTokens>('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    }, false)

    localStorage.setItem('access_token', data.access_token)
    localStorage.setItem('refresh_token', data.refresh_token)
    localStorage.setItem('user', JSON.stringify(data.user))
    user.value = data.user
  }

  function logout() {
    clearTokens()
    localStorage.removeItem('user')
    user.value = null
  }

  async function refreshProfile() {
    try {
      const profile = await api<User & { access_level_name: string }>('/profile')
      user.value = profile
      localStorage.setItem('user', JSON.stringify(profile))
    } catch { /* ignore - offline fallback to cached */ }
  }

  return { user, isAuthenticated, login, logout, refreshProfile }
})
