import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '../composables/useApi'
import type { EmployeeLocation } from '../types'

export const useLocationStore = defineStore('location', () => {
  const locations = ref<EmployeeLocation[]>([])
  const loading = ref(false)

  /**
   * Capture current GPS position via browser Geolocation API.
   * Returns coordinates or null if unavailable/denied. Never throws.
   */
  function capturePosition(): Promise<{ latitude: number; longitude: number; accuracy: number } | null> {
    return new Promise((resolve) => {
      if (!navigator.geolocation) {
        resolve(null)
        return
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
          })
        },
        () => {
          // Permission denied or error — resolve null silently
          resolve(null)
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 300000, // 5 min cache
        },
      )
    })
  }

  /**
   * Fire-and-forget: save location to backend.
   * Errors are silently ignored — this should never block user workflows.
   */
  async function saveLocation(latitude: number, longitude: number, accuracy: number | null, projectCode?: string): Promise<void> {
    try {
      await api('/location', {
        method: 'POST',
        body: JSON.stringify({ latitude, longitude, accuracy, project_code: projectCode || '' }),
      })
    } catch {
      // Silently ignore — location saving is best-effort
    }
  }

  /**
   * Load all recent employee locations for the map.
   */
  async function loadLocations(): Promise<void> {
    loading.value = true
    try {
      locations.value = await api<EmployeeLocation[]>('/locations')
    } catch {
      locations.value = []
    } finally {
      loading.value = false
    }
  }

  return { locations, loading, capturePosition, saveLocation, loadLocations }
})
