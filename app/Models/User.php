<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;

/**
 * User Model
 * 
 * Represents a user in the system
 */
class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    const GROUP_USER = 0;
    const GROUP_ADMIN = 1;

    /**
     * Get all approved users
     * 
     * @return array
     */
    public function getApproved()
    {
        return $this->where('reg_status', self::STATUS_APPROVED);
    }

    /**
     * Get pending users
     * 
     * @return array
     */
    public function getPending()
    {
        return $this->where('reg_status', self::STATUS_PENDING);
    }

    /**
     * Get admins
     * 
     * @return array
     */
    public function getAdmins()
    {
        return $this->where('group_id', self::GROUP_ADMIN);
    }

    /**
     * Find by username
     * 
     * @param string $username
     * @return array|null
     */
    public function findByUsername($username)
    {
        return $this->findBy('username', $username);
    }

    /**
     * Find by email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email)
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find user by Google account id
     *
     * @param string $googleId
     * @return array|null
     */
    public function findByGoogleId($googleId)
    {
        return $this->findBy('google_id', $googleId);
    }

    /**
     * Get user's items
     * 
     * @param int $userId
     * @param bool $onlyApproved
     * @return array
     */
    public function getUserItems($userId, $onlyApproved = false)
    {
        $where = ['member_id' => $userId];
        if ($onlyApproved) {
            $where['approve'] = 1;
        }
        
        $item = new Item();
        return $item->all(['*'], $where);
    }

    /**
     * Get total users count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->count();
    }

    /**
     * Get pending users count
     * 
     * @return int
     */
    public function getPendingCount()
    {
        return $this->count(['reg_status' => self::STATUS_PENDING]);
    }

    /**
     * Get latest users
     * 
     * @param int $limit
     * @return array
     */
    public function getLatest($limit = 5)
    {
        return $this->all(['*'], [], ['date' => 'DESC'], $limit);
    }

    /**
     * Check if username exists
     * 
     * @param string $username
     * @param int $excludeId
     * @return bool
     */
    public function usernameExists($username, $excludeId = null)
    {
        if ($excludeId) {
            $db = \ECommerce\Core\Database::getInstance();
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ? AND user_id != ?";
            $result = $db->queryOne($query, [$username, $excludeId]);
            return $result['count'] > 0;
        }
        return $this->exists('username', $username);
    }

    /**
     * Check if email exists
     * 
     * @param string $email
     * @param int $excludeId
     * @return bool
     */
    public function emailExists($email, $excludeId = null)
    {
        if ($excludeId) {
            $db = \ECommerce\Core\Database::getInstance();
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ? AND user_id != ?";
            $result = $db->queryOne($query, [$email, $excludeId]);
            return $result['count'] > 0;
        }
        return $this->exists('email', $email);
    }
}
