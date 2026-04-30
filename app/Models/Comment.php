<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;
use ECommerce\Core\Database;

/**
 * Comment Model
 * 
 * Represents item comments/reviews
 */
class Comment extends BaseModel
{
    protected $table = 'comments';
    protected $primaryKey = 'c_id';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    /**
     * Get approved comments
     * 
     * @return array
     */
    public function getApproved()
    {
        return $this->where('status', self::STATUS_APPROVED);
    }

    /**
     * Get pending comments
     * 
     * @return array
     */
    public function getPending()
    {
        return $this->where('status', self::STATUS_PENDING);
    }

    /**
     * Get item comments
     * 
     * @param int $itemId
     * @param bool $onlyApproved
     * @return array
     */
    public function getItemComments($itemId, $onlyApproved = true)
    {
        $db = Database::getInstance();
        $approveCondition = $onlyApproved ? ' AND status = ' . self::STATUS_APPROVED : '';
        
        $query = "SELECT c.*, u.username, u.avatar
                  FROM {$this->table} c
                  LEFT JOIN users u ON c.user_id = u.user_id
                  WHERE c.item_id = ? {$approveCondition}
                  ORDER BY c.comment_date DESC";
        
        return $db->query($query, [$itemId]);
    }

    /**
     * Get user comments
     * 
     * @param int $userId
     * @param bool $onlyApproved
     * @return array
     */
    public function getUserComments($userId, $onlyApproved = true)
    {
        $where = ['user_id' => $userId];
        if ($onlyApproved) {
            $where['status'] = self::STATUS_APPROVED;
        }
        
        return $this->all(['*'], $where, ['comment_date' => 'DESC']);
    }

    /**
     * Get latest comments
     * 
     * @param int $limit
     * @param bool $onlyApproved
     * @return array
     */
    public function getLatest($limit = 5, $onlyApproved = true)
    {
        $db = Database::getInstance();
        $approveCondition = $onlyApproved ? ' WHERE status = ' . self::STATUS_APPROVED : '';
        
        $query = "SELECT c.*, u.username, i.name as item_name
                  FROM {$this->table} c
                  LEFT JOIN users u ON c.user_id = u.user_id
                  LEFT JOIN items i ON c.item_id = i.item_id
                  {$approveCondition}
                  ORDER BY c.comment_date DESC
                  LIMIT {$limit}";
        
        return $db->query($query);
    }

    /**
     * Get total comments count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->count();
    }

    /**
     * Get pending comments count
     * 
     * @return int
     */
    public function getPendingCount()
    {
        return $this->count(['status' => self::STATUS_PENDING]);
    }

    /**
     * Get item comments count
     * 
     * @param int $itemId
     * @param bool $onlyApproved
     * @return int
     */
    public function getItemCommentsCount($itemId, $onlyApproved = true)
    {
        $where = ['item_id' => $itemId];
        if ($onlyApproved) {
            $where['status'] = self::STATUS_APPROVED;
        }
        
        return $this->count($where);
    }

    /**
     * Approve comment
     * 
     * @param int $commentId
     * @return int affected rows
     */
    public function approve($commentId)
    {
        return $this->update($commentId, ['status' => self::STATUS_APPROVED]);
    }

    /**
     * Get comment with details
     * 
     * @param int $commentId
     * @return array|null
     */
    public function getFullDetails($commentId)
    {
        $db = Database::getInstance();
        $query = "SELECT c.*, u.username, u.avatar, i.name as item_name
                  FROM {$this->table} c
                  LEFT JOIN users u ON c.user_id = u.user_id
                  LEFT JOIN items i ON c.item_id = i.item_id
                  WHERE c.c_id = ? LIMIT 1";
        
        return $db->queryOne($query, [$commentId]);
    }
}
