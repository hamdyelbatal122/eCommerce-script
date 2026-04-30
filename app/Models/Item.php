<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;
use ECommerce\Core\Database;

/**
 * Item Model
 * 
 * Represents a product item in the system
 */
class Item extends BaseModel
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    /**
     * Get all approved items
     * 
     * @return array
     */
    public function getApproved()
    {
        return $this->where('approve', self::STATUS_APPROVED);
    }

    /**
     * Get pending items
     * 
     * @return array
     */
    public function getPending()
    {
        return $this->where('approve', self::STATUS_PENDING);
    }

    /**
     * Get items by category
     * 
     * @param int $categoryId
     * @param bool $onlyApproved
     * @return array
     */
    public function getByCategory($categoryId, $onlyApproved = true)
    {
        $where = ['cat_id' => $categoryId];
        if ($onlyApproved) {
            $where['approve'] = self::STATUS_APPROVED;
        }
        
        return $this->all(['*'], $where);
    }

    /**
     * Get items by tag
     * 
     * @param string $tag
     * @return array
     */
    public function getByTag($tag)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM {$this->table} WHERE tags LIKE ? AND approve = ? ORDER BY add_date DESC";
        return $db->query($query, ['%' . $tag . '%', self::STATUS_APPROVED]);
    }

    /**
     * Get items with full details (join user and category)
     * 
     * @param int $itemId
     * @return array|null
     */
    public function getFullDetails($itemId)
    {
        $db = Database::getInstance();
        $query = "SELECT i.*, 
                         u.username, u.full_name, u.avatar,
                         c.name as category_name
                  FROM {$this->table} i
                  LEFT JOIN users u ON i.member_id = u.user_id
                  LEFT JOIN categories c ON i.cat_id = c.id
                  WHERE i.item_id = ? LIMIT 1";
        
        return $db->queryOne($query, [$itemId]);
    }

    /**
     * Get latest items
     * 
     * @param int $limit
     * @param bool $onlyApproved
     * @return array
     */
    public function getLatest($limit = 5, $onlyApproved = true)
    {
        $where = [];
        if ($onlyApproved) {
            $where['approve'] = self::STATUS_APPROVED;
        }
        
        return $this->all(['*'], $where, ['add_date' => 'DESC'], $limit);
    }

    /**
     * Get items by member
     * 
     * @param int $memberId
     * @param bool $onlyApproved
     * @return array
     */
    public function getByMember($memberId, $onlyApproved = true)
    {
        $where = ['member_id' => $memberId];
        if ($onlyApproved) {
            $where['approve'] = self::STATUS_APPROVED;
        }
        
        return $this->all(['*'], $where, ['add_date' => 'DESC']);
    }

    /**
     * Get total items count
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->count();
    }

    /**
     * Get pending items count
     * 
     * @return int
     */
    public function getPendingCount()
    {
        return $this->count(['approve' => self::STATUS_PENDING]);
    }

    /**
     * Search items
     * 
     * @param string $query
     * @return array
     */
    public function search($query)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$this->table} 
                WHERE approve = ? AND (name LIKE ? OR description LIKE ?)
                ORDER BY add_date DESC";
        
        $searchTerm = '%' . $query . '%';
        return $db->query($sql, [self::STATUS_APPROVED, $searchTerm, $searchTerm]);
    }

    /**
     * Advanced listing with filters, sorting, and pagination
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function advancedList(array $filters, int $page = 1, int $perPage = 12): array
    {
        $db = Database::getInstance();
        $where = ['i.approve = ?'];
        $params = [self::STATUS_APPROVED];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(i.name LIKE ? OR i.description LIKE ? OR i.tags LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[] = 'i.cat_id = ?';
            $params[] = $categoryId;
        }

        $minPrice = (float) ($filters['min_price'] ?? 0);
        $maxPrice = (float) ($filters['max_price'] ?? 0);
        if ($minPrice > 0) {
            $where[] = 'i.price >= ?';
            $params[] = $minPrice;
        }
        if ($maxPrice > 0 && $maxPrice >= $minPrice) {
            $where[] = 'i.price <= ?';
            $params[] = $maxPrice;
        }

        $sort = (string) ($filters['sort'] ?? 'newest');
        $orderBy = 'i.add_date DESC';
        if ($sort === 'price_asc') {
            $orderBy = 'i.price ASC';
        } elseif ($sort === 'price_desc') {
            $orderBy = 'i.price DESC';
        } elseif ($sort === 'name_asc') {
            $orderBy = 'i.name ASC';
        } elseif ($sort === 'oldest') {
            $orderBy = 'i.add_date ASC';
        }

        $whereSql = implode(' AND ', $where);
        $countRow = $db->queryOne("SELECT COUNT(*) AS total FROM {$this->table} i WHERE {$whereSql}", $params);
        $total = (int) ($countRow['total'] ?? 0);

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT i.*, c.name AS category_name
                FROM {$this->table} i
                LEFT JOIN categories c ON c.id = i.cat_id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";
        $items = $db->query($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Approve item
     * 
     * @param int $itemId
     * @return int affected rows
     */
    public function approve($itemId)
    {
        return $this->update($itemId, ['approve' => self::STATUS_APPROVED]);
    }

    /**
     * Get items in category with details
     * 
     * @param int $categoryId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCategoryItems($categoryId, $limit = 12, $offset = 0)
    {
        $db = Database::getInstance();
        $query = "SELECT i.*, u.username, c.name as category_name
                  FROM {$this->table} i
                  LEFT JOIN users u ON i.member_id = u.user_id
                  LEFT JOIN categories c ON i.cat_id = c.id
                  WHERE i.cat_id = ? AND i.approve = ?
                  ORDER BY i.add_date DESC
                  LIMIT ? OFFSET ?";
        
        return $db->query($query, [$categoryId, self::STATUS_APPROVED, $limit, $offset]);
    }
}
