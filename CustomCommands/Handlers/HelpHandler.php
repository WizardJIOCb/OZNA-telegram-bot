<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for help related callback queries
 */
class HelpHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function canHandle(string $callbackData): bool
    {
        // Fix encoding issues with callback data
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        $canHandle = str_starts_with($callbackData, 'category_Помощь');
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Получаем подкатегорию
        if ($category === 'Помощь_Начало') {
            return $this->showGettingStarted($chatId);
        } elseif ($category === 'Помощь_Функции') {
            return $this->showFeatures($chatId, $userId);
        } elseif ($category === 'Помощь_FAQ') {
            return $this->showFAQ($chatId);
        } elseif ($category === 'Помощь_Уровни') {
            return $this->showAccessLevels($chatId, $userId);
        } else {
            // Главное меню помощи
            return $this->showHelpMenu($chatId);
        }
    }
    
    /**
     * Главное меню помощи
     */
    private function showHelpMenu(string $chatId): ServerResponse
    {
        $text = "📘 <b>Помощь</b>\n\n";
        $text .= "Добро пожаловать в справочный раздел!\n";
        $text .= "Выберите раздел:\n";
        
        $keyboard = [
            [
                ['text' => '🚀 Как начать работу', 'callback_data' => 'category_Помощь_Начало']
            ],
            [
                ['text' => '✨ Основные функции', 'callback_data' => 'category_Помощь_Функции']
            ],
            [
                ['text' => '❓ Частые вопросы', 'callback_data' => 'category_Помощь_FAQ']
            ],
            [
                ['text' => '🔐 Уровни доступа', 'callback_data' => 'category_Помощь_Уровни']
            ],
            [
                ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Как начать работу
     */
    private function showGettingStarted(string $chatId): ServerResponse
    {
        $text = "🚀 <b>Как начать работу</b>\n\n";
        $text .= "<b>Шаг 1: Заполните профиль</b>\n";
        $text .= "• Нажмите 👤 Профиль\n";
        $text .= "• Укажите ваше ФИО\n";
        $text .= "• Выберите функциональное направление\n\n";
        
        $text .= "<b>Шаг 2: Запросите доступ</b>\n";
        $text .= "• Нажмите 🔐 Запросить доступ\n";
        $text .= "• Дождитесь одобрения администратора\n\n";
        
        $text .= "<b>Шаг 3: Начните работу</b>\n";
        $text .= "• После получения доступа вам будут доступны:\n";
        $text .= "  📊 Табель - учет рабочего времени\n";
        $text .= "  📁 Проекты - работа с проектами\n\n";
        
        $text .= "💡 <i>Совет: Используйте /start для возврата в главное меню</i>";
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к помощи', 'callback_data' => 'category_Помощь']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Основные функции
     */
    private function showFeatures(string $chatId, int $userId): ServerResponse
    {
        // Получаем уровень доступа
        require_once __DIR__ . '/../Services/AdminService.php';
        $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($this->messageService);
        $accessLevel = $adminService->getUserAccessLevel($userId);
        
        $text = "✨ <b>Основные функции бота</b>\n\n";
        
        $text .= "👤 <b>Профиль</b>\n";
        $text .= "• Просмотр вашего профиля\n";
        $text .= "• Изменение ФИО\n";
        $text .= "• Выбор функционального направления\n\n";
        
        if ($accessLevel >= 1) {
            $text .= "📊 <b>Табель</b>\n";
            $text .= "• Учет рабочего времени\n";
            $text .= "• Выбор подразделения\n";
            $text .= "• Выбор вида деятельности\n";
            $text .= "• Указание времени работ\n\n";
            
            $text .= "📁 <b>Проекты</b>\n";
            $text .= "• 🆕 Добавление новых проектов (с модерацией)\n";
            $text .= "• 📋 Личный список проектов для отчета\n";
            $text .= "• 📊 Просмотр активности по проектам\n";
            $text .= "• 🗂️ Работа с карточками проектов\n\n";
            
            $text .= "📌 <b>Заметки</b>\n";
            $text .= "• ✍️ Создание заметок по проектам\n";
            $text .= "• 📂 Заметки по этапам работ (ШМР/ПНР)\n";
            $text .= "• 🔍 Просмотр и редактирование заметок\n";
            $text .= "• ✅ Закрытие выполненных задач\n";
            $text .= "• 📤 Экспорт заметок в PDF\n\n";
        }
        
        if ($accessLevel >= 2) {
            $text .= "📋 <b>Супервайзер</b>\n";
            $text .= "• 📊 Отчет по проекту (все сотрудники)\n";
            $text .= "• 🔍 Проекты на контроле\n\n";
            
            $text .= "👥 <b>Распределение ресурсов</b>\n";
            $text .= "• 🏗️ Сотрудники на объекте\n";
            $text .= "• 🏖️ Свободная вахта\n";
            $text .= "• 📊 Отслеживание длительности вахт\n";
            $text .= "• 🎨 Цветовые индикаторы статуса\n\n";
            
            $text .= "⚙️ <b>Админка</b>\n";
            $text .= "• 🔔 Модерация новых проектов\n";
            $text .= "• ✏️ Переименование проектов\n";
            $text .= "• 📋 Управление списком проектов\n";
            $text .= "• 👥 Рассмотрение заявок на доступ\n";
            $text .= "• 🔐 Управление уровнями доступа\n\n";
        }
        
        if ($accessLevel == 0) {
            $text .= "⚠️ <i>У вас уровень доступа \"Гость\".\nЗапросите доступ для полной функциональности!</i>";
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к помощи', 'callback_data' => 'category_Помощь']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Частые вопросы
     */
    private function showFAQ(string $chatId): ServerResponse
    {
        $text = "❓ <b>Частые вопросы</b>\n\n";
        
        $text .= "<b>Q: Как получить доступ к боту?</b>\n";
        $text .= "A: Заполните профиль (ФИО + функциональное направление) и нажмите 🔐 Запросить доступ.\n\n";
        
        $text .= "<b>Q: Как вернуться в главное меню?</b>\n";
        $text .= "A: Отправьте команду /start\n\n";
        
        $text .= "<b>Q: Как добавить новый проект?</b>\n";
        $text .= "A: 📁 Проекты → 📂 Мои проекты → 🆕 Добавить проект. Проект пойдет на модерацию и будет доступен сразу для ваших записей.\n\n";
        
        $text .= "<b>Q: Что такое \"Личный список проектов\"?</b>\n";
        $text .= "A: Это ваш персональный список проектов, которые вы чаще всего используете. При выборе проекта в Табеле будут показаны только проекты из вашего списка (если список пустой - все проекты).\n\n";
        
        $text .= "<b>Q: Как настроить личный список?</b>\n";
        $text .= "A: 📁 Проекты → 📂 Мои проекты → 📋 Проекты для отчёта. Добавляйте и удаляйте проекты по необходимости.\n\n";
        
        $text .= "<b>Q: Почему мой проект не одобрен?</b>\n";
        $text .= "A: Администратор может переименовать проект (если есть ошибки) или отклонить (если проект невалидный). Вы получите уведомление о решении.\n\n";
        
        $text .= "<b>Q: Не вижу кнопку \"Админка\"?</b>\n";
        $text .= "A: Кнопка доступна только для супервайзеров и администраторов (уровень 2+).\n\n";
        
        $text .= "<b>Q: Как изменить ФИО?</b>\n";
        $text .= "A: Перейдите в 👤 Профиль → ✒️ Изменить ФИО\n\n";
        
        $text .= "<b>Q: Моя заявка не рассматривается?</b>\n";
        $text .= "A: Дождитесь, пока администратор рассмотрит ее. При необходимости обратитесь к администратору напрямую.\n\n";
        
        $text .= "<b>Q: Как работать с заметками?</b>\n";
        $text .= "A: В меню ШМР/ПНР нажмите 📌 Заметки, выберите этап работ и создайте заметку. Заметки видны всем участникам проекта и помогают координировать работу.\n\n";
        
        $text .= "<b>Q: Что означают цвета в 'Распределении ресурсов'?</b>\n";
        $text .= "A: Для 'На объекте': 🟢 0-30 дней, 🟡 31-45 дней, 🔴 >45 дней. Для 'Свободной вахты': 🔴 ≤14 дней (мало отдыха), 🟡 15-30 дней, 🟢 >30 дней (хороший отдых).\n\n";
        
        $text .= "💬 <i>Если у вас остались вопросы, обратитесь к администратору.</i>";
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к помощи', 'callback_data' => 'category_Помощь']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Уровни доступа
     */
    private function showAccessLevels(string $chatId, int $userId): ServerResponse
    {
        // Получаем текущий уровень
        require_once __DIR__ . '/../Services/AdminService.php';
        $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($this->messageService);
        $currentLevel = $adminService->getUserAccessLevel($userId);
        $currentLevelName = $adminService->getAccessLevelName($currentLevel);
        
        $text = "🔐 <b>Уровни доступа</b>\n\n";
        $text .= "🎯 Ваш текущий уровень: <b>{$currentLevelName}</b>\n\n";
        
        $levels = [
            [
                'emoji' => '🚫',
                'name' => '<b>0 - Гость</b>',
                'access' => [
                    '• Просмотр профиля',
                    '• Заполнение профиля',
                    '• Запрос доступа'
                ]
            ],
            [
                'emoji' => '👤',
                'name' => '<b>1 - Пользователь</b>',
                'access' => [
                    '• Все функции гостя',
                    '• Работа с Табелем',
                    '• Работа с Проектами'
                ]
            ],
            [
                'emoji' => '💼',
                'name' => '<b>2 - Супервайзер</b>',
                'access' => [
                    '• Все функции пользователя',
                    '• Доступ к Админке',
                    '• Управление проектами',
                    '• Рассмотрение заявок'
                ]
            ],
            [
                'emoji' => '👑',
                'name' => '<b>3-4 - Администратор</b>',
                'access' => [
                    '• Все функции супервайзера',
                    '• Управление пользователями',
                    '• Полный доступ ко всем функциям'
                ]
            ]
        ];
        
        foreach ($levels as $index => $level) {
            $text .= "{$level['emoji']} {$level['name']}\n";
            foreach ($level['access'] as $item) {
                $text .= "  {$item}\n";
            }
            if ($index < count($levels) - 1) {
                $text .= "\n";
            }
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к помощи', 'callback_data' => 'category_Помощь']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    public function getPriority(): int
    {
        return 1; // Same as before, lowest priority
    }
    
    /**
     * Ensure UTF-8 encoding for text
     */
    private function ensureUtf8Encoding($text)
    {
        // If mbstring is available, use it for encoding conversion
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // If mbstring is not available, return as is
        return $text;
    }
}