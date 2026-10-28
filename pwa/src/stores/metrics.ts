import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '../composables/useApi'
import type {
  MetricsBNSummary,
  MetricsProjectListItem,
  MetricsProjectDetail,
  ProjectCardEditData,
  ProjectCardLookups,
  MilestonesData,
} from '../types'

export const useMetricsStore = defineStore('metrics', () => {
  const bnSummary = ref<MetricsBNSummary | null>(null)
  const projectList = ref<MetricsProjectListItem[]>([])
  const currentProject = ref<MetricsProjectDetail | null>(null)
  const projectCard = ref<ProjectCardEditData | null>(null)
  const lookups = ref<ProjectCardLookups | null>(null)
  const loading = ref(false)
  const error = ref('')
  const milestonesData = ref<MilestonesData | null>(null)

  async function loadBNSummary(): Promise<void> {
    try {
      const cached = localStorage.getItem('metrics_bn_summary')
      const cachedAt = localStorage.getItem('metrics_bn_summary_at')
      if (cached && cachedAt && Date.now() - parseInt(cachedAt) < 15 * 60 * 1000) {
        bnSummary.value = JSON.parse(cached)
        return
      }
      const data = await api<MetricsBNSummary>('/metrics/bn-summary')
      bnSummary.value = data
      localStorage.setItem('metrics_bn_summary', JSON.stringify(data))
      localStorage.setItem('metrics_bn_summary_at', String(Date.now()))
    } catch (e) {
      const cached = localStorage.getItem('metrics_bn_summary')
      if (cached) bnSummary.value = JSON.parse(cached)
      else error.value = 'Не удалось загрузить данные метрик'
    }
  }

  async function loadProjects(group: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
      const data = await api<{ projects: MetricsProjectListItem[] }>(
        `/metrics/projects?group=${encodeURIComponent(group)}`
      )
      projectList.value = data.projects
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка загрузки проектов'
      projectList.value = []
    } finally {
      loading.value = false
    }
  }

  async function loadProject(code: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
      const data = await api<MetricsProjectDetail>(
        `/metrics/project/${encodeURIComponent(code)}`
      )
      currentProject.value = data
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка загрузки проекта'
      currentProject.value = null
    } finally {
      loading.value = false
    }
  }

  async function loadProjectCard(code: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
      const data = await api<{ card: ProjectCardEditData; lookups: ProjectCardLookups }>(
        `/metrics/project-card/${encodeURIComponent(code)}`
      )
      projectCard.value = data.card
      lookups.value = data.lookups
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка загрузки карточки'
    } finally {
      loading.value = false
    }
  }

  async function updateProjectCard(
    code: string,
    fields: Record<string, unknown>
  ): Promise<boolean> {
    error.value = ''
    try {
      await api(`/metrics/project-card/${encodeURIComponent(code)}`, {
        method: 'PUT',
        body: JSON.stringify(fields),
      })
      localStorage.removeItem('metrics_bn_summary')
      localStorage.removeItem('metrics_bn_summary_at')
      return true
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка сохранения'
      return false
    }
  }

  async function updateProcessCard(
    code: string,
    workType: string,
    fields: Record<string, unknown>
  ): Promise<boolean> {
    error.value = ''
    try {
      await api(
        `/metrics/project-card/${encodeURIComponent(code)}/process/${encodeURIComponent(workType)}`,
        { method: 'PUT', body: JSON.stringify(fields) }
      )
      return true
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка сохранения'
      return false
    }
  }

  async function toggleFavorite(code: string): Promise<boolean> {
    try {
      const data = await api<{ is_favorite: boolean }>('/metrics/favorites', {
        method: 'POST',
        body: JSON.stringify({ project_code: code }),
      })
      const item = projectList.value.find(p => p.project_code === code)
      if (item) item.is_favorite = data.is_favorite
      if (currentProject.value?.project?.project_code === code) {
        currentProject.value.project.is_favorite = data.is_favorite
      }
      return data.is_favorite
    } catch {
      return false
    }
  }

  async function createLookupItem(
    type: 'customer' | 'business-unit' | 'manager',
    name: string
  ): Promise<{ id: number; name: string } | null> {
    try {
      const data = await api<{ id: number; name: string }>(
        `/metrics/lookups/${type}`,
        { method: 'POST', body: JSON.stringify({ name }) }
      )
      if (lookups.value && data) {
        if (type === 'customer') lookups.value.customers.push(data)
        else if (type === 'business-unit') lookups.value.business_units.push(data)
        else if (type === 'manager') lookups.value.managers.push(data)
      }
      return data
    } catch {
      return null
    }
  }

  async function loadMilestones(code: string, workType: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
      const data = await api<MilestonesData>(
        `/metrics/milestones/${encodeURIComponent(code)}/${encodeURIComponent(workType)}`
      )
      milestonesData.value = data
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка загрузки вех'
      milestonesData.value = null
    } finally {
      loading.value = false
    }
  }

  async function updateMilestones(
    code: string,
    workType: string,
    body: Record<string, unknown>
  ): Promise<boolean> {
    error.value = ''
    try {
      await api(
        `/metrics/milestones/${encodeURIComponent(code)}/${encodeURIComponent(workType)}`,
        { method: 'PUT', body: JSON.stringify(body) }
      )
      localStorage.removeItem('metrics_bn_summary')
      localStorage.removeItem('metrics_bn_summary_at')
      return true
    } catch (e: unknown) {
      error.value = (e as { message?: string }).message || 'Ошибка сохранения вех'
      return false
    }
  }

  async function confirmMilestone(code: string, workType: string, milestoneName: string): Promise<boolean> {
    try {
      await api<{ success: boolean }>('/metrics/milestone-confirm', {
        method: 'POST',
        body: JSON.stringify({ project_code: code, work_type: workType, milestone_name: milestoneName }),
      })
      return true
    } catch {
      return false
    }
  }

  async function unconfirmMilestone(code: string, workType: string, milestoneName: string): Promise<boolean> {
    try {
      await api<{ success: boolean }>('/metrics/milestone-unconfirm', {
        method: 'POST',
        body: JSON.stringify({ project_code: code, work_type: workType, milestone_name: milestoneName }),
      })
      return true
    } catch {
      return false
    }
  }

  return {
    bnSummary,
    projectList,
    currentProject,
    projectCard,
    lookups,
    loading,
    error,
    milestonesData,
    loadBNSummary,
    loadProjects,
    loadProject,
    loadProjectCard,
    updateProjectCard,
    updateProcessCard,
    toggleFavorite,
    createLookupItem,
    loadMilestones,
    updateMilestones,
    confirmMilestone,
    unconfirmMilestone,
  }
})
