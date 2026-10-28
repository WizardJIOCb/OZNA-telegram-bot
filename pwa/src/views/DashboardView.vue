<template>
  <div class="page">
    <header class="app-header">
      <button class="btn-back" @click="$router.push('/')">&#8592;</button>
      <h1>Дашборды</h1>
    </header>

    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="successMsg" class="success">{{ successMsg }}</div>

    <div class="section">
      <h2>Выгрузки для дашбордов</h2>

      <div v-if="loading" class="loading">Загрузка списка...</div>

      <div v-else class="export-list">
        <div
          v-for="exp in exports"
          :key="exp.id"
          class="export-card"
        >
          <div class="export-info">
            <div class="export-name">{{ exp.name }}</div>
            <div class="export-desc">{{ exp.description }}</div>
          </div>
          <button
            class="btn-export"
            :disabled="downloading === exp.id"
            @click="downloadExport(exp.id, exp.name)"
          >
            <span v-if="downloading === exp.id">Формируется...</span>
            <span v-else>Скачать</span>
          </button>
        </div>

        <div v-if="!loading && exports.length === 0" class="empty">
          Нет доступных выгрузок
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface DashboardExport {
  id: string
  name: string
  description: string
}

const exports = ref<DashboardExport[]>([])
const loading = ref(true)
const downloading = ref<string | null>(null)
const error = ref('')
const successMsg = ref('')

const API_BASE = import.meta.env.PROD ? '/tg/ozna/api' : '/tg/ozna/api'

function getAccessToken(): string | null {
  return localStorage.getItem('access_token')
}

async function loadExports() {
  loading.value = true
  error.value = ''
  try {
    const token = getAccessToken()
    const res = await fetch(`${API_BASE}/dashboard/exports`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (!res.ok) {
      if (res.status === 403) {
        error.value = 'Недостаточно прав доступа'
        return
      }
      throw new Error(`HTTP ${res.status}`)
    }
    const data = await res.json()
    if (data.exports) {
      exports.value = data.exports
    }
  } catch (e: unknown) {
    error.value = 'Ошибка загрузки списка выгрузок'
  } finally {
    loading.value = false
  }
}

async function downloadExport(exportId: string, exportName: string) {
  downloading.value = exportId
  error.value = ''
  successMsg.value = ''

  try {
    const token = getAccessToken()
    const res = await fetch(`${API_BASE}/dashboard/exports/${exportId}`, {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (!res.ok) {
      if (res.status === 403) {
        error.value = 'Недостаточно прав доступа'
        return
      }
      // Try to parse error JSON
      const contentType = res.headers.get('content-type') || ''
      if (contentType.includes('application/json')) {
        const errData = await res.json()
        error.value = errData.error || `Ошибка ${res.status}`
      } else {
        error.value = `Ошибка сервера: ${res.status}`
      }
      return
    }

    // Check if response is JSON (error) or file
    const contentType = res.headers.get('content-type') || ''
    if (contentType.includes('application/json')) {
      const data = await res.json()
      if (data.error) {
        error.value = data.error
        return
      }
    }

    // Download file
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url

    // Get filename from Content-Disposition or use default
    const disposition = res.headers.get('content-disposition')
    let filename = `${exportName}_${new Date().toISOString().slice(0, 10)}.xlsx`
    if (disposition) {
      const match = disposition.match(/filename[^;=\n]*=(["']?)([^"';\n]*)\1/)
      if (match && match[2]) {
        filename = decodeURIComponent(match[2])
      }
    }

    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    successMsg.value = `Файл "${filename}" скачан`
  } catch (e: unknown) {
    error.value = 'Ошибка при скачивании файла'
  } finally {
    downloading.value = null
  }
}

onMounted(() => {
  loadExports()
})
</script>

<style scoped>
.export-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.export-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.export-name {
  font-size: 1rem;
  font-weight: 600;
}

.export-desc {
  font-size: 0.85rem;
  color: var(--text-secondary);
  line-height: 1.4;
}

.btn-export {
  display: block;
  width: 100%;
  padding: 12px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}

.btn-export:hover {
  background: var(--primary-dark);
}

.btn-export:disabled {
  background: #bdbdbd;
  cursor: not-allowed;
}
</style>
