<template>
  <div class="page">
    <header class="app-header">
      <button class="btn-back" @click="handleBack">&larr;</button>
      <h1>Метрика</h1>
      <span v-if="step > 1" class="step-indicator">{{ stepLabel }}</span>
    </header>

    <!-- Step 1: BN Menu -->
    <div v-if="step === 1" class="step">
      <div v-if="summaryLoading" class="loading">Загрузка...</div>
      <template v-else-if="metrics.bnSummary">
        <div class="option-list">
          <button
            v-for="g in bnGroupButtons"
            :key="g.key"
            class="btn-option bn-btn"
            @click="selectBNGroup(g.key)"
          >
            <span>{{ g.name }}</span>
            <span class="bn-count">{{ g.count }}</span>
          </button>
        </div>

        <button class="btn-missing-info" @click="goToMissingInfo">
          Проекты без информации
          <span v-if="totalMissingCount > 0" class="bn-count">{{ totalMissingCount }}</span>
        </button>
      </template>
      <div v-else class="empty">Нет данных. Запустите пересчёт метрик.</div>
    </div>

    <!-- Step 2: Missing Info Sub-Menu -->
    <div v-if="step === 2" class="step">
      <h2>Проекты без информации</h2>
      <div class="option-list">
        <button class="btn-option" @click="selectGroup('missing_active')">
          <span>Активные проекты</span>
          <span class="bn-count">{{ metrics.bnSummary?.missing_info?.active ?? 0 }}</span>
        </button>
        <button class="btn-option" @click="selectGroup('missing_inactive')">
          <span>Неактивные проекты</span>
          <span class="bn-count">{{ metrics.bnSummary?.missing_info?.inactive ?? 0 }}</span>
        </button>
      </div>
    </div>

    <!-- Step 3: Project List -->
    <div v-if="step === 3" class="step">
      <h2>{{ currentGroupName }}</h2>
      <div class="field">
        <input
          type="text"
          v-model="projectSearch"
          placeholder="Поиск проекта..."
          class="search-input"
        />
      </div>

      <div v-if="metrics.loading" class="loading">Загрузка проектов...</div>
      <template v-else>
        <div v-if="!projectSearch && favoriteProjects.length > 0">
          <div class="section-label">Избранные</div>
          <div class="option-list">
            <button
              v-for="p in favoriteProjects"
              :key="'fav-' + p.project_code"
              class="btn-option btn-project"
              @click="selectProject(p)"
            >
              <div class="project-info">
                <div class="project-code">{{ p.project_code }}</div>
                <div v-if="p.project_name" class="project-name">{{ p.project_name }}</div>
              </div>
              <span
                class="star-icon active"
                @click.stop="metrics.toggleFavorite(p.project_code)"
              >&#9733;</span>
            </button>
          </div>
          <div class="section-label">Все проекты</div>
        </div>

        <div class="option-list">
          <button
            v-for="p in filteredProjects"
            :key="p.project_code"
            class="btn-option btn-project"
            @click="selectProject(p)"
          >
            <div class="project-info">
              <div class="project-code">{{ p.project_code }}</div>
              <div v-if="p.project_name" class="project-name">{{ p.project_name }}</div>
              <div v-if="!isMissingInfoMode && (p.worst_efficiency !== null || p.worst_forecast_efficiency !== null)" class="project-eff">
                <span :style="{ color: effColor(p.worst_efficiency) }">{{ p.worst_efficiency !== null ? p.worst_efficiency + '%' : '—' }}</span>
                <span style="opacity:0.5"> | </span>
                <span :style="{ color: effColor(p.worst_forecast_efficiency) }">{{ p.worst_forecast_efficiency !== null ? p.worst_forecast_efficiency + '%' : '—' }}</span>
              </div>
            </div>
            <span
              class="star-icon"
              :class="{ active: p.is_favorite }"
              @click.stop="metrics.toggleFavorite(p.project_code)"
            >{{ p.is_favorite ? '&#9733;' : '&#9734;' }}</span>
          </button>
        </div>
        <div v-if="filteredProjects.length === 0 && !projectSearch" class="empty">
          Нет проектов в этой категории
        </div>
        <div v-if="filteredProjects.length === 0 && projectSearch" class="empty">
          Проекты не найдены
        </div>
      </template>
    </div>

    <!-- Step 4: Project Info (General) -->
    <div v-if="step === 4" class="step">
      <div v-if="metrics.loading" class="loading">Загрузка метрик...</div>
      <template v-else-if="metrics.currentProject">
        <div class="confirm-card">
          <div class="confirm-row">
            <span class="label">Шифр</span>
            <span class="value">{{ metrics.currentProject.project.project_code }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">Название</span>
            <span class="value">{{ metrics.currentProject.project.project_name || '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">Заказчик</span>
            <span class="value">{{ metrics.currentProject.project.customer_name || '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">БН</span>
            <span class="value">{{ metrics.currentProject.project.business_unit_name || '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">РП</span>
            <span class="value">{{ metrics.currentProject.project.project_manager_fio || '—' }}</span>
          </div>
        </div>

        <div v-for="(data, wt) in metrics.currentProject.work_types" :key="wt" class="wt-summary-card">
          <div class="wt-summary-header">{{ wt }}</div>
          <div class="wt-summary-body">
            <div class="wt-stat">
              <span class="wt-stat-label">Прогресс</span>
              <span class="wt-stat-value">{{ data.stage_progress !== null ? data.stage_progress + '%' : '—' }}</span>
            </div>
            <div class="wt-stat">
              <span class="wt-stat-label">Эффективность</span>
              <span class="wt-stat-value">
                <template v-if="data.current_efficiency !== null || data.forecast_efficiency !== null">
                  <span :style="{ color: data.current_efficiency !== null ? effColor(data.current_efficiency) : '' }">{{ data.current_efficiency !== null ? data.current_efficiency + '%' : '—' }}</span>
                  <span style="opacity:0.5"> | </span>
                  <span :style="{ color: data.forecast_efficiency !== null ? effColor(data.forecast_efficiency) : '' }">{{ data.forecast_efficiency !== null ? data.forecast_efficiency + '%' : '—' }}</span>
                </template>
                <template v-else>—</template>
              </span>
            </div>
            <div class="wt-stat">
              <span class="wt-stat-label">План</span>
              <span class="wt-stat-value">{{ data.planned_completion || '—' }}</span>
            </div>
            <div class="wt-stat">
              <span class="wt-stat-label">Прогноз</span>
              <span class="wt-stat-value">{{ data.forecast_completion || '—' }}</span>
            </div>
          </div>
        </div>

        <div class="process-buttons">
          <button
            v-for="wt in availableWorkTypes"
            :key="wt"
            class="btn-process"
            @click="selectProcess(wt)"
          >{{ wt }}</button>
        </div>

        <button class="btn-secondary" style="margin-top: 8px" @click="goToGeneralEditor">
          Настройки
        </button>
      </template>
    </div>

    <!-- Step 5: Process Detail -->
    <div v-if="step === 5" class="step">
      <h2>{{ selectedWorkType }}</h2>
      <template v-if="selectedProcessData">
        <div class="confirm-card">
          <div class="confirm-row">
            <span class="label">Прогресс этапа</span>
            <span class="value">{{ selectedProcessData.stage_progress !== null ? selectedProcessData.stage_progress + '%' : '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">Текущая эффективность</span>
            <span class="value" :style="{ color: selectedProcessData.current_efficiency !== null ? effColor(selectedProcessData.current_efficiency) : '' }">
              {{ selectedProcessData.current_efficiency !== null ? selectedProcessData.current_efficiency + '%' : '—' }}
            </span>
          </div>
          <div class="confirm-row">
            <span class="label">Прогнозная эффективность</span>
            <span class="value" :style="{ color: selectedProcessData.forecast_efficiency !== null ? effColor(selectedProcessData.forecast_efficiency) : '' }">
              {{ selectedProcessData.forecast_efficiency !== null ? selectedProcessData.forecast_efficiency + '%' : '—' }}
            </span>
          </div>
          <div class="confirm-row">
            <span class="label">План завершения</span>
            <span class="value">{{ selectedProcessData.planned_completion || '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">Прогноз завершения</span>
            <span class="value">{{ selectedProcessData.forecast_completion || '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">План (чел-дни)</span>
            <span class="value">{{ selectedProcessData.planned_hours_days !== null ? selectedProcessData.planned_hours_days : '—' }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">Факт (чел-дни)</span>
            <span class="value">{{ selectedProcessData.fact_hours_days !== null ? selectedProcessData.fact_hours_days : '—' }}</span>
          </div>
          <div v-if="selectedProcessData.historical_fact" class="confirm-row historical-fact-note">
            <span class="label">В т.ч. исторические данные</span>
            <span class="value">{{ selectedProcessData.historical_fact }} чел-дн</span>
          </div>
          <div v-if="selectedProcessData.forecast_remaining_days !== null" class="confirm-row">
            <span class="label">Прогноз остаток (чел-дни)</span>
            <span class="value">{{ selectedProcessData.forecast_remaining_days }}</span>
          </div>
        </div>

        <div v-if="selectedProcessData.milestones.length > 0">
          <div class="section-label">Вехи</div>

          <!-- СМР: grouped by top-level milestone -->
          <div v-if="cmrMilestoneGroups" class="milestone-table">
            <div v-for="(group, topName) in cmrMilestoneGroups" :key="topName" class="cmr-group">
              <div class="cmr-group-header">
                <span>{{ topName }}</span>
                <span v-if="group.allConfirmed" class="confirmed-badge">Подтверждена</span>
                <button v-if="group.allConfirmed" class="btn-unconfirm" :disabled="confirmingMilestone !== null" @click="onUnconfirmMilestone(topName as string)">
                  {{ confirmingMilestone === topName ? '...' : 'Отменить' }}
                </button>
                <button v-else class="btn-confirm" :disabled="confirmingMilestone !== null" @click="onConfirmMilestone(topName as string)">
                  {{ confirmingMilestone === topName ? '...' : 'Подтвердить (100%)' }}
                </button>
              </div>
              <div v-for="m in group.milestones" :key="m.name" class="milestone-row cmr-sub-milestone" :class="{ 'milestone-confirmed': m.is_confirmed }">
                <div class="milestone-name">{{ m.name.includes(' - ') ? m.name.split(' - ')[1] : m.name }}</div>
                <div class="milestone-stats">
                  <div class="milestone-stat">
                    <span class="milestone-stat-label">Прогресс</span>
                    <span class="milestone-stat-value">{{ m.progress }}%</span>
                  </div>
                  <div class="milestone-stat">
                    <span class="milestone-stat-label">План</span>
                    <span class="milestone-stat-value">{{ m.plan_days }}д</span>
                  </div>
                  <div class="milestone-stat">
                    <span class="milestone-stat-label">Факт</span>
                    <span class="milestone-stat-value">{{ m.fact_days }}д</span>
                  </div>
                  <div class="milestone-stat">
                    <span class="milestone-stat-label">Эфф.</span>
                    <span class="milestone-stat-value" :style="{ color: m.efficiency !== null ? effColor(m.efficiency) : '' }">
                      {{ m.efficiency !== null ? m.efficiency + '%' : '—' }}
                    </span>
                  </div>
                </div>
                <div class="milestone-bar-track">
                  <div class="milestone-bar-fill" :style="{ width: Math.min(m.progress, 100) + '%', background: effColor(m.efficiency ?? 100) }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- ШМР/ПНР: flat list with per-milestone confirm -->
          <div v-else class="milestone-table">
            <div v-for="m in selectedProcessData.milestones" :key="m.name" class="milestone-row" :class="{ 'milestone-confirmed': m.is_confirmed }">
              <div class="milestone-name">
                {{ m.name }}
                <span v-if="m.is_confirmed" class="confirmed-badge">Подтверждена</span>
              </div>
              <div class="milestone-stats">
                <div class="milestone-stat">
                  <span class="milestone-stat-label">Прогресс</span>
                  <span class="milestone-stat-value">{{ m.progress }}%</span>
                </div>
                <div class="milestone-stat">
                  <span class="milestone-stat-label">План</span>
                  <span class="milestone-stat-value">{{ m.plan_days }}д</span>
                </div>
                <div class="milestone-stat">
                  <span class="milestone-stat-label">Факт</span>
                  <span class="milestone-stat-value">{{ m.fact_days }}д</span>
                </div>
                <div class="milestone-stat">
                  <span class="milestone-stat-label">Эфф.</span>
                  <span class="milestone-stat-value" :style="{ color: m.efficiency !== null ? effColor(m.efficiency) : '' }">
                    {{ m.efficiency !== null ? m.efficiency + '%' : '—' }}
                  </span>
                </div>
              </div>
              <div class="milestone-bar-track">
                <div class="milestone-bar-fill" :style="{ width: Math.min(m.progress, 100) + '%', background: effColor(m.efficiency ?? 100) }"></div>
              </div>
              <div class="milestone-confirm-actions">
                <button v-if="m.is_confirmed" class="btn-unconfirm" :disabled="confirmingMilestone !== null" @click="onUnconfirmMilestone(m.name)">
                  {{ confirmingMilestone === m.name ? '...' : 'Отменить подтверждение' }}
                </button>
                <button v-else class="btn-confirm" :disabled="confirmingMilestone !== null" @click="onConfirmMilestone(m.name)">
                  {{ confirmingMilestone === m.name ? '...' : 'Подтвердить (100%)' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="Object.keys(projectActivity).length > 0">
          <div class="section-label">Проектная активность</div>
          <div class="auxiliary-table">
            <div v-for="(item, key) in projectActivity" :key="key" class="auxiliary-row">
              <div class="auxiliary-name">{{ auxLabel(key) }}</div>
              <div class="auxiliary-stats">
                <div v-if="item.plan > 0" class="auxiliary-stat">
                  <span class="auxiliary-stat-label">План</span>
                  <span class="auxiliary-stat-value">{{ item.plan }}д</span>
                </div>
                <div class="auxiliary-stat">
                  <span class="auxiliary-stat-label">Факт</span>
                  <span class="auxiliary-stat-value">{{ item.fact }}д</span>
                </div>
                <div v-if="item.plan > 0" class="auxiliary-stat">
                  <span class="auxiliary-stat-label">Откл.</span>
                  <span class="auxiliary-stat-value" :style="{ color: item.fact > item.plan ? '#c62828' : '#2e7d32' }">
                    {{ item.fact > item.plan ? '+' : '' }}{{ item.fact - item.plan }}д
                  </span>
                </div>
              </div>
              <div v-if="item.plan > 0" class="milestone-bar-track">
                <div class="milestone-bar-fill" :style="{
                  width: Math.min(item.fact / item.plan * 100, 100) + '%',
                  background: item.fact <= item.plan ? '#2e7d32' : item.fact <= item.plan * 1.2 ? '#e6a700' : '#c62828'
                }"></div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="Object.keys(additionalWorks).length > 0">
          <div class="section-label">Дополнительные работы</div>
          <div class="auxiliary-table">
            <div v-for="(item, key) in additionalWorks" :key="key" class="auxiliary-row">
              <div class="auxiliary-name">{{ auxLabel(key) }}</div>
              <div class="auxiliary-stats">
                <div class="auxiliary-stat">
                  <span class="auxiliary-stat-label">Факт</span>
                  <span class="auxiliary-stat-value">{{ item.fact }}д</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button class="btn-secondary" style="margin-top: 12px" @click="goToProcessEditor">
          Настройки {{ selectedWorkType }}
        </button>
      </template>
      <template v-else>
        <div class="empty" style="margin-bottom: 12px">Метрики ещё не рассчитаны для {{ selectedWorkType }}</div>
        <button class="btn-secondary" style="margin-top: 12px" @click="goToProcessEditor">
          Настройки {{ selectedWorkType }}
        </button>
      </template>
    </div>

    <!-- Step 6: Конструктор — General fields (matching TG constructor) -->
    <div v-if="step === 6" class="step">
      <h2>Конструктор проекта</h2>
      <div v-if="metrics.loading" class="loading">Загрузка...</div>
      <template v-else-if="metrics.projectCard">
        <div class="field">
          <label>Наименование проекта</label>
          <input type="text" v-model="editForm.project_name" />
        </div>

        <!-- РП — from project_managers, fillable -->
        <div class="field">
          <label>Руководитель проекта (РП)</label>
          <select v-model="editForm.project_manager_id">
            <option :value="null">— Не указан —</option>
            <option v-for="m in metrics.lookups?.managers" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
          <div class="add-new-row">
            <input type="text" v-model="newManagerName" placeholder="Добавить нового РП..." class="add-new-input" />
            <button class="btn-add-new" @click="addManager" :disabled="!newManagerName.trim()">+</button>
          </div>
        </div>

        <!-- Заказчик — from customers, fillable -->
        <div class="field">
          <label>Заказчик</label>
          <select v-model="editForm.customer_id">
            <option :value="null">— Не указан —</option>
            <option v-for="c in metrics.lookups?.customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <div class="add-new-row">
            <input type="text" v-model="newCustomerName" placeholder="Добавить нового заказчика..." class="add-new-input" />
            <button class="btn-add-new" @click="addCustomer" :disabled="!newCustomerName.trim()">+</button>
          </div>
        </div>

        <!-- БН — from business_units, fillable -->
        <div class="field">
          <label>Бизнес-направление (БН)</label>
          <select v-model="editForm.business_unit_id">
            <option :value="null">— Не указано —</option>
            <option v-for="b in metrics.lookups?.business_units" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
          <div class="add-new-row">
            <input type="text" v-model="newBUName" placeholder="Добавить новое БН..." class="add-new-input" />
            <button class="btn-add-new" @click="addBusinessUnit" :disabled="!newBUName.trim()">+</button>
          </div>
        </div>

        <div class="field">
          <label>Наименование 1С</label>
          <input type="text" v-model="editForm.name_1c" />
        </div>

        <div v-if="saveError" class="error">{{ saveError }}</div>
        <div v-if="saveSuccess" class="success">Сохранено</div>

        <button class="btn-primary" @click="saveGeneral" :disabled="saving">
          {{ saving ? 'Сохранение...' : 'Сохранить' }}
        </button>

        <div class="section-label" style="margin-top:16px">Настройка этапов</div>
        <div class="process-buttons">
          <button v-for="wt in ['ШМР', 'ПНР', 'СМР']" :key="wt" class="btn-process" @click="goToProcessEditorDirect(wt)">
            {{ wt }}
          </button>
        </div>
      </template>
    </div>

    <!-- Step 7: Конструктор — Process settings with milestones (matching TG constructor) -->
    <div v-if="step === 7" class="step">
      <h2>Настройки {{ selectedWorkType }}</h2>
      <div v-if="metrics.loading" class="loading">Загрузка...</div>
      <template v-else-if="metrics.milestonesData">
        <!-- Milestones editor -->
        <div v-if="metrics.milestonesData.milestones.length > 0">
          <div class="section-label">Вехи (плановые трудозатраты)</div>
          <div class="milestone-edit-list">
            <div
              v-for="m in milestoneEditList"
              :key="m.id"
              class="milestone-edit-row"
              :class="{ 'milestone-na': m.is_not_applicable }"
            >
              <div class="milestone-edit-name">{{ m.milestone_name }}</div>
              <div class="milestone-edit-controls">
                <input
                  type="number"
                  step="1"
                  :value="m.planned_hours"
                  @input="updateMilestoneValue(m.id, $event)"
                  :disabled="m.is_not_applicable"
                  class="milestone-edit-input"
                  :placeholder="m.unit === 'hours' ? 'ч.' : 'дн.'"
                />
                <span class="milestone-edit-unit">{{ m.unit === 'hours' ? 'ч.' : 'дн.' }}</span>
                <label class="milestone-na-toggle">
                  <input type="checkbox" :checked="m.is_not_applicable" @change="toggleMilestoneNA(m.id)" />
                  <span class="na-label">Н/П</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty" style="margin-bottom:12px">Вехи не настроены для {{ selectedWorkType }}</div>

        <!-- Additional work fields -->
        <div class="section-label">Дополнительные работы (дни)</div>
        <div class="field">
          <label>Мобилизация (дни)</label>
          <input type="number" step="1" v-model.number="additionalForm.mobilization" />
        </div>
        <div v-if="selectedWorkType === 'ШМР'" class="field">
          <label>Обязанности мастера (дни)</label>
          <input type="number" step="1" v-model.number="additionalForm.master_duties" />
        </div>
        <div class="field">
          <label>Проектная деятельность (дни)</label>
          <input type="number" step="1" v-model.number="additionalForm.project_activity" />
        </div>

        <!-- Historical fact (archival labor intensity) -->
        <div class="section-label">Исторические данные</div>
        <div class="field">
          <label>Архивная трудоёмкость (чел-дни)</label>
          <input type="number" step="0.01" v-model.number="additionalForm.historical_fact" placeholder="Из архивов" />
        </div>

        <!-- Planned completion date -->
        <div class="section-label">Плановая дата завершения</div>
        <div class="field">
          <label>Дата завершения (план)</label>
          <input type="date" v-model="additionalForm.planned_completion" />
        </div>

        <div v-if="selectedWorkType === 'СМР'" class="field" style="display:flex;align-items:center;gap:8px;margin-top:8px">
          <input type="checkbox" id="cmr_forecast" v-model="cmrForecastEnabled" style="width:auto" />
          <label for="cmr_forecast" style="margin:0">Гибридный прогноз (Mode A/B)</label>
        </div>

        <div v-if="saveError" class="error">{{ saveError }}</div>
        <div v-if="saveSuccess" class="success">Сохранено</div>

        <button class="btn-primary" @click="saveMilestones" :disabled="saving">
          {{ saving ? 'Сохранение...' : 'Сохранить' }}
        </button>
      </template>
    </div>

    <!-- Step 8: Full card editor (Missing Info flow) -->
    <div v-if="step === 8" class="step">
      <h2>Заполнение карточки проекта</h2>
      <div v-if="metrics.loading" class="loading">Загрузка...</div>
      <template v-else-if="metrics.projectCard">
        <div class="section-label">Общие данные</div>
        <div class="field">
          <label>Наименование проекта</label>
          <input type="text" v-model="editForm.project_name" />
        </div>
        <div class="field">
          <label>Руководитель проекта (РП)</label>
          <select v-model="editForm.project_manager_id">
            <option :value="null">— Не указан —</option>
            <option v-for="m in metrics.lookups?.managers" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
          <div class="add-new-row">
            <input type="text" v-model="newManagerName" placeholder="Добавить нового РП..." class="add-new-input" />
            <button class="btn-add-new" @click="addManager" :disabled="!newManagerName.trim()">+</button>
          </div>
        </div>
        <div class="field">
          <label>Заказчик</label>
          <select v-model="editForm.customer_id">
            <option :value="null">— Не указан —</option>
            <option v-for="c in metrics.lookups?.customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <div class="add-new-row">
            <input type="text" v-model="newCustomerName" placeholder="Добавить нового заказчика..." class="add-new-input" />
            <button class="btn-add-new" @click="addCustomer" :disabled="!newCustomerName.trim()">+</button>
          </div>
        </div>
        <div class="field">
          <label>Бизнес-направление (БН)</label>
          <select v-model="editForm.business_unit_id">
            <option :value="null">— Не указано —</option>
            <option v-for="b in metrics.lookups?.business_units" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
          <div class="add-new-row">
            <input type="text" v-model="newBUName" placeholder="Добавить новое БН..." class="add-new-input" />
            <button class="btn-add-new" @click="addBusinessUnit" :disabled="!newBUName.trim()">+</button>
          </div>
        </div>
        <div class="field">
          <label>Наименование 1С</label>
          <input type="text" v-model="editForm.name_1c" />
        </div>

        <div v-if="saveError" class="error">{{ saveError }}</div>
        <div v-if="saveSuccess" class="success">Сохранено</div>

        <button class="btn-primary" @click="saveFullCard" :disabled="saving">
          {{ saving ? 'Сохранение...' : 'Сохранить' }}
        </button>

        <div class="section-label" style="margin-top:16px">Настройка вех по этапам</div>
        <div class="process-buttons">
          <button v-for="wt in ['ШМР', 'ПНР', 'СМР']" :key="wt" class="btn-process" @click="goToProcessEditorDirect(wt)">
            {{ wt }}
          </button>
        </div>
      </template>
    </div>

    <div v-if="step > 1" class="step-footer">
      <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useMetricsStore } from '../stores/metrics'
import type { MetricsProjectListItem, MetricsWorkTypeData, MetricsMilestone, MilestoneEditItem } from '../types'

const router = useRouter()
const metrics = useMetricsStore()

const step = ref(1)
const prevStepBeforeMilestones = ref(5)  // tracks where Step 7 should go Back to
const currentGroup = ref('')
const isMissingInfoMode = ref(false)
const selectedProjectCode = ref('')
const selectedWorkType = ref('')
const projectSearch = ref('')
const summaryLoading = ref(false)

const saving = ref(false)
const saveError = ref('')
const saveSuccess = ref(false)

// "Add new" fields for fillable lists
const newCustomerName = ref('')
const newBUName = ref('')
const newManagerName = ref('')

const editForm = reactive({
  project_name: '',
  customer_id: null as number | null,
  business_unit_id: null as number | null,
  project_manager_id: null as number | null,
  name_1c: '',
})

// Milestone editor state
const milestoneEditList = ref<MilestoneEditItem[]>([])
const additionalForm = reactive({
  mobilization: null as number | null,
  project_activity: null as number | null,
  master_duties: null as number | null,
  historical_fact: null as number | null,
  planned_completion: '',
})
const cmrForecastEnabled = ref(false)

// ── Computed ──────────────────────────────────────────────

const bnGroupButtons = computed(() => {
  if (!metrics.bnSummary) return []
  const groups = metrics.bnSummary.groups
  const result = []
  for (const key of ['nko', 'opnvg', 'mso']) {
    if (groups[key]) {
      result.push({ key, name: groups[key].name, count: groups[key].count })
    }
  }
  return result
})

const totalMissingCount = computed(() => {
  if (!metrics.bnSummary) return 0
  return (metrics.bnSummary.missing_info?.active ?? 0) + (metrics.bnSummary.missing_info?.inactive ?? 0)
})

const currentGroupName = computed(() => {
  const nameMap: Record<string, string> = {
    nko: 'БН НКО', opnvg: 'БН ОПНВГ', mso: 'БН МСО', unsorted: 'Ошибка сортировки',
    missing_active: 'Активные проекты без информации', missing_inactive: 'Неактивные проекты без информации',
  }
  return nameMap[currentGroup.value] || 'Проекты'
})

const stepLabel = computed(() => {
  const labels: Record<number, string> = {
    2: 'Без информации', 3: 'Проекты', 4: 'Инфо', 5: selectedWorkType.value,
    6: 'Конструктор', 7: 'Конструктор', 8: 'Карточка',
  }
  return labels[step.value] || ''
})

const favoriteProjects = computed(() => metrics.projectList.filter(p => p.is_favorite))

const filteredProjects = computed(() => {
  const q = projectSearch.value.toLowerCase()
  if (!q) return metrics.projectList
  return metrics.projectList.filter(
    p => p.project_code.toLowerCase().includes(q)
      || (p.project_name && p.project_name.toLowerCase().includes(q))
  )
})

const availableWorkTypes = computed(() => {
  if (!metrics.currentProject) return []
  const cached = Object.keys(metrics.currentProject.work_types)
  const all = ['ШМР', 'ПНР', 'СМР']
  return [...cached, ...all.filter(wt => !cached.includes(wt))]
})

const selectedProcessData = computed<MetricsWorkTypeData | null>(() => {
  if (!metrics.currentProject || !selectedWorkType.value) return null
  return metrics.currentProject.work_types[selectedWorkType.value] ?? null
})

const projectActivity = computed<Record<string, { plan: number; fact: number }>>(() => {
  const aux = selectedProcessData.value?.auxiliary
  if (!aux) return {}
  const keys = ['mobilization', 'project_activity', 'master_duties']
  const result: Record<string, { plan: number; fact: number }> = {}
  for (const key of keys) {
    if (aux[key]) result[key] = aux[key]
  }
  return result
})

const additionalWorks = computed<Record<string, { plan: number; fact: number }>>(() => {
  const aux = selectedProcessData.value?.auxiliary
  if (!aux) return {}
  const keys = ['nvor', 'downtime', 'rk_comments', 'warranty', 'unaccounted']
  const result: Record<string, { plan: number; fact: number }> = {}
  for (const key of keys) {
    if (aux[key]) result[key] = aux[key]
  }
  return result
})

// ── Helpers ───────────────────────────────────────────────

function effColor(eff: number | null | undefined): string {
  if (eff == null) return ''
  if (eff >= 100) return '#2e7d32'
  if (eff >= 80) return '#e6a700'
  return '#c62828'
}

function auxLabel(key: string): string {
  const labels: Record<string, string> = {
    mobilization: 'Мобилизационные мероприятия',
    project_activity: 'Проектная деятельность',
    master_duties: 'Обязанности мастера',
    nvor: 'Работы неучтённые в ВОР',
    downtime: 'Простои',
    rk_comments: 'Устранение замечаний РК',
    warranty: 'Работы по гарантии',
    unaccounted: 'Прочие неучтённые работы',
  }
  return labels[key] || key
}

// ── Milestone confirmation ────────────────────────────────

const confirmingMilestone = ref<string | null>(null)

/** СМР: group sub-milestones by top-level name */
const cmrMilestoneGroups = computed<Record<string, { milestones: MetricsMilestone[], allConfirmed: boolean }> | null>(() => {
  if (selectedWorkType.value !== 'СМР' || !selectedProcessData.value) return null
  const groups: Record<string, { milestones: MetricsMilestone[], allConfirmed: boolean }> = {}
  for (const m of selectedProcessData.value.milestones) {
    const dashIdx = m.name.indexOf(' - ')
    const topLevel = dashIdx > -1 ? m.name.substring(0, dashIdx) : m.name
    if (!groups[topLevel]) groups[topLevel] = { milestones: [], allConfirmed: true }
    groups[topLevel].milestones.push(m)
    if (!m.is_confirmed) groups[topLevel].allConfirmed = false
  }
  return Object.keys(groups).length > 0 ? groups : null
})

async function onConfirmMilestone(name: string) {
  if (confirmingMilestone.value) return
  confirmingMilestone.value = name
  try {
    const ok = await metrics.confirmMilestone(selectedProjectCode.value, selectedWorkType.value, name)
    if (ok) await metrics.loadProject(selectedProjectCode.value)
  } finally {
    confirmingMilestone.value = null
  }
}

async function onUnconfirmMilestone(name: string) {
  if (confirmingMilestone.value) return
  confirmingMilestone.value = name
  try {
    const ok = await metrics.unconfirmMilestone(selectedProjectCode.value, selectedWorkType.value, name)
    if (ok) await metrics.loadProject(selectedProjectCode.value)
  } finally {
    confirmingMilestone.value = null
  }
}

// ── Fillable list handlers ────────────────────────────────

async function addCustomer() {
  const name = newCustomerName.value.trim()
  if (!name) return
  const result = await metrics.createLookupItem('customer', name)
  if (result) {
    editForm.customer_id = result.id
    newCustomerName.value = ''
  }
}

async function addBusinessUnit() {
  const name = newBUName.value.trim()
  if (!name) return
  const result = await metrics.createLookupItem('business-unit', name)
  if (result) {
    editForm.business_unit_id = result.id
    newBUName.value = ''
  }
}

async function addManager() {
  const name = newManagerName.value.trim()
  if (!name) return
  const result = await metrics.createLookupItem('manager', name)
  if (result) {
    editForm.project_manager_id = result.id
    newManagerName.value = ''
  }
}

// ── Milestone editor helpers ──────────────────────────────

function updateMilestoneValue(id: number, event: Event) {
  const target = event.target as HTMLInputElement
  const val = target.value === '' ? null : parseFloat(target.value)
  const m = milestoneEditList.value.find(x => x.id === id)
  if (m) m.planned_hours = val
}

function toggleMilestoneNA(id: number) {
  const m = milestoneEditList.value.find(x => x.id === id)
  if (m) {
    m.is_not_applicable = !m.is_not_applicable
    if (m.is_not_applicable) m.planned_hours = null
  }
}

// ── Step Handlers ─────────────────────────────────────────

onMounted(async () => {
  summaryLoading.value = true
  await metrics.loadBNSummary()
  summaryLoading.value = false
})

function selectBNGroup(key: string) {
  currentGroup.value = key
  isMissingInfoMode.value = false
  projectSearch.value = ''
  step.value = 3
  metrics.loadProjects(key)
}

function goToMissingInfo() { step.value = 2 }

function selectGroup(group: string) {
  currentGroup.value = group
  isMissingInfoMode.value = group.startsWith('missing_')
  projectSearch.value = ''
  step.value = 3
  metrics.loadProjects(group)
}

function selectProject(p: MetricsProjectListItem) {
  selectedProjectCode.value = p.project_code
  if (isMissingInfoMode.value) {
    step.value = 8
    loadCardForEdit(p.project_code)
  } else {
    step.value = 4
    metrics.loadProject(p.project_code)
  }
}

function selectProcess(wt: string) {
  selectedWorkType.value = wt
  step.value = 5
}

function goToGeneralEditor() {
  step.value = 6
  loadCardForEdit(selectedProjectCode.value)
}

async function goToProcessEditor() {
  prevStepBeforeMilestones.value = step.value
  step.value = 7
  // Load milestones + additional data from dedicated endpoint
  await metrics.loadMilestones(selectedProjectCode.value, selectedWorkType.value)
  if (metrics.milestonesData) {
    milestoneEditList.value = metrics.milestonesData.milestones.map(m => ({ ...m }))
    const a = metrics.milestonesData.additional
    additionalForm.mobilization = a.mobilization
    additionalForm.project_activity = a.project_activity
    additionalForm.master_duties = a.master_duties ?? null
    additionalForm.historical_fact = a.historical_fact ?? null
    additionalForm.planned_completion = a.planned_completion ?? ''
  }
  // Load CMR forecast flag from card
  if (selectedWorkType.value === 'СМР') {
    await metrics.loadProjectCard(selectedProjectCode.value)
    cmrForecastEnabled.value = !!metrics.projectCard?.cmr_forecast_enabled
  }
}

function goToProcessEditorDirect(wt: string) {
  selectedWorkType.value = wt
  goToProcessEditor()
}

async function loadCardForEdit(code: string) {
  saveError.value = ''
  saveSuccess.value = false
  newCustomerName.value = ''
  newBUName.value = ''
  newManagerName.value = ''
  await metrics.loadProjectCard(code)
  if (metrics.projectCard) {
    const c = metrics.projectCard
    editForm.project_name = c.project_name ?? ''
    editForm.customer_id = c.customer_id
    editForm.business_unit_id = c.business_unit_id
    editForm.project_manager_id = c.project_manager_id
    editForm.name_1c = c.name_1c ?? ''
  }
}

// ── Save Handlers ─────────────────────────────────────────

async function saveGeneral() {
  saving.value = true
  saveError.value = ''
  saveSuccess.value = false
  const ok = await metrics.updateProjectCard(selectedProjectCode.value, {
    project_name: editForm.project_name,
    customer_id: editForm.customer_id,
    business_unit_id: editForm.business_unit_id,
    project_manager_id: editForm.project_manager_id,
    name_1c: editForm.name_1c,
  })
  saving.value = false
  if (ok) {
    saveSuccess.value = true
    metrics.loadProject(selectedProjectCode.value)
  } else {
    saveError.value = metrics.error
  }
}

async function saveMilestones() {
  saving.value = true
  saveError.value = ''
  saveSuccess.value = false

  const body: Record<string, unknown> = {
    milestones: milestoneEditList.value.map(m => ({
      id: m.id, planned_hours: m.planned_hours, is_not_applicable: m.is_not_applicable, unit: m.unit,
    })),
    additional: {
      mobilization: additionalForm.mobilization,
      project_activity: additionalForm.project_activity,
      historical_fact: additionalForm.historical_fact,
      planned_completion: additionalForm.planned_completion || null,
      ...(selectedWorkType.value === 'ШМР' ? { master_duties: additionalForm.master_duties } : {}),
    },
  }

  const ok = await metrics.updateMilestones(selectedProjectCode.value, selectedWorkType.value, body)

  // Also save CMR forecast flag via process card endpoint
  if (ok && selectedWorkType.value === 'СМР') {
    await metrics.updateProcessCard(selectedProjectCode.value, 'СМР', {
      cmr_forecast_enabled: cmrForecastEnabled.value ? 1 : 0,
    })
  }

  saving.value = false
  if (ok) {
    saveSuccess.value = true
    metrics.loadProject(selectedProjectCode.value)
  } else {
    saveError.value = metrics.error
  }
}

async function saveFullCard() {
  saving.value = true
  saveError.value = ''
  saveSuccess.value = false

  const genOk = await metrics.updateProjectCard(selectedProjectCode.value, {
    project_name: editForm.project_name,
    customer_id: editForm.customer_id,
    business_unit_id: editForm.business_unit_id,
    project_manager_id: editForm.project_manager_id,
    name_1c: editForm.name_1c,
  })

  if (!genOk) { saving.value = false; saveError.value = metrics.error; return }

  saving.value = false
  saveSuccess.value = true
}

// ── Back Navigation ───────────────────────────────────────

function handleBack() {
  saveError.value = ''
  saveSuccess.value = false

  switch (step.value) {
    case 1: router.push('/'); break
    case 2: step.value = 1; break
    case 3:
      if (isMissingInfoMode.value) step.value = 2
      else step.value = 1
      metrics.projectList = []
      projectSearch.value = ''
      break
    case 4: step.value = 3; metrics.currentProject = null; break
    case 5: step.value = 4; selectedWorkType.value = ''; break
    case 6: step.value = 4; break
    case 7: step.value = prevStepBeforeMilestones.value; break
    case 8: step.value = 3; break
  }
}
</script>

<style scoped>
.bn-btn { font-weight: 600; }
.bn-count {
  background: var(--bg); border-radius: 12px; padding: 2px 10px;
  font-size: 0.85rem; color: var(--text-secondary); font-weight: 600;
}
.btn-missing-info {
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; padding: 14px 16px; background: #fff3e0; border: 1px solid #ffe0b2;
  border-radius: var(--radius); font-size: 0.9375rem; font-weight: 500;
  text-align: left; cursor: pointer; margin-top: 16px; transition: border-color 0.15s;
}
.btn-missing-info:hover { border-color: #ff9800; }

.project-info { flex: 1; min-width: 0; }
.project-code { font-weight: 600; }
.project-name { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.project-eff { font-size: 0.8rem; font-weight: 600; margin-top: 2px; }

.wt-summary-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 8px; overflow: hidden; }
.wt-summary-header { background: var(--bg); padding: 8px 12px; font-weight: 700; font-size: 0.875rem; border-bottom: 1px solid var(--border); }
.wt-summary-body { display: flex; gap: 0; padding: 0; }
.wt-stat { flex: 1; text-align: center; padding: 10px 6px; border-right: 1px solid var(--border); }
.wt-stat:last-child { border-right: none; }
.wt-stat-label { display: block; font-size: 0.7rem; color: var(--text-secondary); margin-bottom: 2px; }
.wt-stat-value { font-weight: 700; font-size: 0.9rem; }

.process-buttons { display: flex; gap: 8px; margin-top: 12px; }
.btn-process {
  flex: 1; padding: 12px; background: var(--primary); color: #fff;
  border: none; border-radius: var(--radius); font-size: 0.9375rem;
  font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.btn-process:hover { background: var(--primary-dark); }

.milestone-table { display: flex; flex-direction: column; gap: 8px; }
.milestone-row { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; }
.milestone-name { font-weight: 600; font-size: 0.875rem; margin-bottom: 6px; }
.milestone-stats { display: flex; gap: 4px; margin-bottom: 6px; }
.milestone-stat { flex: 1; text-align: center; }
.milestone-stat-label { display: block; font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; }
.milestone-stat-value { font-weight: 700; font-size: 0.8rem; }
.milestone-bar-track { height: 4px; background: var(--border); border-radius: 2px; overflow: hidden; }
.milestone-bar-fill { height: 100%; border-radius: 2px; transition: width 0.3s; }

/* Milestone confirmation */
.milestone-confirmed { border-left: 3px solid #2e7d32; }
.confirmed-badge { color: #2e7d32; font-size: 0.7rem; font-weight: 400; margin-left: 6px; }
.milestone-confirm-actions { margin-top: 6px; }
.btn-confirm { background: none; border: 1px solid #2e7d32; color: #2e7d32; border-radius: 6px; padding: 4px 10px; font-size: 0.75rem; cursor: pointer; }
.btn-confirm:disabled { opacity: 0.5; cursor: wait; }
.btn-unconfirm { background: none; border: 1px solid #999; color: #999; border-radius: 6px; padding: 4px 10px; font-size: 0.75rem; cursor: pointer; }
.btn-unconfirm:disabled { opacity: 0.5; cursor: wait; }
.cmr-group { margin-bottom: 4px; }
.cmr-group-header { font-weight: 600; font-size: 0.9rem; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius) var(--radius) 0 0; }
.cmr-group + .cmr-group .cmr-group-header { border-radius: var(--radius) var(--radius) 0 0; }
.cmr-sub-milestone { border-radius: 0; border-top: none; padding-left: 20px; }
.cmr-sub-milestone:last-child { border-radius: 0 0 var(--radius) var(--radius); }

/* Auxiliary work */
.auxiliary-table { display: flex; flex-direction: column; gap: 6px; }
.auxiliary-row { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; }
.auxiliary-name { font-weight: 600; font-size: 0.85rem; margin-bottom: 4px; }
.auxiliary-stats { display: flex; gap: 16px; }
.auxiliary-stat { text-align: center; }
.auxiliary-stat-label { display: block; font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; }
.auxiliary-stat-value { font-weight: 700; font-size: 0.8rem; }

/* Fillable list add-new */
.add-new-row { display: flex; gap: 6px; margin-top: 4px; }
.add-new-input { flex: 1; padding: 6px 10px; font-size: 0.8125rem; border: 1px dashed var(--border); border-radius: var(--radius); background: var(--bg); }
.btn-add-new {
  padding: 6px 12px; background: var(--primary); color: #fff; border: none;
  border-radius: var(--radius); font-size: 1rem; font-weight: 700; cursor: pointer;
  min-width: 36px; transition: opacity 0.15s;
}
.btn-add-new:disabled { opacity: 0.4; cursor: default; }

/* Milestone editor */
.milestone-edit-list { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.milestone-edit-row {
  display: flex; flex-direction: column; gap: 4px; padding: 10px 12px;
  background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
}
.milestone-edit-row.milestone-na { opacity: 0.5; }
.milestone-edit-name { font-weight: 600; font-size: 0.85rem; }
.milestone-edit-controls { display: flex; align-items: center; gap: 8px; }
.milestone-edit-input {
  width: 80px; padding: 6px 8px; border: 1px solid var(--border);
  border-radius: var(--radius); font-size: 0.875rem; text-align: center;
}
.milestone-edit-input:disabled { background: var(--bg); color: var(--text-secondary); }
.milestone-edit-unit { font-size: 0.8rem; color: var(--text-secondary); min-width: 24px; }
.milestone-na-toggle { display: flex; align-items: center; gap: 4px; margin-left: auto; cursor: pointer; }
.milestone-na-toggle input { width: auto; }
.na-label { font-size: 0.75rem; color: var(--text-secondary); white-space: nowrap; }
.historical-fact-note .label { font-style: italic; color: var(--text-secondary); font-size: 0.8rem; }
.historical-fact-note .value { font-style: italic; color: var(--text-secondary); font-size: 0.8rem; }
</style>
