export interface User {
  fio: string
  functional_direction: string
  access_level: number
  access_level_name?: string
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
