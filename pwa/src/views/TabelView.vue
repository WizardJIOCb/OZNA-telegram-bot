<template>
  <div class="page">
    <header class="app-header">
      <button class="btn-back" @click="handleBack">&larr;</button>
      <h1>Табель</h1>
      <span class="step-indicator">{{ step }}/{{ totalSteps }}</span>
    </header>

    <!-- Step 1: Work Type (FN) -->
    <div v-if="step === 1" class="step">
      <h2>Выберите направление</h2>
      <div class="option-list">
        <button
          v-for="node in tabel.activityTree"
          :key="node.key"
          class="btn-option"
          @click="selectWorkType(node)"
        >
          {{ node.label }}
        </button>
      </div>
    </div>

    <!-- ABSENCE FLOW: Step 2a - select reason -->
    <div v-if="step === 2 && isAbsenceFlow" class="step">
      <h2>Выберите тип отсутствия</h2>
      <div class="option-list">
        <button
          v-for="node in (selectedWorkType?.children || [])"
          :key="node.key"
          class="btn-option btn-leaf"
          @click="selectAbsenceType(node)"
        >
          {{ node.label }}
        </button>
      </div>
      <div class="step-footer">
        <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
      </div>
    </div>

    <!-- ABSENCE FLOW: Step 3a - date range -->
    <div v-if="step === 3 && isAbsenceFlow" class="step">
      <h2>{{ isSingleDateAbsence ? 'Укажите дату' : 'Укажите период' }}</h2>
      <div class="field">
        <label>{{ isSingleDateAbsence ? 'Дата' : 'Дата начала' }}</label>
        <input type="date" v-model="absenceStartDate" />
      </div>
      <div v-if="!isSingleDateAbsence" class="field">
        <label>Дата окончания</label>
        <input type="date" v-model="absenceEndDate" />
      </div>
      <div v-if="!isSingleDateAbsence && absenceDayCount > 0" class="info-text">
        {{ absenceDayCount }} дн.
      </div>
      <div v-if="absenceDateError" class="error">{{ absenceDateError }}</div>
      <button class="btn-primary" @click="confirmAbsenceDates" :disabled="!absenceDatesValid">
        Далее
      </button>
      <div class="step-footer">
        <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
      </div>
    </div>

    <!-- ABSENCE FLOW: Step 4a - confirm -->
    <div v-if="step === 4 && isAbsenceFlow" class="step">
      <h2>Подтвердите запись</h2>
      <div class="confirm-card">
        <div class="confirm-row">
          <span class="label">Тип</span>
          <span class="value">{{ selectedActivityLabel }}</span>
        </div>
        <div class="confirm-row">
          <span class="label">Период</span>
          <span class="value" v-if="isSingleDateAbsence">{{ absenceStartFormatted }}</span>
          <span class="value" v-else>{{ absenceStartFormatted }} — {{ absenceEndFormatted }} ({{ absenceDayCount }} дн.)</span>
        </div>
      </div>

      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="successMsg" class="success">{{ successMsg }}</div>

      <div class="confirm-buttons">
        <button class="btn-primary" @click="submitAbsence" :disabled="submitting">
          {{ submitting ? 'Сохранение...' : 'Подтвердить' }}
        </button>
        <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
      </div>
    </div>

    <!-- REGULAR FLOW: Step 2 - Date -->
    <div v-if="step === 2 && !isAbsenceFlow" class="step">
      <h2>Выберите дату</h2>
      <div class="date-buttons">
        <button class="btn-option" @click="selectDate(todayStr)">
          Сегодня ({{ todayStr }})
        </button>
        <button class="btn-option" @click="selectDate(yesterdayStr)">
          Вчера ({{ yesterdayStr }})
        </button>
        <button class="btn-option" @click="selectDate(dayBeforeYesterdayStr)">
          Позавчера ({{ dayBeforeYesterdayStr }})
        </button>
      </div>
      <div class="field">
        <label>Или введите дату</label>
        <input type="date" v-model="customDate" @change="selectDateFromInput" />
      </div>
      <div class="step-footer">
        <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
      </div>
    </div>

    <!-- REGULAR FLOW: Step 3 - Project -->
    <div v-if="step === 3 && !isAbsenceFlow && !skipProject" class="step">
      <h2>Выберите проект</h2>
      <div class="field">
        <input
          type="text"
          v-model="projectSearch"
          placeholder="Поиск проекта..."
          class="search-input"
        />
      </div>

      <div v-if="!projectSearch && personalProjects.length > 0">
        <div class="section-label">Ваши проекты</div>
        <div class="option-list">
          <button
            v-for="p in personalProjects"
            :key="'star-' + p"
            class="btn-option btn-project"
            @click="selectProject(p)"
          >
            <span>{{ p }}</span>
            <span
              class="star-icon"
              :class="{ active: tabel.isStarred(p) }"
              @click.stop="tabel.toggleStarProject(p)"
            >{{ tabel.isStarred(p) ? '&#9733;' : '&#9734;' }}</span>
          </button>
        </div>
        <div class="section-label">Все проекты</div>
      </div>

      <div class="option-list">
        <button
          v-for="p in filteredProjects"
          :key="p"
          class="btn-option btn-project"
          @click="selectProject(p)"
        >
          <span>{{ p }}</span>
          <span
            class="star-icon"
            :class="{ active: tabel.isStarred(p) }"
            @click.stop="tabel.toggleStarProject(p)"
          >{{ tabel.isStarred(p) ? '&#9733;' : '&#9734;' }}</span>
        </button>
      </div>
      <div v-if="filteredProjects.length === 0" class="empty">
        Проекты не найдены
      </div>
      <div class="step-footer">
        <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
      </div>
    </div>

    <!-- REGULAR FLOW: Step 4 - Activity -->
    <div v-if="step === 4 && !isAbsenceFlow" class="step">
      <h2>Выберите тип работ</h2>
      <div v-if="breadcrumbs.length > 0" class="breadcrumbs">
        <button class="btn-link" @click="goToBreadcrumb(-1)">{{ selectedWorkType?.label || 'Все' }}</button>
        <span v-for="(b, i) in breadcrumbs" :key="i">
          &rsaquo;
          <button class="btn-link" @click="goToBreadcrumb(i)">{{ b.label }}</button>
        </span>
      </div>
      <div class="option-list">
        <button
          v-for="node in currentNodes"
          :key="node.key"
          class="btn-option"
          :class="{ 'btn-leaf': node.leaf }"
          @click="selectActivity(node)"
        >
          {{ node.label }}
          <span v-if="!node.leaf && node.children" class="chevron">&rsaquo;</span>
        </button>
      </div>
      <div class="step-footer">
        <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
      </div>
    </div>

    <!-- REGULAR FLOW: Step 5 - Details OR Claims -->
    <div v-if="step === 5 && !isAbsenceFlow" class="step">

      <!-- WARRANTY: Claims list -->
      <template v-if="isWarranty">
        <template v-if="!creatingClaim && !selectedClaim">
          <h2>Работы по гарантии</h2>
          <div v-if="claimsLoading" class="loading">Загрузка рекламаций...</div>
          <div v-else>
            <div v-if="claims.length > 0" class="option-list">
              <button
                v-for="c in claims"
                :key="c.id"
                class="btn-option"
                style="flex-direction:column;align-items:flex-start;gap:2px"
                @click="selectClaim(c)"
              >
                <div style="font-weight:600">{{ c.status === 'active' ? '✅' : '⏳' }} {{ c.subject }}</div>
                <div style="font-size:0.8rem;color:var(--text-secondary)">
                  {{ c.equipment }}<span v-if="c.serial_number && c.serial_number !== '-'"> · {{ c.serial_number }}</span>
                </div>
                <div v-if="c.reg_number && c.reg_number !== '-'" style="font-size:0.8rem;color:var(--text-secondary)">
                  Рег. № {{ c.reg_number }} · {{ c.act_date }}
                </div>
              </button>
            </div>
            <div v-else class="empty">
              Нет активных рекламаций для {{ entry.project_code }}
            </div>
            <button class="btn-primary" style="margin-top: 12px" @click="creatingClaim = true">
              + Добавить рекламацию
            </button>
          </div>
          <div class="step-footer">
            <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
          </div>
        </template>

        <!-- WARRANTY: Claim selected - go to confirmation -->
        <template v-if="!creatingClaim && !!selectedClaim">
          <h2>Рекламация выбрана</h2>
          <div class="confirm-card">
            <div class="confirm-row">
              <span class="label">Тема</span>
              <span class="value">{{ selectedClaim.subject }}</span>
            </div>
            <div class="confirm-row">
              <span class="label">Оборудование</span>
              <span class="value">{{ selectedClaim.equipment }}</span>
            </div>
            <div v-if="selectedClaim.serial_number && selectedClaim.serial_number !== '-'" class="confirm-row">
              <span class="label">Зав. номер</span>
              <span class="value">{{ selectedClaim.serial_number }}</span>
            </div>
            <div v-if="selectedClaim.reg_number && selectedClaim.reg_number !== '-'" class="confirm-row">
              <span class="label">Рег. номер</span>
              <span class="value">{{ selectedClaim.reg_number }}</span>
            </div>
            <div v-if="selectedClaim.act_date" class="confirm-row">
              <span class="label">Дата акта</span>
              <span class="value">{{ selectedClaim.act_date }}</span>
            </div>
            <div v-if="selectedClaim.act_author" class="confirm-row">
              <span class="label">Автор</span>
              <span class="value">{{ selectedClaim.act_author }}</span>
            </div>
            <div v-if="selectedClaim.description" class="confirm-row">
              <span class="label">Описание</span>
              <span class="value">{{ selectedClaim.description }}</span>
            </div>
            <div class="confirm-row">
              <span class="label">Статус</span>
              <span class="value">{{ selectedClaim.status === 'active' ? 'Активна' : 'Ожидает модерации' }}</span>
            </div>
          </div>
          <button class="btn-primary" @click="goToConfirm">Далее</button>
          <div class="step-footer">
            <button class="btn-secondary" @click="deselectClaim">&larr; Назад</button>
          </div>
        </template>

        <!-- WARRANTY: Create claim form -->
        <template v-if="creatingClaim">
          <h2>Новая рекламация</h2>
          <div class="field">
            <label>Тема рекламации *</label>
            <input v-model="claimForm.subject" type="text" placeholder="Краткое описание проблемы" />
          </div>
          <div class="field">
            <label>Оборудование *</label>
            <input v-model="claimForm.equipment" type="text" placeholder="Название оборудования" />
          </div>
          <div class="field">
            <label>Заводской номер</label>
            <input v-model="claimForm.serial_number" type="text" placeholder="Серийный / заводской номер" />
          </div>
          <div class="field">
            <label>Описание</label>
            <input v-model="claimForm.description" type="text" placeholder="Подробное описание" />
          </div>
          <div v-if="claimError" class="error">{{ claimError }}</div>
          <button
            class="btn-primary"
            @click="submitClaim"
            :disabled="!claimForm.subject.trim() || !claimForm.equipment.trim() || claimSubmitting"
          >
            {{ claimSubmitting ? 'Создание...' : 'Создать рекламацию' }}
          </button>
          <div class="step-footer">
            <button class="btn-secondary" @click="creatingClaim = false">&larr; К списку рекламаций</button>
          </div>
        </template>
      </template>

      <!-- REGULAR: Details -->
      <template v-else>
        <!-- MILESTONE SURVEY (blocking) -->
        <template v-if="milestoneSurveyLoading">
          <div class="loading">Проверка вехи...</div>
        </template>
        <template v-else-if="milestoneSurvey?.needs_survey && !milestoneSurveyDone && milestoneSurvey.previous_percent < 100">
          <h2>Оценка выполнения вехи</h2>
          <div class="confirm-card">
            <div class="confirm-row">
              <span class="label">Веха</span>
              <span class="value">{{ milestoneSurvey.milestone_name }}</span>
            </div>
            <div class="confirm-row">
              <span class="label">Тип</span>
              <span class="value">{{ milestoneSurvey.work_type }}</span>
            </div>
            <div v-if="milestoneSurvey.plan_hours !== null" class="confirm-row">
              <span class="label">План</span>
              <span class="value">{{ (milestoneSurvey.plan_hours / 8).toFixed(1) }} дн.</span>
            </div>
            <div class="confirm-row">
              <span class="label">Факт</span>
              <span class="value">{{ (milestoneSurvey.fact_hours / 8).toFixed(1) }} дн.</span>
            </div>
            <div v-if="milestoneSurvey.previous_percent > 0" class="confirm-row">
              <span class="label">Пред. оценка</span>
              <span class="value">{{ milestoneSurvey.previous_percent }}% ({{ milestoneSurvey.previous_estimator }}, {{ milestoneSurvey.previous_date }})</span>
            </div>
            <div v-if="milestoneSurvey.efficiency !== null" class="confirm-row">
              <span class="label">Эффективность вехи</span>
              <span class="value" :style="{ color: milestoneSurvey.efficiency >= 100 ? 'var(--success)' : milestoneSurvey.efficiency >= 80 ? '#e6a700' : 'var(--error)' }">
                {{ milestoneSurvey.efficiency }}%
              </span>
            </div>
            <div v-if="milestoneSurvey.total_efficiency !== null" class="confirm-row">
              <span class="label">Эффективность {{ milestoneSurvey.work_type }}</span>
              <span class="value" :style="{ color: milestoneSurvey.total_efficiency >= 100 ? 'var(--success)' : milestoneSurvey.total_efficiency >= 80 ? '#e6a700' : 'var(--error)' }">
                {{ milestoneSurvey.total_efficiency }}%
              </span>
            </div>
            <div v-if="milestoneSurvey.motivational" class="confirm-row">
              <span class="label">Статус</span>
              <span class="value">{{ milestoneSurvey.motivational.emoji }} {{ milestoneSurvey.motivational.message }}</span>
            </div>
          </div>
          <div class="field">
            <label>Процент выполнения вехи (0-100)</label>
            <input v-model.number="milestonePercent" type="number" min="0" max="100" step="1"
              :placeholder="milestoneSurvey.previous_percent > 0 ? `Мин: ${milestoneSurvey.previous_percent}` : '0'" />
          </div>
          <div v-if="milestoneError" class="error">{{ milestoneError }}</div>
          <button class="btn-primary" @click="submitMilestoneSurvey" :disabled="milestoneSubmitting">
            {{ milestoneSubmitting ? 'Сохранение...' : 'Сохранить оценку' }}
          </button>
          <div class="step-footer">
            <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
          </div>
        </template>

        <!-- MILESTONE SURVEY DONE -->
        <template v-else-if="milestoneSurveyDone && milestoneResult">
          <h2>{{ milestoneResult.is_complete ? 'Веха завершена!' : 'Оценка сохранена' }}</h2>
          <div class="confirm-card">
            <div class="confirm-row">
              <span class="label">Веха</span>
              <span class="value">{{ milestoneSurvey?.milestone_name }}</span>
            </div>
            <div class="confirm-row">
              <span class="label">Выполнение</span>
              <span class="value">{{ milestonePercent }}%</span>
            </div>
            <div v-if="milestoneResult.efficiency > 0" class="confirm-row">
              <span class="label">Эффективность вехи</span>
              <span class="value" :style="{ color: milestoneResult.efficiency >= 100 ? 'var(--success)' : milestoneResult.efficiency >= 80 ? '#e6a700' : 'var(--error)' }">
                {{ milestoneResult.efficiency }}%
              </span>
            </div>
            <div v-if="milestoneSurvey?.total_efficiency !== null" class="confirm-row">
              <span class="label">Эффективность {{ milestoneSurvey?.work_type }}</span>
              <span class="value" :style="{ color: (milestoneSurvey?.total_efficiency ?? 0) >= 100 ? 'var(--success)' : (milestoneSurvey?.total_efficiency ?? 0) >= 80 ? '#e6a700' : 'var(--error)' }">
                {{ milestoneSurvey?.total_efficiency }}%
              </span>
            </div>
            <div v-if="milestoneResult.motivational" class="confirm-row">
              <span class="label">Статус</span>
              <span class="value">{{ milestoneResult.motivational.emoji }} {{ milestoneResult.motivational.message }}</span>
            </div>
          </div>
          <p v-if="milestoneResult.is_complete" style="color: var(--success); font-weight: 600; text-align: center;">
            Запись о завершении вехи создана автоматически
          </p>
          <!-- Continue to regular details -->
          <template v-if="!isMilestoneConfirmation">
          <h2 v-if="needsCustomInput">Дополнительная информация</h2>
          <h2 v-else>Укажите детали</h2>

          <div v-if="needsCustomInput" class="field">
            <label>{{ customInputLabel }}</label>
            <input v-model="customDescription" type="text" :placeholder="customInputLabel" />
          </div>

          <div class="field">
            <label>Количество часов</label>
            <input v-model.number="hours" type="number" min="0.5" max="24" step="0.5" placeholder="8" />
          </div>

          <button class="btn-primary" @click="goToConfirm" :disabled="!canProceed">
            Далее
          </button>
          </template>
          <template v-else>
          <div class="milestone-confirm-notice" style="margin-top: 12px">
            Подтверждение вехи означает завершение всех работ по данному этапу.
          </div>
          <button class="btn-primary" @click="goToConfirm">
            Подтвердить завершение этапа
          </button>
          </template>
          <div class="step-footer">
            <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
          </div>
        </template>

        <!-- REGULAR DETAILS (no survey needed) -->
        <template v-else>
          <!-- MILESTONE CONFIRMATION: no hours, show warning -->
          <template v-if="isMilestoneConfirmation">
            <div class="milestone-confirm-warning">
              <h2>Подтверждение вехи</h2>
              <div class="confirm-card">
                <div class="confirm-row">
                  <span class="label">Проект</span>
                  <span class="value">{{ entry.project_code }}</span>
                </div>
                <div class="confirm-row">
                  <span class="label">Работа</span>
                  <span class="value">{{ selectedActivityLabel }}</span>
                </div>
              </div>
              <div class="milestone-confirm-notice">
                Подтверждение вехи означает завершение всех работ по данному этапу. После подтверждения прогресс будет установлен на 100%.
              </div>
            </div>
            <button class="btn-primary" @click="goToConfirm">
              Подтвердить завершение этапа
            </button>
            <div class="step-footer">
              <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
            </div>
          </template>

          <!-- REGULAR: hours + custom input -->
          <template v-else>
          <h2 v-if="needsCustomInput">Дополнительная информация</h2>
          <h2 v-else>Укажите детали</h2>

          <div v-if="idleReasonAutoSet && customDescription" class="confirm-card" style="margin-bottom: 12px">
            <div class="confirm-row">
              <span class="label">Причина простоя</span>
              <span class="value">{{ customDescription }}</span>
            </div>
          </div>

          <div v-if="needsCustomInput" class="field">
            <label>{{ customInputLabel }}</label>
            <input v-model="customDescription" type="text" :placeholder="customInputLabel" />
          </div>

          <div class="field">
            <label>Количество часов</label>
            <input v-model.number="hours" type="number" min="0.5" max="24" step="0.5" placeholder="8" />
          </div>

          <button class="btn-primary" @click="goToConfirm" :disabled="!canProceed">
            Далее
          </button>
          <div class="step-footer">
            <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
          </div>
          </template>
        </template>
      </template>
    </div>

    <!-- REGULAR FLOW: Step 6 - Confirm -->
    <div v-if="step === 6 && !isAbsenceFlow" class="step">
      <h2>Подтвердите запись</h2>
      <div class="confirm-card">
        <div class="confirm-row">
          <span class="label">Направление</span>
          <span class="value">{{ selectedWorkType?.label || '' }}</span>
        </div>
        <div class="confirm-row">
          <span class="label">Дата</span>
          <span class="value">{{ entry.date }}</span>
        </div>
        <div v-if="entry.project_code" class="confirm-row">
          <span class="label">Проект</span>
          <span class="value">{{ entry.project_code }}</span>
        </div>
        <div v-if="milestoneSurvey?.milestone_name" class="confirm-row">
          <span class="label">Веха</span>
          <span class="value">{{ milestoneSurvey.milestone_name }}</span>
        </div>
        <div class="confirm-row">
          <span class="label">Работа</span>
          <span class="value">{{ selectedActivityLabel }}</span>
        </div>
        <template v-if="isWarranty && selectedClaim">
          <div class="confirm-row">
            <span class="label">Рекламация</span>
            <span class="value">{{ selectedClaim.subject }}</span>
          </div>
          <div class="confirm-row">
            <span class="label">Оборудование</span>
            <span class="value">{{ selectedClaim.equipment }}</span>
          </div>
          <div v-if="selectedClaim.serial_number && selectedClaim.serial_number !== '-'" class="confirm-row">
            <span class="label">Серийный номер</span>
            <span class="value">{{ selectedClaim.serial_number }}</span>
          </div>
          <div v-if="selectedClaim.reg_number && selectedClaim.reg_number !== '-'" class="confirm-row">
            <span class="label">Рег. номер</span>
            <span class="value">{{ selectedClaim.reg_number }}</span>
          </div>
        </template>
        <div v-if="customDescription && !isWarranty" class="confirm-row">
          <span class="label">{{ entry.activity_type.includes('Простой') ? 'Причина простоя' : 'Описание' }}</span>
          <span class="value">{{ customDescription }}</span>
        </div>
        <div v-if="hours" class="confirm-row">
          <span class="label">Часы</span>
          <span class="value">{{ hours }}</span>
        </div>
      </div>

      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="successMsg" class="success">{{ successMsg }}</div>

      <!-- WARRANTY: Two-button actions -->
      <template v-if="isWarranty && selectedClaim">
        <div class="confirm-buttons">
          <!-- After clicking "Отметить в табель": show hours input -->
          <template v-if="warrantyHoursMode && !tabelSubmitted">
            <div class="field" style="margin-bottom:12px">
              <label>Количество часов</label>
              <input v-model.number="hours" type="number" min="0.5" max="24" step="0.5" placeholder="8" />
            </div>
            <button class="btn-primary" @click="submitEntry" :disabled="submitting">
              {{ submitting ? 'Сохранение...' : 'Подтвердить запись' }}
            </button>
          </template>
          <!-- Initial state: show "Отметить в табель" button -->
          <template v-else>
            <button class="btn-primary" @click="warrantyHoursMode = true" :disabled="tabelSubmitted">
              {{ tabelSubmitted ? '✅ Записано в табель' : 'Отметить в табель' }}
            </button>
          </template>
          <div v-if="claimCloseError" class="error">{{ claimCloseError }}</div>
          <button
            class="btn-logout"
            style="color:var(--primary);border-color:var(--primary);margin-top:0"
            @click="requestCloseClaim"
            :disabled="claimCloseSubmitted || claimCloseSubmitting"
          >
            {{ claimCloseSubmitted ? '✅ Заявка на закрытие отправлена' : (claimCloseSubmitting ? 'Отправка...' : 'Закрыть рекламацию') }}
          </button>
          <button v-if="tabelSubmitted || claimCloseSubmitted" class="btn-primary" style="margin-top:8px" @click="resetForm">
            Готово
          </button>
          <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
        </div>
      </template>

      <!-- REGULAR: Single confirm button -->
      <template v-else>
        <div class="confirm-buttons">
          <button class="btn-primary" @click="submitEntry" :disabled="submitting">
            {{ submitting ? 'Сохранение...' : 'Подтвердить' }}
          </button>
          <button class="btn-secondary" @click="handleBack">&larr; Назад</button>
        </div>
      </template>

      <!-- Milestone info panel (reference data) -->
      <div v-if="milestoneSurvey?.milestone_name" class="milestone-info-panel">
        <div class="milestone-info-header">Справка по вехе</div>
        <div class="milestone-info-row">
          <span class="milestone-info-label">Эффективность вехи</span>
          <span class="milestone-info-value" v-if="milestoneInfoEfficiency !== null"
            :style="{ color: milestoneInfoEfficiency >= 100 ? 'var(--success)' : milestoneInfoEfficiency >= 80 ? '#e6a700' : 'var(--error)' }">
            {{ milestoneInfoEfficiency }}%
          </span>
          <span class="milestone-info-value milestone-info-nodata" v-else>нет данных</span>
        </div>
        <div class="milestone-info-row">
          <span class="milestone-info-label">Эффективность {{ milestoneSurvey.work_type }}</span>
          <span class="milestone-info-value" v-if="milestoneSurvey.total_efficiency !== null"
            :style="{ color: milestoneSurvey.total_efficiency >= 100 ? 'var(--success)' : milestoneSurvey.total_efficiency >= 80 ? '#e6a700' : 'var(--error)' }">
            {{ milestoneSurvey.total_efficiency }}%
          </span>
          <span class="milestone-info-value milestone-info-nodata" v-else>нет данных</span>
        </div>
        <div class="milestone-info-row">
          <span class="milestone-info-label">Прогресс вехи</span>
          <span class="milestone-info-value">{{ milestoneInfoProgress }}%</span>
        </div>
        <div class="milestone-info-row">
          <span class="milestone-info-label">Последняя оценка</span>
          <span class="milestone-info-value">{{ milestoneInfoEstimator }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTabelStore } from '../stores/tabel'
import { useAuthStore } from '../stores/auth'
import { useLocationStore } from '../stores/location'
import type { ActivityNode, Claim } from '../types'

const router = useRouter()
const tabel = useTabelStore()
const auth = useAuthStore()
const locationStore = useLocationStore()

function captureLocationIfEnabled(projectCode?: string) {
  if (auth.user?.share_location === false) return
  locationStore.capturePosition().then(pos => {
    if (pos) locationStore.saveLocation(pos.latitude, pos.longitude, pos.accuracy, projectCode)
  })
}

const step = ref(1)
const entry = ref({ date: '', project_code: '', activity_type: '' })
const selectedWorkType = ref<ActivityNode | null>(null)
const projectSearch = ref('')
const customDate = ref('')
const hours = ref<number | null>(null)
const customDescription = ref('')
const submitting = ref(false)
const error = ref('')
const successMsg = ref('')

// Activity navigation
const breadcrumbs = ref<ActivityNode[]>([])
const currentNodes = ref<ActivityNode[]>([])
const selectedActivityLabel = ref('')

// Idle (Простой) subtree handling
const IDLE_ROOT_KEYS = ['Простой', 'ШМ_Простой', 'МобБриг_Простой']
const idleReasonAutoSet = ref(false)

function stripEmoji(text: string): string {
  return text.replace(/[\p{Emoji_Presentation}\p{Extended_Pictographic}\uFE0F\u200D]/gu, '').trim()
}

function getIdleRoot(): ActivityNode | undefined {
  return breadcrumbs.value.find(b => IDLE_ROOT_KEYS.includes(b.key))
}

function composeIdleReason(node: ActivityNode): string {
  const idleRoot = getIdleRoot()
  if (!idleRoot) return ''
  const rootIndex = breadcrumbs.value.indexOf(idleRoot)
  const pathAfterRoot = breadcrumbs.value.slice(rootIndex + 1)
  if (pathAfterRoot.length === 0) return stripEmoji(node.label)
  const category = stripEmoji(pathAfterRoot[0]!.label)
  const subcategories = [
    ...pathAfterRoot.slice(1).map(b => stripEmoji(b.label)),
    stripEmoji(node.label)
  ]
  return `${category} (${subcategories.join(', ')})`
}

// Absence flow
const isAbsenceFlow = computed(() => selectedWorkType.value?.key === 'absence')
const isSingleDateAbsence = computed(() => entry.value.activity_type === 'Донорский день')
const absenceStartDate = ref('')
const absenceEndDate = ref('')

const absenceStartFormatted = computed(() => {
  if (!absenceStartDate.value) return ''
  const p = absenceStartDate.value.split('-')
  return `${p[2]}.${p[1]}.${p[0]}`
})

const absenceEndFormatted = computed(() => {
  if (!absenceEndDate.value) return ''
  const p = absenceEndDate.value.split('-')
  return `${p[2]}.${p[1]}.${p[0]}`
})

const absenceDayCount = computed(() => {
  if (!absenceStartDate.value) return 0
  if (isSingleDateAbsence.value) return 1
  if (!absenceEndDate.value) return 0
  const s = new Date(absenceStartDate.value)
  const e = new Date(absenceEndDate.value)
  if (e < s) return 0
  return Math.round((e.getTime() - s.getTime()) / 86400000) + 1
})

const absenceDateError = computed(() => {
  if (isSingleDateAbsence.value) return ''
  if (!absenceStartDate.value || !absenceEndDate.value) return ''
  if (absenceDayCount.value === 0) return 'Дата окончания не может быть раньше начала'
  if (absenceDayCount.value > 60) return 'Максимальный период — 60 дней'
  return ''
})

const absenceDatesValid = computed(() => {
  if (isSingleDateAbsence.value) return !!absenceStartDate.value
  return absenceDayCount.value > 0 && absenceDayCount.value <= 60 && !absenceDateError.value
})

// Claims (warranty)
const claims = ref<Claim[]>([])
const claimsLoading = ref(false)
const creatingClaim = ref(false)
const claimSubmitting = ref(false)
const claimError = ref('')
const selectedClaim = ref<Claim | null>(null)
const claimForm = ref({
  subject: '',
  equipment: '',
  serial_number: '',
  description: '',
})

// Warranty keys
const WARRANTY_KEYS = ['pnr_warranty', 'smr_warranty']
const warrantyOriginalKey = ref('')
const isWarranty = computed(() => {
  const key = warrantyOriginalKey.value
  return WARRANTY_KEYS.includes(key) || key.endsWith('_РабПоГарантии')
})
const tabelSubmitted = ref(false)
const claimCloseSubmitted = ref(false)
const claimCloseSubmitting = ref(false)
const claimCloseError = ref('')
const warrantyHoursMode = ref(false)

// Milestone survey
import type { MilestoneSurveyData, MilestoneEstimateResult } from '../types'
const milestoneSurvey = ref<MilestoneSurveyData | null>(null)
const milestoneSurveyLoading = ref(false)
const milestonePercent = ref<number | null>(null)
const milestoneSubmitting = ref(false)
const milestoneSurveyDone = ref(false)
const milestoneError = ref('')
const milestoneResult = ref<MilestoneEstimateResult | null>(null)

// Milestone info panel computed values for step 6
const milestoneInfoEfficiency = computed(() => {
  if (milestoneSurveyDone.value && milestoneResult.value) {
    return milestoneResult.value.efficiency > 0 ? milestoneResult.value.efficiency : null
  }
  return milestoneSurvey.value?.efficiency ?? null
})

const milestoneInfoProgress = computed(() => {
  if (milestoneSurveyDone.value && milestonePercent.value !== null) {
    return milestonePercent.value
  }
  return milestoneSurvey.value?.previous_percent ?? 0
})

const milestoneInfoEstimator = computed(() => {
  if (milestoneSurveyDone.value) {
    return auth.user?.fio ?? 'Вы'
  }
  const name = milestoneSurvey.value?.previous_estimator
  const date = milestoneSurvey.value?.previous_date
  if (name && date) return `${name}, ${date}`
  if (name) return name
  return 'нет данных'
})

// Whether project step should be skipped
const skipProject = computed(() => {
  if (!selectedWorkType.value) return false
  return selectedWorkType.value.key === 'current_activity'
})

// Total steps
const totalSteps = computed(() => {
  if (isAbsenceFlow.value) return 4
  if (skipProject.value) return 5
  return 6
})

// Special activity type flags
const needsCustomInput = computed(() => {
  const key = entry.value.activity_type
  if (key.includes('Простой') && idleReasonAutoSet.value) return false
  return key.includes('Простой') || key.includes('Прочее') || key.includes('ПрочаяДеятельность')
})

const isMilestoneConfirmation = computed(() => {
  return entry.value.activity_type.includes('ПодтВех')
})

const customInputLabel = computed(() => {
  const key = entry.value.activity_type
  if (key.includes('Простой')) return 'Причина простоя'
  return 'Описание работ'
})

const canProceed = computed(() => {
  if (needsCustomInput.value && !customDescription.value.trim()) return false
  return true
})

// Date helpers
function formatDate(d: Date): string {
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = d.getFullYear()
  return `${dd}.${mm}.${yyyy}`
}

const today = new Date()
const yesterday = new Date(today)
yesterday.setDate(today.getDate() - 1)
const dayBeforeYesterday = new Date(today)
dayBeforeYesterday.setDate(today.getDate() - 2)

const todayStr = formatDate(today)
const yesterdayStr = formatDate(yesterday)
const dayBeforeYesterdayStr = formatDate(dayBeforeYesterday)

// Filtered projects
const filteredProjects = computed(() => {
  const q = projectSearch.value.toLowerCase()
  if (!q) return tabel.projects
  return tabel.projects.filter(p => p.toLowerCase().includes(q))
})

const personalProjects = computed(() => tabel.getPersonalProjects())

// Init
onMounted(async () => {
  await Promise.all([tabel.loadActivityTree(), tabel.loadProjects()])
})

// === STEP HANDLERS ===

// Step 1: Work Type
function selectWorkType(node: ActivityNode) {
  selectedWorkType.value = node
  step.value = 2
}

// ABSENCE: Step 2 - select type
function selectAbsenceType(node: ActivityNode) {
  entry.value.activity_type = node.key
  selectedActivityLabel.value = node.label
  step.value = 3
}

// ABSENCE: Step 3 - confirm dates
function confirmAbsenceDates() {
  step.value = 4
  error.value = ''
  successMsg.value = ''
}

// ABSENCE: Step 4 - submit
async function submitAbsence() {
  submitting.value = true
  error.value = ''
  successMsg.value = ''

  try {
    const startFmt = absenceStartFormatted.value
    const endFmt = isSingleDateAbsence.value ? startFmt : absenceEndFormatted.value
    const result = await tabel.createAbsence({
      activity_type: entry.value.activity_type,
      start_date: startFmt,
      end_date: endFmt,
    })
    successMsg.value = result.message
    // Fire-and-forget: capture + save location
    captureLocationIfEnabled()
    setTimeout(() => resetForm(), 2000)
  } catch (e: unknown) {
    error.value = (e as { message?: string }).message || 'Ошибка'
  } finally {
    submitting.value = false
  }
}

// REGULAR: Step 2 - Date
function selectDate(d: string) {
  entry.value.date = d
  if (skipProject.value) {
    entry.value.project_code = ''
    initActivityNavigation()
    step.value = 4
  } else {
    step.value = 3
  }
}

function selectDateFromInput() {
  if (!customDate.value) return
  const parts = customDate.value.split('-')
  if (parts.length === 3) {
    selectDate(`${parts[2]}.${parts[1]}.${parts[0]}`)
  }
}

// REGULAR: Step 3 - Project
function selectProject(code: string) {
  entry.value.project_code = code
  tabel.addRecentProject(code)
  initActivityNavigation()
  step.value = 4
}

function initActivityNavigation() {
  breadcrumbs.value = []
  currentNodes.value = selectedWorkType.value?.children || []
}

// REGULAR: Step 4 - Activity
async function selectActivity(node: ActivityNode) {
  if (node.leaf) {
    // Check if we're in a Простой subtree
    const idleRoot = getIdleRoot()

    if (idleRoot) {
      // Простой subtree leaf selected
      entry.value.activity_type = idleRoot.key
      selectedActivityLabel.value = idleRoot.label
      hours.value = null
      selectedClaim.value = null
      milestoneSurvey.value = null
      milestoneSurveyDone.value = false
      milestonePercent.value = null
      milestoneError.value = ''
      milestoneResult.value = null

      if (node.needsInput) {
        // "Другая причина" - send leaf key directly, backend normalizes
        entry.value.activity_type = node.key
        customDescription.value = ''
        idleReasonAutoSet.value = false
      } else {
        customDescription.value = composeIdleReason(node)
        idleReasonAutoSet.value = true
      }

      step.value = 5
      return
    }

    entry.value.activity_type = node.key
    selectedActivityLabel.value = node.label
    customDescription.value = ''
    hours.value = null
    selectedClaim.value = null
    idleReasonAutoSet.value = false
    // Reset milestone survey state
    milestoneSurvey.value = null
    milestoneSurveyDone.value = false
    milestonePercent.value = null
    milestoneError.value = ''
    milestoneResult.value = null

    if (WARRANTY_KEYS.includes(node.key) || node.key.endsWith('_РабПоГарантии')) {
      warrantyOriginalKey.value = node.key
      claimsLoading.value = true
      creatingClaim.value = false
      claimError.value = ''
      resetClaimForm()
      step.value = 5
      claims.value = await tabel.loadClaims(entry.value.project_code)
      claimsLoading.value = false
    } else {
      step.value = 5
      // Check milestone survey (non-blocking — fires in background)
      milestoneSurveyLoading.value = true
      tabel.checkMilestoneSurvey(entry.value.project_code, node.key).then(data => {
        milestoneSurvey.value = data
      }).catch(() => {
        // Graceful degradation: skip survey on error
        milestoneSurvey.value = null
      }).finally(() => {
        milestoneSurveyLoading.value = false
      })
    }
  } else if (node.children) {
    breadcrumbs.value.push(node)
    currentNodes.value = node.children
  }
}

// Claims
function selectClaim(claim: Claim) {
  selectedClaim.value = claim
  entry.value.activity_type = 'РабГарантии'
  customDescription.value = claim.subject
  selectedActivityLabel.value = 'Работы по гарантии'
  hours.value = null
}

function deselectClaim() {
  selectedClaim.value = null
  entry.value.activity_type = warrantyOriginalKey.value
  customDescription.value = ''
  hours.value = null
}

async function submitClaim() {
  claimSubmitting.value = true
  claimError.value = ''
  try {
    const result = await tabel.createClaimEntry({
      project_code: entry.value.project_code,
      subject: claimForm.value.subject.trim(),
      equipment: claimForm.value.equipment.trim(),
      serial_number: claimForm.value.serial_number.trim() || '-',
      description: claimForm.value.description.trim(),
    })
    if (result.success) {
      creatingClaim.value = false
      claims.value = await tabel.loadClaims(entry.value.project_code)
    }
  } catch (e: unknown) {
    claimError.value = (e as { message?: string }).message || 'Ошибка создания'
  } finally {
    claimSubmitting.value = false
  }
}

function resetClaimForm() {
  claimForm.value = { subject: '', equipment: '', serial_number: '', description: '' }
}

function goToBreadcrumb(index: number) {
  if (index < 0) {
    breadcrumbs.value = []
    currentNodes.value = selectedWorkType.value?.children || []
  } else {
    const node = breadcrumbs.value[index]
    breadcrumbs.value = breadcrumbs.value.slice(0, index + 1)
    currentNodes.value = node?.children ?? []
  }
}

function goToConfirm() {
  // Block if milestone survey is needed but not completed
  if (milestoneSurvey.value?.needs_survey && !milestoneSurveyDone.value) {
    milestoneError.value = 'Сначала завершите оценку вехи'
    return
  }
  error.value = ''
  successMsg.value = ''
  step.value = 6
}

async function submitMilestoneSurvey() {
  if (milestonePercent.value === null || milestonePercent.value < 0 || milestonePercent.value > 100) {
    milestoneError.value = 'Введите процент от 0 до 100'
    return
  }
  const prevMin = milestoneSurvey.value?.previous_percent ?? 0
  if (milestonePercent.value < prevMin) {
    milestoneError.value = `Процент не может быть меньше ${prevMin}%`
    return
  }
  milestoneError.value = ''
  milestoneSubmitting.value = true
  try {
    const result = await tabel.submitMilestoneEstimate({
      project_code: entry.value.project_code,
      activity_type: entry.value.activity_type,
      estimated_percent: milestonePercent.value,
    })
    milestoneResult.value = result
    milestoneSurveyDone.value = true
  } catch (e: unknown) {
    milestoneError.value = (e as { message?: string }).message || 'Ошибка сохранения'
  } finally {
    milestoneSubmitting.value = false
  }
}

// REGULAR: Submit
async function submitEntry() {
  submitting.value = true
  error.value = ''
  successMsg.value = ''

  try {
    const result = await tabel.createEntry({
      date: entry.value.date,
      project_code: entry.value.project_code,
      activity_type: entry.value.activity_type,
      idle_reason: customDescription.value || undefined,
    })
    successMsg.value = result.message
    // Fire-and-forget: capture + save location
    captureLocationIfEnabled(entry.value.project_code)
    if (isWarranty.value) {
      tabelSubmitted.value = true
    } else {
      setTimeout(() => resetForm(), 2000)
    }
  } catch (e: unknown) {
    error.value = (e as { message?: string }).message || 'Ошибка'
  } finally {
    submitting.value = false
  }
}

async function requestCloseClaim() {
  if (!selectedClaim.value) return
  claimCloseError.value = ''
  claimCloseSubmitting.value = true
  try {
    await tabel.closeClaim(selectedClaim.value.id)
    claimCloseSubmitted.value = true
  } catch (e: unknown) {
    claimCloseError.value = (e as { message?: string }).message || 'Ошибка'
  } finally {
    claimCloseSubmitting.value = false
  }
}

function resetForm() {
  step.value = 1
  entry.value = { date: '', project_code: '', activity_type: '' }
  selectedWorkType.value = null
  customDescription.value = ''
  hours.value = null
  projectSearch.value = ''
  customDate.value = ''
  successMsg.value = ''
  error.value = ''
  breadcrumbs.value = []
  currentNodes.value = []
  claims.value = []
  creatingClaim.value = false
  selectedClaim.value = null
  warrantyOriginalKey.value = ''
  tabelSubmitted.value = false
  claimCloseSubmitted.value = false
  claimCloseSubmitting.value = false
  claimCloseError.value = ''
  warrantyHoursMode.value = false
  // Idle reason
  idleReasonAutoSet.value = false
  // Milestone survey
  milestoneSurvey.value = null
  milestoneSurveyLoading.value = false
  milestonePercent.value = null
  milestoneSubmitting.value = false
  milestoneSurveyDone.value = false
  milestoneError.value = ''
  milestoneResult.value = null
  absenceStartDate.value = ''
  absenceEndDate.value = ''
  resetClaimForm()
}

// Navigation
function handleBack() {
  if (isAbsenceFlow.value) {
    switch (step.value) {
      case 2: step.value = 1; selectedWorkType.value = null; break
      case 3: step.value = 2; break
      case 4: step.value = 3; break
      default: router.push('/')
    }
    return
  }

  switch (step.value) {
    case 1:
      router.push('/')
      break
    case 2:
      step.value = 1
      selectedWorkType.value = null
      break
    case 3:
      step.value = 2
      entry.value.date = ''
      break
    case 4:
      if (breadcrumbs.value.length > 0) {
        breadcrumbs.value.pop()
        if (breadcrumbs.value.length > 0) {
          currentNodes.value = breadcrumbs.value[breadcrumbs.value.length - 1]?.children ?? []
        } else {
          currentNodes.value = selectedWorkType.value?.children ?? []
        }
      } else if (skipProject.value) {
        step.value = 2
        entry.value.date = ''
      } else {
        step.value = 3
        entry.value.project_code = ''
      }
      break
    case 5:
      if (selectedClaim.value) {
        deselectClaim()
      } else {
        // Clear milestone survey state when going back
        milestoneSurvey.value = null
        milestoneSurveyDone.value = false
        milestonePercent.value = null
        milestoneError.value = ''
        milestoneResult.value = null
        idleReasonAutoSet.value = false
        customDescription.value = ''
        step.value = 4
        creatingClaim.value = false
        warrantyOriginalKey.value = ''
        if (breadcrumbs.value.length > 0) {
          currentNodes.value = breadcrumbs.value[breadcrumbs.value.length - 1]?.children ?? []
        } else {
          currentNodes.value = selectedWorkType.value?.children ?? []
        }
      }
      break
    case 6:
      warrantyHoursMode.value = false
      claimCloseSubmitting.value = false
      if (isWarranty.value && selectedClaim.value) {
        // Go back to claims list, deselect the claim
        selectedClaim.value = null
        entry.value.activity_type = warrantyOriginalKey.value
        customDescription.value = ''
        hours.value = null
      }
      step.value = 5
      tabelSubmitted.value = false
      claimCloseSubmitted.value = false
      claimCloseError.value = ''
      break
  }
}
</script>
