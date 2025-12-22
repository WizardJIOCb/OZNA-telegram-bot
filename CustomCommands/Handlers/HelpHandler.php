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
        $text .= "• Просмотр и редактирование профиля\n";
        $text .= "• Изменение ФИО\n";
        $text .= "• Выбор функционального направления\n";
        if ($accessLevel == 0) {
            $text .= "• 🔐 Запрос доступа к боту\n";
        }
        $text .= "\n";
        
        if ($accessLevel >= 1) {
            $text .= "📊 <b>Табель</b>\n";
            $text .= "• Учет рабочего времени по проектам\n";
            $text .= "• Выбор подразделения и вида деятельности\n";
            $text .= "• Указание времени работ в часах\n";
            $text .= "• Запись разных видов активностей\n\n";
            
            $text .= "📁 <b>Проекты</b>\n";
            $text .= "• 🆕 Добавление новых проектов (модерируются администратором)\n";
            $text .= "• 📋 Личный список проектов для быстрого доступа\n";
            $text .= "• 📊 Просмотр вашей активности по проектам\n";
            $text .= "• 🗂️ Работа с карточками проектов\n\n";
            
            $text .= "📌 <b>Заметки</b>\n";
            $text .= "• ✍️ Создание заметок по проектам\n";
            $text .= "• 📂 Заметки по этапам работ (ШМР/ПНР)\n";
            $text .= "• 🔍 Просмотр и редактирование заметок\n";
            $text .= "• ✅ Закрытие выполненных задач\n";
            $text .= "• 📤 Экспорт заметок в PDF\n\n";
        } else {
            $text .= "⚠️ <i>Для доступа к Табелю, Проектам и Заметкам запросите доступ через Профиль.</i>\n";
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
        $text .= "A: Заполните профиль (ФИО + функциональное направление) и нажмите 🔐 Запросить доступ в разделе Профиль.\n\n";
        
        $text .= "<b>Q: Как вернуться в главное меню?</b>\n";
        $text .= "A: Отправьте команду /start или нажмите кнопку \"Вернуться в главное меню\" в текущем разделе.\n\n";
        
        $text .= "<b>Q: Как добавить новый проект?</b>\n";
        $text .= "A: 📁 Проекты → 📂 Мои проекты → 🆕 Добавить проект. Проект будет добавлен на модерацию и сразу доступен для ваших записей.\n\n";
        
        $text .= "<b>Q: Что такое \"Личный список проектов\"?</b>\n";
        $text .= "A: Это ваш персональный список проектов для быстрого доступа. При выборе проекта в Табеле показываются только проекты из вашего списка (если список пустой - показываются все проекты).\n\n";
        
        $text .= "<b>Q: Как настроить личный список проектов?</b>\n";
        $text .= "A: 📁 Проекты → 📂 Мои проекты → 📋 Проекты для отчёта. Добавляйте нужные проекты или удаляйте ненужные.\n\n";
        
        $text .= "<b>Q: Почему мой проект не одобрен?</b>\n";
        $text .= "A: Администратор может переименовать проект для исправления ошибок или отклонить невалидный проект. Вы получите уведомление о решении.\n\n";
        
        $text .= "<b>Q: Как изменить ФИО?</b>\n";
        $text .= "A: Перейдите в 👤 Профиль → ✒️ Изменить ФИО и введите новые данные.\n\n";
        
        $text .= "<b>Q: Моя заявка долго не рассматривается?</b>\n";
        $text .= "A: Администратор рассмотрит заявку в ближайшее время. При необходимости обратитесь к администратору напрямую.\n\n";
        
        $text .= "<b>Q: Как работать с заметками?</b>\n";
        $text .= "A: В Табеле выберите подразделение (ШМР/ПНР), затем нажмите 📌 Заметки. Выберите проект и создайте заметку. Заметки помогают координировать работу команды.\n\n";
        
        $text .= "<b>Q: Можно ли редактировать или удалять записи в табеле?</b>\n";
        $text .= "A: Записи в табеле сохраняются в базу данных. Для изменений обратитесь к администратору.\n\n";
        
        $text .= "💬 <i>Если у вас остались вопросы, обратитесь к администратору.</i>";
        
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