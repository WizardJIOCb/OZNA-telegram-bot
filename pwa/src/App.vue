<template>
  <div id="ozna-app">
    <div v-if="!isOnline" class="offline-banner">
      Нет подключения к сети
    </div>
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useTabelStore } from './stores/tabel'

const isOnline = ref(navigator.onLine)

function handleOnline() { isOnline.value = true; syncQueue() }
function handleOffline() { isOnline.value = false }

async function syncQueue() {
  const tabel = useTabelStore()
  const synced = await tabel.syncOfflineQueue()
  if (synced > 0) {
    console.log(`Synced ${synced} offline entries`)
  }
}

onMounted(() => {
  window.addEventListener('online', handleOnline)
  window.addEventListener('offline', handleOffline)
  if (isOnline.value) syncQueue()
})

onUnmounted(() => {
  window.removeEventListener('online', handleOnline)
  window.removeEventListener('offline', handleOffline)
})
</script>
