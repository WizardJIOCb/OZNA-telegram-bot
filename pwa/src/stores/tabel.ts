import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '../composables/useApi'
import type { ActivityNode, TabelEntryPayload, Claim, ClaimCreatePayload, MilestoneSurveyData, MilestoneEstimatePayload, MilestoneEstimateResult, MilestoneAlert } from '../types'

export const useTabelStore = defineStore('tabel', () => {
  const activityTree = ref<ActivityNode[]>([])
  const projects = ref<string[]>([])
  const loading = ref(false)
  const error = ref('')
  const starredProjects = ref<string[]>(JSON.parse(localStorage.getItem('starred_projects') || '[]'))
  const recentProjects = ref<string[]>(JSON.parse(localStorage.getItem('recent_projects') || '[]'))

  async function loadActivityTree() {
    try {
      const cached = localStorage.getItem('activity_tree')
      if (cached && activityTree.value.length > 0) return
      if (cached) {
        activityTree.value = JSON.parse(cached)
      }
      const data = await api<ActivityNode[]>(`/tabel/activity-types?_=${Date.now()}`)
      activityTree.value = data
      localStorage.setItem('activity_tree', JSON.stringify(data))
    } catch (e) {
      if (activityTree.value.length === 0) {
        error.value = 'Не удалось загрузить типы работ'
      }
    }
  }

  async function loadProjects() {
    if (projects.value.length > 0) return
    try {
      const cached = localStorage.getItem('projects')
      if (cached) {
        projects.value = JSON.parse(cached)
      }
      const data = await api<string[]>('/tabel/projects')
      projects.value = data
      localStorage.setItem('projects', JSON.stringify(data))
    } catch (e) {
      if (projects.value.length === 0) {
        error.value = 'Не удалось загрузить проекты'
      }
    }
  }

  async function createEntry(payload: TabelEntryPayload): Promise<{ success: boolean; message: string }> {
    loading.value = true
    error.value = ''
    try {
      const res = await api<{ success: boolean; message: string }>('/tabel', {
        method: 'POST',
        body: JSON.stringify(payload),
      })
      return res
    } catch (e: unknown) {
      const msg = (e as { message?: string }).message || 'Ошибка сохранения'
      error.value = msg

      // Queue for offline sync if network error
      if (e instanceof TypeError) {
        queueOfflineEntry(payload)
        return { success: true, message: 'Сохранено локально. Отправится при подключении к сети.' }
      }
      throw e
    } finally {
      loading.value = false
    }
  }

  function queueOfflineEntry(payload: TabelEntryPayload) {
    const queue = JSON.parse(localStorage.getItem('offline_queue') || '[]')
    queue.push({ ...payload, queued_at: Date.now() })
    localStorage.setItem('offline_queue', JSON.stringify(queue))
  }

  async function syncOfflineQueue() {
    const queue = JSON.parse(localStorage.getItem('offline_queue') || '[]')
    if (queue.length === 0) return 0

    let synced = 0
    const remaining: TabelEntryPayload[] = []

    for (const entry of queue) {
      try {
        await api('/tabel', {
          method: 'POST',
          body: JSON.stringify(entry),
        })
        synced++
      } catch {
        remaining.push(entry)
      }
    }

    localStorage.setItem('offline_queue', JSON.stringify(remaining))
    return synced
  }

  function getOfflineQueueCount(): number {
    const queue = JSON.parse(localStorage.getItem('offline_queue') || '[]')
    return queue.length
  }

  function toggleStarProject(code: string) {
    const idx = starredProjects.value.indexOf(code)
    if (idx >= 0) {
      starredProjects.value.splice(idx, 1)
    } else {
      starredProjects.value.push(code)
    }
    localStorage.setItem('starred_projects', JSON.stringify(starredProjects.value))
  }

  function addRecentProject(code: string) {
    recentProjects.value = [code, ...recentProjects.value.filter(p => p !== code)].slice(0, 5)
    localStorage.setItem('recent_projects', JSON.stringify(recentProjects.value))
  }

  function getPersonalProjects(): string[] {
    return starredProjects.value.filter(p => projects.value.includes(p))
  }

  function isStarred(code: string): boolean {
    return starredProjects.value.includes(code)
  }

  async function loadClaims(projectCode: string): Promise<Claim[]> {
    try {
      return await api<Claim[]>(`/tabel/claims?project_code=${encodeURIComponent(projectCode)}`)
    } catch {
      return []
    }
  }

  async function createClaimEntry(payload: ClaimCreatePayload): Promise<{ success: boolean; message: string; claim_id?: number }> {
    return await api<{ success: boolean; message: string; claim_id?: number }>('/tabel/claims', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  }

  async function createAbsence(payload: { activity_type: string; start_date: string; end_date: string }): Promise<{ success: boolean; message: string }> {
    return await api<{ success: boolean; message: string }>('/tabel/absence', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  }

  async function closeClaim(claimId: number, reason?: string): Promise<{ success: boolean; message: string }> {
    return await api<{ success: boolean; message: string }>(`/tabel/claims/${claimId}/close`, {
      method: 'POST',
      body: JSON.stringify({ reason: reason || 'Закрыто из PWA' }),
    })
  }

  // ── Milestone Surveys ──────────────────────────────────────
  const milestoneAlerts = ref<MilestoneAlert[]>([])

  async function checkMilestoneSurvey(projectCode: string, activityType: string): Promise<MilestoneSurveyData> {
    return await api<MilestoneSurveyData>(
      `/tabel/milestone-survey?project_code=${encodeURIComponent(projectCode)}&activity_type=${encodeURIComponent(activityType)}`
    )
  }

  async function submitMilestoneEstimate(payload: MilestoneEstimatePayload): Promise<MilestoneEstimateResult> {
    return await api<MilestoneEstimateResult>('/tabel/milestone-estimate', {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  }

  async function loadMilestoneAlerts(): Promise<void> {
    try {
      const cached = localStorage.getItem('milestone_alerts')
      const cachedAt = localStorage.getItem('milestone_alerts_at')
      // Cache for 15 minutes
      if (cached && cachedAt && Date.now() - parseInt(cachedAt) < 15 * 60 * 1000) {
        milestoneAlerts.value = JSON.parse(cached)
        return
      }
      const data = await api<{ alerts: MilestoneAlert[] }>('/tabel/milestone-alerts')
      milestoneAlerts.value = data.alerts
      localStorage.setItem('milestone_alerts', JSON.stringify(data.alerts))
      localStorage.setItem('milestone_alerts_at', String(Date.now()))
    } catch {
      // Use cached data if available
      const cached = localStorage.getItem('milestone_alerts')
      if (cached) milestoneAlerts.value = JSON.parse(cached)
    }
  }

  return {
    activityTree, projects, loading, error,
    starredProjects, recentProjects,
    loadActivityTree, loadProjects, createEntry,
    syncOfflineQueue, getOfflineQueueCount,
    toggleStarProject, addRecentProject, getPersonalProjects, isStarred,
    loadClaims, createClaimEntry, createAbsence, closeClaim,
    milestoneAlerts, checkMilestoneSurvey, submitMilestoneEstimate, loadMilestoneAlerts,
  }
})
