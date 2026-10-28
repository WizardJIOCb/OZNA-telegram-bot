<template>
  <div class="page">
    <header class="app-header">
      <h1>Компания ОЗНА</h1>
      <span class="user-fio">{{ auth.user?.fio || '' }}</span>
    </header>

    <!-- Milestone deviation alerts -->
    <div v-if="tabel.milestoneAlerts.length > 0" class="alert-banner">
      <div class="alert-header">Отставание по проектам</div>
      <div v-for="(a, i) in tabel.milestoneAlerts" :key="i" class="alert-row">
        <span class="alert-project">{{ a.project_code }}</span>
        <span class="alert-type">{{ a.work_type }}</span>
        <span class="alert-eff" :style="{ color: a.efficiency >= 80 ? '#e6a700' : '#d32f2f' }">{{ a.efficiency }}%</span>
      </div>
    </div>

    <div class="menu-grid">
      <router-link to="/profile" class="menu-card">
        <span class="menu-icon">👤</span>
        <span class="menu-label">Профиль</span>
      </router-link>

      <router-link to="/tabel" class="menu-card">
        <span class="menu-icon">📊</span>
        <span class="menu-label">Табель</span>
        <span v-if="offlineCount > 0" class="badge">{{ offlineCount }}</span>
      </router-link>
    </div>

    <button class="btn-logout" @click="handleLogout">Выйти</button>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTabelStore } from '../stores/tabel'

const router = useRouter()
const auth = useAuthStore()
const tabel = useTabelStore()

const offlineCount = computed(() => tabel.getOfflineQueueCount())

onMounted(() => {
  auth.refreshProfile()
  // Preload data for offline use
  tabel.loadActivityTree()
  tabel.loadProjects()
  // Load milestone deviation alerts
  tabel.loadMilestoneAlerts()
})

function handleLogout() {
  auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.alert-banner {
  background: #fff3e0;
  border: 1px solid #ffe0b2;
  border-radius: var(--radius);
  padding: 12px;
  margin-bottom: 16px;
}
.alert-header {
  font-weight: 700;
  font-size: 0.9rem;
  margin-bottom: 8px;
  color: #e65100;
}
.alert-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
  font-size: 0.85rem;
}
.alert-project {
  font-weight: 600;
  flex: 1;
}
.alert-type {
  background: var(--bg);
  border-radius: 4px;
  padding: 1px 6px;
  font-size: 0.75rem;
  color: var(--text-secondary);
}
.alert-eff {
  font-weight: 700;
  min-width: 40px;
  text-align: right;
}
</style>
