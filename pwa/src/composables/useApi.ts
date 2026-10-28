import type { ApiError } from '../types'

const API_BASE = import.meta.env.PROD ? '/tg/ozna/api' : '/tg/ozna/api'
const MAX_RETRIES = 3
const RETRY_DELAYS = [1000, 3000, 9000]
const TIMEOUT = 30000

function getTokens() {
  return {
    access: localStorage.getItem('access_token'),
    refresh: localStorage.getItem('refresh_token'),
  }
}

function setTokens(access: string, refresh: string) {
  localStorage.setItem('access_token', access)
  localStorage.setItem('refresh_token', refresh)
}

export function clearTokens() {
  localStorage.removeItem('access_token')
  localStorage.removeItem('refresh_token')
}

async function refreshAccessToken(): Promise<string | null> {
  const { refresh } = getTokens()
  if (!refresh) return null

  try {
    const res = await fetch(`${API_BASE}/auth/refresh`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ refresh_token: refresh }),
    })

    if (!res.ok) {
      clearTokens()
      return null
    }

    const data = await res.json()
    setTokens(data.access_token, data.refresh_token)
    return data.access_token
  } catch {
    return null
  }
}

async function fetchWithTimeout(url: string, options: RequestInit): Promise<Response> {
  const controller = new AbortController()
  const timer = setTimeout(() => controller.abort(), TIMEOUT)
  try {
    return await fetch(url, { ...options, signal: controller.signal })
  } finally {
    clearTimeout(timer)
  }
}

export async function api<T = unknown>(
  path: string,
  options: RequestInit = {},
  requireAuth = true,
): Promise<T> {
  const url = `${API_BASE}${path}`

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    ...(options.headers as Record<string, string> || {}),
  }

  if (requireAuth) {
    const { access } = getTokens()
    if (access) {
      headers['Authorization'] = `Bearer ${access}`
    }
  }

  let lastError: Error | null = null

  for (let attempt = 0; attempt <= MAX_RETRIES; attempt++) {
    try {
      let res = await fetchWithTimeout(url, { ...options, headers })

      // Auto-refresh on 401
      if (res.status === 401 && requireAuth && attempt === 0) {
        const newToken = await refreshAccessToken()
        if (newToken) {
          headers['Authorization'] = `Bearer ${newToken}`
          res = await fetchWithTimeout(url, { ...options, headers })
        } else {
          clearTokens()
          window.location.hash = '#/'
          throw new Error('Session expired')
        }
      }

      if (!res.ok) {
        const err: ApiError = await res.json().catch(() => ({ error: `HTTP ${res.status}` }))
        throw Object.assign(new Error(err.error), { status: res.status, data: err })
      }

      return await res.json() as T
    } catch (e: unknown) {
      lastError = e as Error
      const isNetworkError = e instanceof TypeError || (e as { name?: string }).name === 'AbortError'
      const isServerError = (e as { status?: number }).status && (e as { status: number }).status >= 500

      // Only retry on network/server errors, not on 4xx
      if ((isNetworkError || isServerError) && attempt < MAX_RETRIES) {
        await new Promise(r => setTimeout(r, RETRY_DELAYS[attempt]))
        continue
      }
      throw e
    }
  }

  throw lastError || new Error('Request failed')
}
