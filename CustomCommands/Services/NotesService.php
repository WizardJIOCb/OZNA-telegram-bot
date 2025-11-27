<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use PDO;

/**
 * Сервис для работы с заметками по проектам
 */
class NotesService
{
    private $db;
    
    /**
     * Конструктор
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Создать новую заметку
     * 
     * @param array $noteData Данные заметки
     * @return int|false ID созданной заметки или false при ошибке
     */
    public function createNote($noteData)
    {
        try {
            $sql = "INSERT INTO project_notes 
                    (project_id, user_id, user_fio, department, context_menu, stage, note_text, status) 
                    VALUES 
                    (:project_id, :user_id, :user_fio, :department, :context_menu, :stage, :note_text, 'active')";
            
            $stmt = $this->db->prepare($sql);
            
            // Log the data being inserted for debugging
            error_log("NotesService::createNote - Inserting note with project_id type: " . gettype($noteData['project_id']));
            
            // CRITICAL: project_id in database is VARCHAR (project code), not INT
            // The migration file shows: project_id INT NOT NULL
            // But we're storing project CODE (string like 'ХХХ'), not numeric ID
            // We need to pass it as a string
            $stmt->execute([
                ':project_id' => (string)$noteData['project_id'],  // Force to string
                ':user_id' => $noteData['user_id'],
                ':user_fio' => $noteData['user_fio'],
                ':department' => $noteData['department'],
                ':context_menu' => $noteData['context_menu'],
                ':stage' => $noteData['stage'],
                ':note_text' => $noteData['note_text']
            ]);
            
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("NotesService::createNote error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить заметку по ID
     * 
     * @param int $noteId ID заметки
     * @return array|null Данные заметки или null
     */
    public function getNoteById($noteId)
    {
        try {
            $sql = "SELECT * FROM project_notes WHERE id = :note_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':note_id' => $noteId]);
            
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            return $note ?: null;
        } catch (\Exception $e) {
            error_log("NotesService::getNoteById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Обновить текст заметки
     * 
     * @param int $noteId ID заметки
     * @param string $newText Новый текст
     * @return bool Успешность операции
     */
    public function updateNoteText($noteId, $newText)
    {
        try {
            $sql = "UPDATE project_notes 
                    SET note_text = :note_text, updated_at = NOW() 
                    WHERE id = :note_id AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':note_id' => $noteId,
                ':note_text' => $newText
            ]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("NotesService::updateNoteText error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Закрыть заметку
     * 
     * @param int $noteId ID заметки
     * @param int $userId Telegram ID закрывающего
     * @param string $userFio ФИО закрывающего
     * @return bool Успешность операции
     */
    public function closeNote($noteId, $userId, $userFio)
    {
        try {
            $sql = "UPDATE project_notes 
                    SET status = 'closed', 
                        closed_at = NOW(), 
                        closed_by_user_id = :closed_by_user_id, 
                        closed_by_fio = :closed_by_fio 
                    WHERE id = :note_id AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':note_id' => $noteId,
                ':closed_by_user_id' => $userId,
                ':closed_by_fio' => $userFio
            ]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("NotesService::closeNote error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Переоткрыть закрытую заметку
     * 
     * @param int $noteId ID заметки
     * @return bool Успешность операции
     */
    public function reopenNote($noteId)
    {
        try {
            $sql = "UPDATE project_notes 
                    SET status = 'active', 
                        closed_at = NULL, 
                        closed_by_user_id = NULL, 
                        closed_by_fio = NULL 
                    WHERE id = :note_id AND status = 'closed'";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':note_id' => $noteId]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("NotesService::reopenNote error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить все заметки по проекту
     * 
     * @param int $projectId ID проекта
     * @param string|null $status Фильтр по статусу (active/closed/null для всех)
     * @return array Массив заметок
     */
    public function getNotesByProject($projectId, $status = null)
    {
        try {
            if ($status) {
                $sql = "SELECT * FROM project_notes 
                        WHERE project_id = :project_id AND status = :status 
                        ORDER BY 
                            CASE WHEN status = 'active' THEN 0 ELSE 1 END,
                            created_at DESC";
                $params = [':project_id' => $projectId, ':status' => $status];
            } else {
                $sql = "SELECT * FROM project_notes 
                        WHERE project_id = :project_id 
                        ORDER BY 
                            CASE WHEN status = 'active' THEN 0 ELSE 1 END,
                            created_at DESC";
                $params = [':project_id' => $projectId];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("NotesService::getNotesByProject error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить заметки по этапу
     * 
     * @param int $projectId ID проекта
     * @param string $stage Этап работ
     * @return array Массив заметок
     */
    public function getNotesByStage($projectId, $stage)
    {
        try {
            $sql = "SELECT * FROM project_notes 
                    WHERE project_id = :project_id AND stage = :stage 
                    ORDER BY 
                        CASE WHEN status = 'active' THEN 0 ELSE 1 END,
                        created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':project_id' => $projectId,
                ':stage' => $stage
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("NotesService::getNotesByStage error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить количество заметок по каждому этапу
     * 
     * @param int $projectId ID проекта
     * @return array Ассоциативный массив [этап => количество]
     */
    public function getNotesCountByStage($projectId)
    {
        try {
            $sql = "SELECT stage, COUNT(*) as count 
                    FROM project_notes 
                    WHERE project_id = :project_id 
                    GROUP BY stage";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':project_id' => $projectId]);
            
            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[$row['stage']] = $row['count'];
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("NotesService::getNotesCountByStage error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить количество активных заметок по проекту
     * 
     * @param int $projectId ID проекта
     * @return int Количество активных заметок
     */
    public function getActiveNotesCount($projectId)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM project_notes 
                    WHERE project_id = :project_id AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':project_id' => $projectId]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['count'];
        } catch (\Exception $e) {
            error_log("NotesService::getActiveNotesCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Валидация текста заметки
     * 
     * @param string $text Текст заметки
     * @return bool Валиден ли текст
     */
    public function validateNoteText($text)
    {
        // Проверка на пустой текст
        if (empty(trim($text))) {
            return false;
        }
        
        // Проверка лимита Telegram (4096 символов)
        if (mb_strlen($text) > 4096) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Валидация этапа
     * 
     * @param string $stage Этап работ
     * @return bool Валиден ли этап
     */
    public function validateStage($stage)
    {
        $validStages = [
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
        
        return in_array($stage, $validStages);
    }
}
