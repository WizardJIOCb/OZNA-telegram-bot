# 🐛 КРИТИЧЕСКАЯ ПРОБЛЕМА: Сотрудники пропадают из списков!

## Дата обнаружения: 20.10.2025

---

## 📊 Проблема

**Симптом**: Пользователь сообщает, что сотрудники **Миндикаев, Шангин, Гайнутдинов, Ночевной, Муртазин** отсутствуют в списке "Свободная вахта", хотя вернулись с объекта давно.

---

## 🔍 Анализ

### Текущая логика `getFreetimeEmployees()`:

```php
foreach ($users as $user) {
    // Шаг 1: Проверить, на объекте ли
    $sessionStart = $this->findFirstEntryInCurrentSession($user['user_id']);
    if ($sessionStart !== null) {
        continue; // На объекте, пропустить
    }
    
    // Шаг 2: Найти начало свободной вахты
    $freeTimeStart = $this->findLastContinuousWorkPeriod($user['user_id']);
    
    if ($freeTimeStart === null) {
        continue; // ❌ НЕТ ДАННЫХ - ПРОПУСТИТЬ!
    }
    
    // Шаг 3: Добавить в список свободной вахты
    ...
}
```

### Что происходит с проблемными сотрудниками:

**Гайнутдинов Роберт Рамильевич (ID: 1429455763)**
```
Последние записи:
17.10 - 🏢 Участие в ЗПИ  ← НЕ РАБОЧАЯ АКТИВНОСТЬ
16.10 - 🏢 Участие в ЗПИ  ← НЕ РАБОЧАЯ АКТИВНОСТЬ  
15.10 - 🏢 Участие в ЗПИ  ← НЕ РАБОЧАЯ АКТИВНОСТЬ
...

Результат:
- findFirstEntryInCurrentSession() = NULL (нет РАБОЧИХ активностей)
- findLastContinuousWorkPeriod() = NULL (нет периода >4 РАБОЧИХ дней)
- ❌ НЕ попадает в список "Свободная вахта"!
- ❌ НЕ попадает в список "На объекте"!
```

---

## 🎯 Корневая причина

**Метод `findLastContinuousWorkPeriod()` возвращает NULL если**:
1. Последняя РАБОЧАЯ активность была >3 дней назад
2. ИЛИ никогда не было периода >4 РАБОЧИХ дней подряд

**Сотрудники "выпадают" из системы если**:
- Все их активности - офисные/нерабочие ("Участие в ЗПИ", "Текущая деятельность" и т.д.)
- Они давно не были на объекте (нет непрерывного периода работ)
- У них были разрозненные работы, но не больше 4 дней подряд

---

## 💡 Решение

### Вариант 1: Показывать ВСЕХ сотрудников (рекомендуется)

**Логика**: Если сотрудник НЕ на объекте, он автоматически на свободной вахте.

```php
public function getFreetimeEmployees(string $functionalDirection): array
{
    $users = $this->getUsersByFunctionalDirection($functionalDirection);
    $result = [];
    
    foreach ($users as $user) {
        // Проверить, на объекте ли
        $sessionStart = $this->findFirstEntryInCurrentSession($user['user_id']);
        if ($sessionStart !== null) {
            continue; // На объекте, пропустить
        }
        
        // Сотрудник НЕ на объекте = на свободной вахте
        // Найти последнюю ЛЮБУЮ активность
        $lastActivity = $this->findLastActivity($user['user_id']);
        
        if ($lastActivity === null) {
            // Совсем нет записей - показать с пометкой "Нет данных"
            $result[] = [
                'user_id' => $user['user_id'],
                'fio' => $user['fio'],
                'days' => 999,
                'last_work_date' => 'Нет данных',
                'icon' => '⚪'
            ];
            continue;
        }
        
        $now = new DateTime();
        $days = $this->getDaysBetween($lastActivity, $now);
        
        // Цветовые индикаторы (инвертированы!)
        if ($days <= 14) {
            $icon = '🔴'; // Мало отдыха
        } elseif ($days <= 30) {
            $icon = '🟡';
        } else {
            $icon = '🟢'; // Хорошо отдохнул
        }
        
        $result[] = [
            'user_id' => $user['user_id'],
            'fio' => $user['fio'],
            'days' => $days,
            'last_work_date' => $lastActivity->format('d.m.Y'),
            'icon' => $icon
        ];
    }
    
    // Сортировка: меньше отдохнувшие первые
    usort($result, function($a, $b) {
        return $a['days'] - $b['days'];
    });
    
    return $result;
}
```

**Новый метод для поддержки**:

```php
/**
 * Find last activity date of any type for user
 * 
 * @param int $userId
 * @return DateTime|null - Last activity date or null if no records
 */
private function findLastActivity(int $userId): ?DateTime
{
    $conn = $this->databaseService->getConnection();
    
    $query = "SELECT dt FROM ozna_tabel 
              WHERE user_id = ? 
              ORDER BY dt DESC 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return new DateTime($row['dt']);
    }
    
    return null;
}
```

---

### Вариант 2: Требовать хотя бы 1 рабочую активность

**Логика**: Показывать в свободной вахте только если была хотя бы 1 рабочая активность.

```php
// В методе getFreetimeEmployees заменить:
$freeTimeStart = $this->findLastContinuousWorkPeriod($user['user_id']);

if ($freeTimeStart === null) {
    // ВМЕСТО: continue;
    // НОВОЕ: Попробовать найти хотя бы одну рабочую активность
    $freeTimeStart = $this->findLastWorkActivity($user['user_id']);
    
    if ($freeTimeStart === null) {
        continue; // Нет рабочих активностей вообще
    }
}
```

---

## ✅ Рекомендация

**Вариант 1** - показывать ВСЕХ сотрудников, кто не на объекте.

**Обоснование**:
1. ✅ Нет "потерянных" сотрудников
2. ✅ Понятная логика: "Если не работает = на свободной вахте"
3. ✅ Видны сотрудники с офисной работой (ЗПИ, планирование)
4. ✅ Видны сотрудники без данных (с пометкой "Нет данных")

**Недостаток варианта 2**:
- Сотрудники с только офисной работой (ЗПИ) будут скрыты
- Возвращается проблема "потерянных" сотрудников

---

## 📝 Файлы для изменения

1. `CustomCommands/Services/PersonnelService.php`:
   - Изменить метод `getFreetimeEmployees()`
   - Добавить метод `findLastActivity()`

---

## 🧪 Тесты

После исправления проверить:
1. Гайнутдинов появился в списке "Свободная вахта" ✅
2. Все остальные проблемные сотрудники видны ✅
3. Счетчики правильные ✅
4. Сотрудники на объекте не попадают в свободную вахту ✅
