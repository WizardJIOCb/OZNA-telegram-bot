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

    <router-link to="/map" class="btn-map">Карта проектов</router-link>

    <!-- Metrics - supervisor+ only -->
    <router-link
      v-if="isSupervisorOrAbove"
      to="/metrics"
      class="btn-metrics"
    >
      Метрика
    </router-link>

    <!-- Dashboard exports - superadmin only -->
    <router-link
      v-if="isSuperAdmin"
      to="/dashboard"
      class="btn-dashboard"
    >
      Dashboards
    </router-link>

    <!-- Admin user password reset - superadmin only -->
    <router-link
      v-if="isSuperAdmin"
      to="/admin/users"
      class="btn-admin-users"
    >
      Сброс пароля пользователя
    </router-link>

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
const isSuperAdmin = computed(() => (auth.user?.access_level ?? 0) >= 4)
const isSupervisorOrAbove = computed(() => (auth.user?.access_level ?? 0) >= 2)

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
.btn-map {
  display: block;
  width: 100%;
  padding: 14px;
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  font-size: 1rem;
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  cursor: pointer;
  margin-top: 12px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.btn-map:hover, .btn-map:active {
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(21,101,192,0.15);
}
.btn-metrics {
  display: block;
  width: 100%;
  padding: 14px;
  background: #e0f2f1;
  color: #00695c;
  border: 1px solid #b2dfdb;
  border-radius: var(--radius);
  font-size: 1rem;
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  cursor: pointer;
  margin-top: 8px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.btn-metrics:hover, .btn-metrics:active {
  border-color: #00897b;
  box-shadow: 0 2px 8px rgba(0,137,123,0.15);
}
.btn-dashboard {
  display: block;
  width: 100%;
  padding: 14px;
  background: #e8eaf6;
  color: #283593;
  border: 1px solid #c5cae9;
  border-radius: var(--radius);
  font-size: 1rem;
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  cursor: pointer;
  margin-top: 8px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.btn-dashboard:hover, .btn-dashboard:active {
  border-color: #3f51b5;
  box-shadow: 0 2px 8px rgba(63,81,181,0.15);
}
.btn-admin-users {
  display: block;
  width: 100%;
  padding: 14px;
  background: #fce4ec;
  color: #b71c1c;
  border: 1px solid #f8bbd0;
  border-radius: var(--radius);
  font-size: 1rem;
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  cursor: pointer;
  margin-top: 8px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.btn-admin-users:hover, .btn-admin-users:active {
  border-color: #e91e63;
  box-shadow: 0 2px 8px rgba(233,30,99,0.15);
}
</style>
