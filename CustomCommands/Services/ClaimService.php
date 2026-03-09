<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use PDO;

/**
 * Сервис для работы с рекламациями по проектам
 */
class ClaimService
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Создать новую рекламацию
     * 
     * @param array $data Данные рекламации
     * @return int|false ID созданной рекламации или false при ошибке
     */
    public function createClaim(array $data)
    {
        try {
            $sql = "INSERT INTO claims 
                    (project_code, reg_number, subject, act_date, equipment, serial_number, act_author, description, status, created_by) 
                    VALUES 
                    (:project_code, :reg_number, :subject, :act_date, :equipment, :serial_number, :act_author, :description, :status, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':project_code'  => $data['project_code'],
                ':reg_number'    => $data['reg_number'],
                ':subject'       => $data['subject'],
                ':act_date'      => $data['act_date'],
                ':equipment'     => $data['equipment'],
                ':serial_number' => $data['serial_number'],
                ':act_author'    => $data['act_author'],
                ':description'   => $data['description'],
                ':status'        => $data['status'] ?? 'active',
                ':created_by'    => $data['created_by'],
            ]);
            
            $id = $this->db->lastInsertId();
            error_log("ClaimService::createClaim - Created claim ID: {$id} for project: {$data['project_code']}");
            return (int) $id;
        } catch (\Exception $e) {
            error_log("ClaimService::createClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить рекламацию по ID с ФИО создателя
     * 
     * @param int $claimId
     * @return array|null
     */
    public function getClaimById(int $claimId): ?array
    {
        try {
            $sql = "SELECT c.*, 
                           JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as creator_fio,
                           JSON_UNQUOTE(JSON_EXTRACT(od_mod.data, '$.fio')) as moderator_fio,
                           JSON_UNQUOTE(JSON_EXTRACT(od_closed.data, '$.fio')) as closer_fio,
                           JSON_UNQUOTE(JSON_EXTRACT(od_reopened.data, '$.fio')) as reopener_fio
                    FROM claims c
                    LEFT JOIN tg_bot.ozna_data od ON c.created_by = od.user_id
                    LEFT JOIN tg_bot.ozna_data od_mod ON c.moderated_by = od_mod.user_id
                    LEFT JOIN tg_bot.ozna_data od_closed ON c.closed_by = od_closed.user_id
                    LEFT JOIN tg_bot.ozna_data od_reopened ON c.reopened_by = od_reopened.user_id
                    WHERE c.id = :claim_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':claim_id' => $claimId]);
            
            $claim = $stmt->fetch(PDO::FETCH_ASSOC);
            return $claim ?: null;
        } catch (\Exception $e) {
            error_log("ClaimService::getClaimById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получить рекламации по проекту
     * 
     * @param string $projectCode
     * @param string|null $status
     * @return array
     */
    public function getClaimsByProject(string $projectCode, ?string $status = null): array
    {
        try {
            $sql = "SELECT c.*, 
                           JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as creator_fio
                    FROM claims c
                    LEFT JOIN tg_bot.ozna_data od ON c.created_by = od.user_id
                    WHERE c.project_code = :project_code";
            $params = [':project_code' => $projectCode];
            
            if ($status !== null) {
                $sql .= " AND c.status = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getClaimsByProject error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить все рекламации с пагинацией
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllClaims(int $limit = 10, int $offset = 0): array
    {
        try {
            $sql = "SELECT c.*, 
                           JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as creator_fio
                    FROM claims c
                    LEFT JOIN tg_bot.ozna_data od ON c.created_by = od.user_id
                    ORDER BY c.created_at DESC
                    LIMIT :lim OFFSET :off";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getAllClaims error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Общее количество рекламаций
     * 
     * @return int
     */
    public function getTotalClaimsCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM claims";
            $stmt = $this->db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("ClaimService::getTotalClaimsCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Получить рекламации на модерации
     * 
     * @return array
     */
    public function getPendingClaims(): array
    {
        try {
            $sql = "SELECT c.*, 
                           JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as creator_fio
                    FROM claims c
                    LEFT JOIN tg_bot.ozna_data od ON c.created_by = od.user_id
                    WHERE c.status = 'pending_moderation'
                    ORDER BY c.created_at ASC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getPendingClaims error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Количество рекламаций на модерации
     * 
     * @return int
     */
    public function getPendingClaimsCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM claims WHERE status = 'pending_moderation'";
            $stmt = $this->db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("ClaimService::getPendingClaimsCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Получить активные и ожидающие модерации рекламации по проекту
     */
    public function getActiveAndPendingClaimsByProject(string $projectCode, int $limit = 5): array
    {
        try {
            $sql = "SELECT c.*, 
                           JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as creator_fio
                    FROM claims c
                    LEFT JOIN tg_bot.ozna_data od ON c.created_by = od.user_id
                    WHERE c.project_code = :project_code
                      AND c.status IN ('active', 'pending_moderation')
                    ORDER BY FIELD(c.status, 'pending_moderation', 'active'), c.created_at DESC
                    LIMIT :lim";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':project_code', $projectCode);
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getActiveAndPendingClaimsByProject error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Количество активных и ожидающих модерации рекламаций по проекту
     */
    public function getActiveAndPendingClaimsCountByProject(string $projectCode): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM claims 
                    WHERE project_code = :project_code 
                      AND status IN ('active', 'pending_moderation')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':project_code' => $projectCode]);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("ClaimService::getActiveAndPendingClaimsCountByProject error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Одобрить рекламацию
     * 
     * @param int $claimId
     * @param int $moderatorId
     * @param string|null $comment
     * @return bool
     */
    public function approveClaim(int $claimId, int $moderatorId, ?string $comment = null): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'active', 
                        moderated_by = :moderator_id, 
                        moderated_at = NOW(),
                        moderation_comment = :comment
                    WHERE id = :claim_id AND status = 'pending_moderation'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':moderator_id' => $moderatorId,
                ':comment'      => $comment,
                ':claim_id'     => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::approveClaim - Claim {$claimId} approved by {$moderatorId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::approveClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Отклонить рекламацию
     * 
     * @param int $claimId
     * @param int $moderatorId
     * @param string $reason
     * @return bool
     */
    public function rejectClaim(int $claimId, int $moderatorId, string $reason): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'rejected', 
                        moderated_by = :moderator_id, 
                        moderated_at = NOW(),
                        moderation_comment = :reason
                    WHERE id = :claim_id AND status = 'pending_moderation'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':moderator_id' => $moderatorId,
                ':reason'       => $reason,
                ':claim_id'     => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::rejectClaim - Claim {$claimId} rejected by {$moderatorId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::rejectClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновить отдельное поле рекламации
     * 
     * @param int $claimId
     * @param string $field Имя поля (из белого списка)
     * @param string $value Новое значение
     * @return bool
     */
    public function updateClaimField(int $claimId, string $field, string $value): bool
    {
        $allowedFields = [
            'project_code', 'reg_number', 'subject', 'act_date',
            'equipment', 'serial_number', 'act_author', 'description',
        ];
        
        if (!in_array($field, $allowedFields, true)) {
            error_log("ClaimService::updateClaimField - rejected field: {$field}");
            return false;
        }
        
        try {
            $sql = "UPDATE claims SET {$field} = :value, updated_at = NOW() WHERE id = :claim_id AND status IN ('active', 'pending_moderation')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':value'    => $value,
                ':claim_id' => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::updateClaimField - Claim {$claimId}, field {$field}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::updateClaimField error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Закрыть рекламацию
     * 
     * @param int $claimId
     * @param int $userId
     * @param string $reason
     * @return bool
     */
    public function closeClaim(int $claimId, int $userId, string $reason): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'closed', 
                        closed_by = :user_id, 
                        closed_at = NOW(), 
                        close_reason = :reason,
                        updated_at = NOW()
                    WHERE id = :claim_id AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id'  => $userId,
                ':reason'   => $reason,
                ':claim_id' => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::closeClaim - Claim {$claimId} closed by {$userId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::closeClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Запрос на закрытие рекламации (от пользователя, требует модерации)
     */
    public function requestCloseClaim(int $claimId, int $userId, string $reason): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'pending_close', 
                        closed_by = :user_id, 
                        closed_at = NOW(), 
                        close_reason = :reason,
                        updated_at = NOW()
                    WHERE id = :claim_id AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id'  => $userId,
                ':reason'   => $reason,
                ':claim_id' => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::requestCloseClaim - Claim {$claimId} pending_close by {$userId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::requestCloseClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить рекламации, ожидающие подтверждения закрытия
     */
    public function getPendingCloseClaims(): array
    {
        try {
            $sql = "SELECT c.*, 
                           JSON_UNQUOTE(JSON_EXTRACT(od.data, '$.fio')) as creator_fio
                    FROM claims c
                    LEFT JOIN tg_bot.ozna_data od ON c.created_by = od.user_id
                    WHERE c.status = 'pending_close'
                    ORDER BY c.closed_at ASC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getPendingCloseClaims error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Количество рекламаций ожидающих подтверждения закрытия
     */
    public function getPendingCloseClaimsCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM claims WHERE status = 'pending_close'";
            $stmt = $this->db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("ClaimService::getPendingCloseClaimsCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Подтвердить закрытие рекламации (модератором)
     */
    public function approveCloseClaim(int $claimId, int $moderatorId): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'closed', 
                        moderated_by = :moderator_id, 
                        moderated_at = NOW(),
                        updated_at = NOW()
                    WHERE id = :claim_id AND status = 'pending_close'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':moderator_id' => $moderatorId,
                ':claim_id'     => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::approveCloseClaim - Claim {$claimId} close approved by {$moderatorId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::approveCloseClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Отклонить закрытие рекламации (модератором) — вернуть в active
     */
    public function rejectCloseClaim(int $claimId, int $moderatorId): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'active', 
                        closed_by = NULL, 
                        closed_at = NULL, 
                        close_reason = NULL,
                        moderated_by = :moderator_id,
                        moderated_at = NOW(),
                        updated_at = NOW()
                    WHERE id = :claim_id AND status = 'pending_close'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':moderator_id' => $moderatorId,
                ':claim_id'     => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::rejectCloseClaim - Claim {$claimId} close rejected by {$moderatorId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::rejectCloseClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Переоткрыть рекламацию
     * 
     * @param int $claimId
     * @param int $userId
     * @return bool
     */
    public function reopenClaim(int $claimId, int $userId): bool
    {
        try {
            $sql = "UPDATE claims 
                    SET status = 'active', 
                        reopened_by = :user_id, 
                        reopened_at = NOW(), 
                        closed_by = NULL, 
                        closed_at = NULL, 
                        close_reason = NULL,
                        updated_at = NOW()
                    WHERE id = :claim_id AND status = 'closed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id'  => $userId,
                ':claim_id' => $claimId,
            ]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::reopenClaim - Claim {$claimId} reopened by {$userId}, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::reopenClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Удалить рекламацию навсегда
     * 
     * @param int $claimId
     * @return bool
     */
    public function deleteClaim(int $claimId): bool
    {
        try {
            $sql = "DELETE FROM claims WHERE id = :claim_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':claim_id' => $claimId]);
            
            $affected = $stmt->rowCount();
            error_log("ClaimService::deleteClaim - Claim {$claimId} deleted, rows affected: {$affected}");
            return $affected > 0;
        } catch (\Exception $e) {
            error_log("ClaimService::deleteClaim error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Сводка рекламаций сгруппированная по проектам и БН
     * Для уровня 1 иерархического списка
     */
    public function getGroupedClaimsSummary(): array
    {
        try {
            $sql = "SELECT c.project_code, 
                           pc.business_unit_id AS bn_id, 
                           bu.name AS bn_name, 
                           c.status, 
                           COUNT(*) AS cnt
                    FROM claims c
                    LEFT JOIN project_cards pc ON c.project_code = pc.project_code
                    LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
                    WHERE c.status IN ('active', 'closed')
                    GROUP BY c.project_code, pc.business_unit_id, bu.name, c.status";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getGroupedClaimsSummary error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Количество рекламаций по проекту в разбивке по статусам
     * Для уровня 3 (выбор статуса)
     */
    public function getClaimCountsByProjectAndStatus(string $projectCode): array
    {
        try {
            $sql = "SELECT status, COUNT(*) as cnt FROM claims
                    WHERE project_code = :code AND status IN ('active', 'closed')
                    GROUP BY status";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':code' => $projectCode]);
            
            $result = ['active' => 0, 'closed' => 0];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result[$row['status']] = (int) $row['cnt'];
            }
            return $result;
        } catch (\Exception $e) {
            error_log("ClaimService::getClaimCountsByProjectAndStatus error: " . $e->getMessage());
            return ['active' => 0, 'closed' => 0];
        }
    }
    
    /**
     * Проекты с рекламациями внутри БН
     * Для уровня 2 (проекты в БН)
     */
    public function getProjectClaimCountsByBn(int $bnId): array
    {
        try {
            $sql = "SELECT c.project_code, COUNT(*) as cnt 
                    FROM claims c
                    JOIN project_cards pc ON c.project_code = pc.project_code
                    WHERE pc.business_unit_id = :bn_id AND c.status IN ('active', 'closed')
                    GROUP BY c.project_code 
                    ORDER BY c.project_code";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':bn_id' => $bnId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("ClaimService::getProjectClaimCountsByBn error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить название БН по ID
     */
    public function getBusinessUnitName(int $bnId): ?string
    {
        try {
            $sql = "SELECT name FROM business_units WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $bnId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['name'] : null;
        } catch (\Exception $e) {
            error_log("ClaimService::getBusinessUnitName error: " . $e->getMessage());
            return null;
        }
    }
}
