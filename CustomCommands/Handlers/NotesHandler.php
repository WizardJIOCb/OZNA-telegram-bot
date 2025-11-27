<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\NotesService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for notes related callback queries
 */
class NotesHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private ?NotesService $notesService = null;
    private $db;
    
    // Константы этапов
    private const STAGES = [
        'Подготовительные работы',
        'Монтаж блоков',
        'Сборка здания',
        'Технологическое оборудование',
        'Электрооборудование',
        'Отопление и вентиляция',
        'Пожарная сигнализация',
        'Охранная сигнализация',
        'Автоматизация',
        'Метрология',
        'Прочее'
    ];
    
    // Эмодзи для этапов
    private const STAGE_EMOJIS = [
        'Подготовительные работы' => '📦',
        'Монтаж блоков' => '🏗️',
        'Сборка здания' => '🏭',
        'Технологическое оборудование' => '⚙️',
        'Электрооборудование' => '⚡',
        'Отопление и вентиляция' => '🌡️',
        'Пожарная сигнализация' => '🔥',
        'Охранная сигнализация' => '🚨',
        'Автоматизация' => '🤖',
        'Метрология' => '📏',
        'Прочее' => '📝'
    ];
    
    // Сокращения этапов для callback
    private const STAGE_SHORT_CODES = [
        'Подготовительные работы' => 'Подгот',
        'Монтаж блоков' => 'МонтБл',
        'Сборка здания' => 'Сборка',
        'Технологическое оборудование' => 'ТехОбор',
        'Электрооборудование' => 'Электро',
        'Отопление и вентиляция' => 'ОтопВент',
        'Пожарная сигнализация' => 'ПожСигн',
        'Охранная сигнализация' => 'ОхрСигн',
        'Автоматизация' => 'Автомат',
        'Метрология' => 'Метрол',
        'Прочее' => 'Прочее'
    ];
    
    public function __construct(MessageService $messageService, $db = null)
    {
        $this->messageService = $messageService;
        
        // VERSION CHECK - to confirm new code is deployed
        error_log("NotesHandler: Constructor called [VERSION: 2025-10-18-18:10 - FINAL FIX APPLIED]");
        
        // Всегда создаем PDO подключение для NotesService
        try {
            // Проверяем, что константа PDO::MYSQL_ATTR_INIT_COMMAND существует
            if (!defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                define('PDO::MYSQL_ATTR_INIT_COMMAND', 1002);
            }
            
            // Создаем PDO подключение для NotesService
            $pdo = new \PDO(
                'mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4',
                'WizardJIOCb',
                '7qQ#28051961',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            $this->db = $pdo;
            $this->notesService = new NotesService($pdo);
            
            error_log("NotesHandler: PDO connection and NotesService successfully created");
        } catch (\Exception $e) {
            error_log("NotesHandler: CRITICAL - Failed to create PDO connection: " . $e->getMessage());
            error_log("NotesHandler: Stack trace: " . $e->getTraceAsString());
            $this->db = null;
            $this->notesService = null;
        }
    }
    
    public function canHandle(string $callbackData): bool
    {
        return str_starts_with($callbackData, 'notes_');
    }
    
    public function getPriority(): int
    {
        return 10; // Средний приоритет
    }
    
    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();
        
        // Роутинг по типу callback
        if (preg_match('/^notes_menu_(.+)$/', $callbackData, $matches)) {
            $context = $matches[1];
            return $this->showNotesMenu($chatId, $userId, $context);
        } elseif ($callbackData === 'notes_cancel_create') {
            // Отмена создания заметки
            return $this->cancelNoteCreation($chatId, $userId, $conversation);
        } elseif (preg_match('/^notes_cancel_edit_(\d+)$/', $callbackData, $matches)) {
            // Отмена редактирования заметки
            $noteId = (int)$matches[1];
            return $this->cancelNoteEdit($chatId, $userId, $noteId, $conversation);
        } elseif (preg_match('/^notes_create_(.+)$/', $callbackData, $matches)) {
            $context = $matches[1];
            return $this->showStageSelection($chatId, $userId, $context, $conversation);
        } elseif ($callbackData === 'notes_view_list') {
            return $this->showStagesList($chatId, $userId);
        } elseif (preg_match('/^notes_stage_(.+)_(.+)$/', $callbackData, $matches)) {
            $context = $matches[1];
            $stageCode = $matches[2];
            $stage = $this->getStageByCode($stageCode);
            return $this->startCreateNote($chatId, $userId, $context, $stage, $conversation);
        } elseif (preg_match('/^notes_view_stage_(.+)_(\d+)$/', $callbackData, $matches)) {
            $stageCode = $matches[1];
            $page = (int)$matches[2];
            $stage = $this->getStageByCode($stageCode);
            return $this->showNotesByStage($chatId, $userId, $stage, $page);
        } elseif (preg_match('/^notes_detail_(\d+)$/', $callbackData, $matches)) {
            $noteId = (int)$matches[1];
            return $this->showNoteDetails($chatId, $userId, $noteId);
        } elseif (preg_match('/^notes_edit_(\d+)$/', $callbackData, $matches)) {
            $noteId = (int)$matches[1];
            return $this->startEditNote($chatId, $userId, $noteId, $conversation);
        } elseif (preg_match('/^notes_close_(\d+)$/', $callbackData, $matches)) {
            $noteId = (int)$matches[1];
            return $this->confirmCloseNote($chatId, $userId, $noteId);
        } elseif (preg_match('/^notes_close_confirm_(\d+)$/', $callbackData, $matches)) {
            $noteId = (int)$matches[1];
            return $this->closeNote($chatId, $userId, $noteId);
        } elseif (preg_match('/^notes_reopen_(\d+)$/', $callbackData, $matches)) {
            $noteId = (int)$matches[1];
            return $this->reopenNote($chatId, $userId, $noteId);
        } elseif (preg_match('/^notes_back_stage_(.+)$/', $callbackData, $matches)) {
            $stageCode = $matches[1];
            $stage = $this->getStageByCode($stageCode);
            return $this->showNotesByStage($chatId, $userId, $stage, 1);
        } elseif ($callbackData === 'notes_back_stages') {
            return $this->showStagesList($chatId, $userId);
        }
        
        // Fallback
        return $this->showNotesMenu($chatId, $userId, 'ПНР');
    }
    
    /**
     * Показать меню заметок
     */
    private function showNotesMenu($chatId, $userId, $context): ServerResponse
    {
        $keyboard = [
            [['text' => '✍️ Оставить заметку', 'callback_data' => 'notes_create_' . $context]],
            [['text' => '📋 Посмотреть заметки', 'callback_data' => 'notes_view_list']],
            [['text' => '🔙 Назад', 'callback_data' => $this->getBackCallback($context)]]
        ];
        
        $text = "📌 <b>Заметки по проекту</b>\n\n";
        $text .= "Выберите действие:";
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Показать выбор этапа для создания заметки
     */
    private function showStageSelection($chatId, $userId, $context, Conversation $conversation): ServerResponse
    {
        $keyboard = [];
        
        foreach (self::STAGES as $stage) {
            $emoji = self::STAGE_EMOJIS[$stage];
            $code = self::STAGE_SHORT_CODES[$stage];
            $keyboard[] = [['text' => "$emoji $stage", 'callback_data' => "notes_stage_{$context}_{$code}"]];
        }
        
        $keyboard[] = [['text' => '🔙 Назад', 'callback_data' => 'notes_menu_' . $context]];
        
        $text = "✍️ <b>Создание заметки</b>\n\n";
        $text .= "Выберите этап, к которому относится заметка:";
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Начать создание заметки (переход к вводу текста)
     */
    private function startCreateNote($chatId, $userId, $context, $stage, Conversation $conversation): ServerResponse
    {
        // Сохранить контекст в состояние conversation
        $conversation->notes['notes_action'] = 'create';
        $conversation->notes['notes_context'] = $context;
        $conversation->notes['notes_stage'] = $stage;
        $conversation->update();
        
        $emoji = self::STAGE_EMOJIS[$stage] ?? '📝';
        $text = "✍️ <b>Создание заметки</b>\n\n";
        $text .= "Этап: $emoji <b>$stage</b>\n\n";
        $text .= "Введите текст заметки (до 4096 символов):\n\n";
        $text .= "Или нажмите кнопку \"Отменить\" для отказа от создания заметки.";
        
        // Добавить кнопку отмены
        $keyboard = [
            [['text' => '❌ Отменить', 'callback_data' => 'notes_cancel_create']]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Показать список этапов с заметками
     */
    private function showStagesList($chatId, $userId): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        $projectId = $this->getCurrentProject($userId);
        if (!$projectId) {
            return $this->messageService->sendMessage($chatId, "❌ Проект не выбран.", []);
        }
        
        $counts = $this->notesService->getNotesCountByStage($projectId);
        $keyboard = [];
        
        foreach (self::STAGES as $stage) {
            $emoji = self::STAGE_EMOJIS[$stage];
            $code = self::STAGE_SHORT_CODES[$stage];
            $count = $counts[$stage] ?? 0;
            $text = "$emoji $stage ($count)";
            $keyboard[] = [['text' => $text, 'callback_data' => "notes_view_stage_{$code}_1"]];
        }
        
        $keyboard[] = [['text' => '🔙 Назад', 'callback_data' => 'notes_menu_ПНР']];
        
        $messageText = "📋 <b>Заметки по этапам</b>\n\nВыберите этап:";
        
        return $this->messageService->sendMessage($chatId, $messageText, $keyboard);
    }
    
    /**
     * Показать заметки по этапу
     */
    private function showNotesByStage($chatId, $userId, $stage, $page = 1): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        $projectId = $this->getCurrentProject($userId);
        if (!$projectId) {
            return $this->messageService->sendMessage($chatId, "❌ Проект не выбран.", []);
        }
        
        $notes = $this->notesService->getNotesByStage($projectId, $stage);
        
        if (empty($notes)) {
            $emoji = self::STAGE_EMOJIS[$stage] ?? '📝';
            $text = "📋 <b>Заметки: $emoji $stage</b>\n\n";
            $text .= "Заметок пока нет.";
            
            $keyboard = [
                [['text' => '🔙 Назад к этапам', 'callback_data' => 'notes_back_stages']]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Пагинация - 5 заметок на страницу
        $perPage = 5;
        $totalNotes = count($notes);
        $totalPages = ceil($totalNotes / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $notesOnPage = array_slice($notes, $offset, $perPage);
        
        $emoji = self::STAGE_EMOJIS[$stage] ?? '📝';
        $text = "📋 <b>Заметки: $emoji $stage</b>\n";
        $text .= "Страница $page из $totalPages (всего: $totalNotes)\n\n";
        
        $keyboard = [];
        
        foreach ($notesOnPage as $note) {
            $statusEmoji = $note['status'] === 'active' ? '🟢' : '🔴';
            $shortText = mb_substr($note['note_text'], 0, 30);
            if (mb_strlen($note['note_text']) > 30) {
                $shortText .= '...';
            }
            $buttonText = "$statusEmoji $shortText";
            $keyboard[] = [['text' => $buttonText, 'callback_data' => 'notes_detail_' . $note['id']]];
        }
        
        // Кнопки навигации
        $navButtons = [];
        if ($page > 1) {
            $stageCode = self::STAGE_SHORT_CODES[$stage];
            $navButtons[] = ['text' => '⬅️ Пред.', 'callback_data' => "notes_view_stage_{$stageCode}_" . ($page - 1)];
        }
        if ($page < $totalPages) {
            $stageCode = self::STAGE_SHORT_CODES[$stage];
            $navButtons[] = ['text' => '➡️ След.', 'callback_data' => "notes_view_stage_{$stageCode}_" . ($page + 1)];
        }
        if (!empty($navButtons)) {
            $keyboard[] = $navButtons;
        }
        
        $keyboard[] = [['text' => '🔙 Назад к этапам', 'callback_data' => 'notes_back_stages']];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    private function showNoteDetails($chatId, $userId, $noteId): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        $note = $this->notesService->getNoteById($noteId);
        
        if (!$note) {
            return $this->messageService->sendMessage($chatId, "❌ Заметка не найдена.", []);
        }
        
        $emoji = self::STAGE_EMOJIS[$note['stage']] ?? '📝';
        $statusEmoji = $note['status'] === 'active' ? '🟢 Активна' : '🔴 Закрыта';
        
        $text = "📝 <b>Заметка #$noteId</b>\n\n";
        $text .= "Этап: $emoji <b>{$note['stage']}</b>\n";
        $text .= "Статус: $statusEmoji\n";
        $text .= "Автор: {$note['user_fio']}\n";
        $text .= "Создана: " . date('d.m.Y H:i', strtotime($note['created_at'])) . "\n";
        
        if ($note['status'] === 'closed' && $note['closed_by_fio']) {
            $text .= "Закрыта: {$note['closed_by_fio']}\n";
            $text .= "Дата закрытия: " . date('d.m.Y H:i', strtotime($note['closed_at'])) . "\n";
        }
        
        $text .= "\n<b>Текст заметки:</b>\n{$note['note_text']}";
        
        $keyboard = [
            [['text' => '✏️ Редактировать', 'callback_data' => 'notes_edit_' . $noteId]]
        ];
        
        if ($note['status'] === 'active') {
            $keyboard[] = [['text' => '✅ Закрыть заметку', 'callback_data' => 'notes_close_' . $noteId]];
        } else {
            $keyboard[] = [['text' => '🔄 Открыть заново', 'callback_data' => 'notes_reopen_' . $noteId]];
        }
        
        $stageCode = self::STAGE_SHORT_CODES[$note['stage']];
        $keyboard[] = [['text' => '🔙 Назад к списку', 'callback_data' => "notes_view_stage_{$stageCode}_1"]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    private function startEditNote($chatId, $userId, $noteId, Conversation $conversation): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        $note = $this->notesService->getNoteById($noteId);
        
        if (!$note) {
            return $this->messageService->sendMessage($chatId, "❌ Заметка не найдена.", []);
        }
        
        // Сохранить контекст редактирования
        $conversation->notes['notes_action'] = 'edit';
        $conversation->notes['notes_edit_id'] = $noteId;
        $conversation->update();
        
        $emoji = self::STAGE_EMOJIS[$note['stage']] ?? '📝';
        $text = "✏️ <b>Редактирование заметки #$noteId</b>\n\n";
        $text .= "Этап: $emoji <b>{$note['stage']}</b>\n\n";
        $text .= "<b>Текущий текст:</b>\n{$note['note_text']}\n\n";
        $text .= "Введите новый текст заметки (до 4096 символов):\n\n";
        $text .= "Или нажмите кнопку \"Отменить\" для отказа от редактирования.";
        
        // Добавить кнопку отмены
        $keyboard = [
            [['text' => '❌ Отменить', 'callback_data' => 'notes_cancel_edit_' . $noteId]]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    private function confirmCloseNote($chatId, $userId, $noteId): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        $note = $this->notesService->getNoteById($noteId);
        
        if (!$note) {
            return $this->messageService->sendMessage($chatId, "❌ Заметка не найдена.", []);
        }
        
        $emoji = self::STAGE_EMOJIS[$note['stage']] ?? '📝';
        $text = "❓ <b>Закрыть заметку #$noteId?</b>\n\n";
        $text .= "Этап: $emoji <b>{$note['stage']}</b>\n";
        $text .= "Автор: {$note['user_fio']}\n\n";
        $text .= "<b>Текст:</b>\n{$note['note_text']}\n\n";
        $text .= "Закрытая заметка останется в списке с пометкой.";
        
        $keyboard = [
            [['text' => '✅ Да, закрыть', 'callback_data' => 'notes_close_confirm_' . $noteId]],
            [['text' => '❌ Отмена', 'callback_data' => 'notes_detail_' . $noteId]]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    private function closeNote($chatId, $userId, $noteId): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        // Получить ФИО пользователя
        $userFio = $this->getUserFio($userId);
        
        if (!$userFio) {
            return $this->messageService->sendMessage($chatId, "❌ Не удалось получить ваше ФИО. Пожалуйста, обновите профиль.", []);
        }
        
        $success = $this->notesService->closeNote($noteId, $userId, $userFio);
        
        if ($success) {
            $text = "✅ <b>Заметка закрыта</b>\n\n";
            $text .= "Заметка #$noteId успешно закрыта.";
            
            $keyboard = [
                [['text' => '📋 К списку заметок', 'callback_data' => 'notes_view_list']],
                [['text' => '🏠 В главное меню', 'callback_data' => 'notes_menu_ПНР']]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } else {
            return $this->messageService->sendMessage($chatId, "❌ Не удалось закрыть заметку.", []);
        }
    }
    
    private function reopenNote($chatId, $userId, $noteId): ServerResponse
    {
        if (!$this->notesService) {
            return $this->messageService->sendMessage($chatId, "❌ Сервис заметок недоступен", []);
        }
        
        $success = $this->notesService->reopenNote($noteId);
        
        if ($success) {
            $text = "🔄 <b>Заметка открыта</b>\n\n";
            $text .= "Заметка #$noteId успешно открыта заново.";
            
            $keyboard = [
                [['text' => '👁️ Посмотреть заметку', 'callback_data' => 'notes_detail_' . $noteId]],
                [['text' => '📋 К списку заметок', 'callback_data' => 'notes_view_list']]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } else {
            return $this->messageService->sendMessage($chatId, "❌ Не удалось открыть заметку.", []);
        }
    }
    
    /**
     * Получить этап по коду
     */
    private function getStageByCode($code)
    {
        foreach (self::STAGE_SHORT_CODES as $stage => $stageCode) {
            if ($stageCode === $code) {
                return $stage;
            }
        }
        return null;
    }
    
    /**
     * Получить callback для кнопки "Назад"
     */
    private function getBackCallback($context): string
    {
        switch ($context) {
            case 'ПНР':
                return 'cat_ТабПНРаботы';
            case 'ШМР':
                return 'cat_ШМР_ШефМонт_Back';
            case 'Проект':
                return 'cat_ТабПроектнаяДеят';
            default:
                return 'category_Табель_Пусконаладка_Back';
        }
    }
    
    /**
     * Получить текущий проект пользователя
     */
    private function getCurrentProject($userId)
    {
        try {
            // Получаем conversation чтобы достать выбранный проект
            $conversation = new Conversation($userId, $userId, '/callback');
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            error_log("NotesHandler::getCurrentProject for user {$userId}: " . ($projectCode ?: 'not found'));
            return $projectCode;
        } catch (\Exception $e) {
            error_log("NotesHandler::getCurrentProject error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получить ФИО пользователя
     */
    private function getUserFio($userId)
    {
        try {
            // Получаем данные пользователя из pDB
            $userData = \pDB::getUserData($userId);
            $fio = $userData['fio'] ?? null;
            
            // Проверяем что ФИО не пустое и не равно дефолтному значению
            if (empty($fio) || $fio === 'Без ФИО') {
                error_log("NotesHandler::getUserFio for user {$userId}: FIO not set or default value");
                return null;
            }
            
            error_log("NotesHandler::getUserFio for user {$userId}: {$fio}");
            return $fio;
        } catch (\Exception $e) {
            error_log("NotesHandler::getUserFio error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Обработка текстового ввода для заметок
     */
    public function handleNoteTextInput($message, $conversation, $chatId, $userId): ?ServerResponse
    {
        if (!isset($conversation->notes['notes_action'])) {
            return null;
        }
        
        $action = $conversation->notes['notes_action'];
        $text = trim($message->getText(true));
        
        // Валидация текста
        if (!$this->notesService->validateNoteText($text)) {
            $errorText = "❌ Текст заметки должен быть от 1 до 4096 символов.\n";
            $errorText .= "Текущая длина: " . mb_strlen($text) . " символов.\n\n";
            $errorText .= "Пожалуйста, введите корректный текст:";
            return $this->messageService->sendMessage($chatId, $errorText, []);
        }
        
        if ($action === 'create') {
            return $this->saveNewNote($conversation, $chatId, $userId, $text);
        } elseif ($action === 'edit') {
            return $this->saveEditedNote($conversation, $chatId, $userId, $text);
        }
        
        return null;
    }
    
    /**
     * Сохранить новую заметку
     */
    private function saveNewNote($conversation, $chatId, $userId, $text): ServerResponse
    {
        // Проверяем что NotesService инициализирован
        if (!$this->notesService) {
            error_log("NotesHandler::saveNewNote CRITICAL - NotesService is not initialized!");
            return $this->messageService->sendMessage($chatId, "❌ Ошибка инициализации сервиса заметок. Обратитесь к администратору.", []);
        }
        
        $context = $conversation->notes['notes_context'] ?? 'ПНР';
        $stage = $conversation->notes['notes_stage'] ?? 'Прочее';
        
        $projectId = $this->getCurrentProject($userId);
        if (!$projectId) {
            error_log("NotesHandler::saveNewNote - Project not found for user {$userId}");
            return $this->messageService->sendMessage($chatId, "❌ Проект не выбран.", []);
        }
        
        $userFio = $this->getUserFio($userId);
        if (!$userFio) {
            error_log("NotesHandler::saveNewNote - FIO not found for user {$userId}");
            return $this->messageService->sendMessage($chatId, "❌ Не удалось получить ваше ФИО. Пожалуйста, обновите профиль.", []);
        }
        
        // Определить отдел на основе контекста
        $department = match($context) {
            'ШМР' => 'ШМР',
            'ПНР' => 'ПНР',
            'Проект' => 'Проект',
            default => 'ПНР'
        };
        
        $noteData = [
            'project_id' => $projectId,
            'user_id' => $userId,
            'user_fio' => $userFio,
            'department' => $department,
            'context_menu' => $context,
            'stage' => $stage,
            'note_text' => $text
        ];
        
        error_log("NotesHandler::saveNewNote - Attempting to create note with data: " . json_encode($noteData));
        
        $noteId = $this->notesService->createNote($noteData);
        
        error_log("NotesHandler::saveNewNote - createNote returned: " . ($noteId ? "ID {$noteId}" : "FALSE"));
        
        if ($noteId) {
            // Очистить состояние conversation
            unset($conversation->notes['notes_action']);
            unset($conversation->notes['notes_context']);
            unset($conversation->notes['notes_stage']);
            $conversation->update();
            
            $emoji = self::STAGE_EMOJIS[$stage] ?? '📝';
            $successText = "✅ <b>Заметка создана</b>\n\n";
            $successText .= "Заметка #{$noteId} успешно сохранена.\n";
            $successText .= "Этап: {$emoji} <b>{$stage}</b>";
            
            $keyboard = [
                [['text' => '👁️ Посмотреть заметку', 'callback_data' => 'notes_detail_' . $noteId]],
                [['text' => '✍️ Создать еще', 'callback_data' => 'notes_create_' . $context]],
                [['text' => '📋 К списку заметок', 'callback_data' => 'notes_view_list']]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
        } else {
            error_log("NotesHandler::saveNewNote - FAILED to create note. Check error_log for NotesService errors.");
            return $this->messageService->sendMessage($chatId, "❌ Не удалось создать заметку. Проверьте подключение к базе данных.", []);
        }
    }
    
    /**
     * Сохранить отредактированную заметку
     */
    private function saveEditedNote($conversation, $chatId, $userId, $text): ServerResponse
    {
        $noteId = $conversation->notes['notes_edit_id'] ?? null;
        
        if (!$noteId) {
            return $this->messageService->sendMessage($chatId, "❌ Ошибка: ID заметки не найден.", []);
        }
        
        $success = $this->notesService->updateNoteText($noteId, $text);
        
        if ($success) {
            // Очистить состояние conversation
            unset($conversation->notes['notes_action']);
            unset($conversation->notes['notes_edit_id']);
            $conversation->update();
            
            $successText = "✅ <b>Заметка обновлена</b>\n\n";
            $successText .= "Заметка #$noteId успешно изменена.";
            
            $keyboard = [
                [['text' => '👁️ Посмотреть заметку', 'callback_data' => 'notes_detail_' . $noteId]],
                [['text' => '📋 К списку заметок', 'callback_data' => 'notes_view_list']]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
        } else {
            return $this->messageService->sendMessage($chatId, "❌ Не удалось обновить заметку. Попробуйте позже.", []);
        }
    }
    
    /**
     * Отмена создания заметки
     */
    private function cancelNoteCreation($chatId, $userId, Conversation $conversation): ServerResponse
    {
        // Очистить состояние
        $context = $conversation->notes['notes_context'] ?? 'ПНР';
        unset($conversation->notes['notes_action']);
        unset($conversation->notes['notes_context']);
        unset($conversation->notes['notes_stage']);
        $conversation->update();
        
        $text = "❌ Создание заметки отменено.";
        
        // Вернуться к меню заметок
        return $this->showNotesMenu($chatId, $userId, $context);
    }
    
    /**
     * Отмена редактирования заметки
     */
    private function cancelNoteEdit($chatId, $userId, $noteId, Conversation $conversation): ServerResponse
    {
        // Очистить состояние
        unset($conversation->notes['notes_action']);
        unset($conversation->notes['notes_edit_id']);
        $conversation->update();
        
        $text = "❌ Редактирование заметки отменено.";
        
        // Вернуться к просмотру заметки
        return $this->showNoteDetails($chatId, $userId, $noteId);
    }
}
