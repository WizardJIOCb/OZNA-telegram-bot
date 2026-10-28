export interface User {
  fio: string
  functional_direction: string
  access_level: number
  access_level_name?: string
  share_location?: boolean
}

export interface AuthTokens {
  access_token: string
  refresh_token: string
  user: User
}

export interface ActivityNode {
  key: string
  label: string
  leaf?: boolean
  children?: ActivityNode[]
  needsInput?: boolean
  inputLabel?: string
}

export interface TabelEntryPayload {
  date: string
  project_code: string
  activity_type: string
  idle_reason?: string
  custom_description?: string
}

export interface ApiError {
  error: string
  duplicate?: boolean
}

export interface Claim {
  id: number
  project_code: string
  reg_number: string
  subject: string
  act_date: string
  equipment: string
  serial_number: string
  act_author: string
  description: string
  status: string
  created_at: string
}

export interface ClaimCreatePayload {
  project_code: string
  subject: string
  equipment: string
  serial_number: string
  description: string
}

export interface MilestoneSurveyData {
  needs_survey: boolean
  milestone_name: string
  work_type: string
  plan_hours: number | null
  fact_hours: number
  previous_percent: number
  previous_estimator: string | null
  previous_date: string | null
  efficiency: number | null
  total_efficiency: number | null
  motivational: { level: string; emoji: string; message: string } | null
}

export interface MilestoneEstimatePayload {
  project_code: string
  activity_type: string
  estimated_percent: number
}

export interface MilestoneEstimateResult {
  success: boolean
  efficiency: number
  motivational: { level: string; emoji: string; message: string } | null
  is_complete: boolean
}

export interface MilestoneAlert {
  project_code: string
  work_type: string
  efficiency: number
  level: string
  emoji: string
  message: string
}

export interface EmployeeLocation {
  user_id: number
  fio: string
  functional_direction: string
  latitude: number
  longitude: number
  accuracy: number | null
  updated_at: string
  project_code: string
}

// ── Metrics Module ────────────────────────────────────────

export interface MetricsBNSummary {
  groups: Record<string, { name: string; count: number }>
  missing_info: { active: number; inactive: number }
}

export interface MetricsProjectListItem {
  project_code: string
  project_name: string
  customer_name: string | null
  business_unit_name: string | null
  work_types: string[]
  best_progress: number | null
  worst_efficiency: number | null
  worst_forecast_efficiency: number | null
  is_favorite: boolean
  has_missing_data: boolean
}

export interface MetricsMilestone {
  name: string
  plan_days: number
  fact_days: number
  progress: number
  efficiency: number | null
  is_confirmed?: boolean
}

export interface MetricsWorkTypeData {
  planned_completion: string | null
  planned_hours_days: number | null
  fact_hours_days: number | null
  stage_progress: number | null
  current_efficiency: number | null
  forecast_efficiency: number | null
  forecast_remaining_days: number | null
  forecast_completion: string | null
  milestones: MetricsMilestone[]
  auxiliary?: Record<string, { plan: number; fact: number }>
  historical_fact?: number | null
}

export interface MetricsProjectDetail {
  project: {
    project_code: string
    project_name: string
    customer_name: string | null
    business_unit_name: string | null
    project_manager_fio: string | null
    is_favorite: boolean
  }
  work_types: Record<string, MetricsWorkTypeData>
}

export interface ProjectCardEditData {
  project_code: string
  project_name: string
  customer_id: number | null
  business_unit_id: number | null
  project_manager_id: number | null
  smr_planned_hours: number | null
  smr_planned_completion: string | null
  smr_mobilization: number | null
  smr_project_activity: number | null
  smr_master_duties: number | null
  pnr_planned_hours: number | null
  pnr_planned_completion: string | null
  pnr_mobilization: number | null
  pnr_project_activity: number | null
  cmr_planned_completion: string | null
  cmr_mobilization: number | null
  cmr_project_activity: number | null
  cmr_forecast_enabled: number | null
  name_1c: string | null
}

export interface ProjectCardLookups {
  customers: { id: number; name: string }[]
  business_units: { id: number; name: string }[]
  managers: { id: number; name: string }[]
}

export interface MilestoneEditItem {
  id: number
  milestone_name: string
  planned_hours: number | null
  is_not_applicable: boolean
  unit: 'days' | 'hours'
}

export interface MilestoneAdditional {
  mobilization: number | null
  project_activity: number | null
  master_duties?: number | null
  planned_completion: string | null
}

export interface MilestonesData {
  milestones: MilestoneEditItem[]
  additional: MilestoneAdditional
  work_type: string
}
