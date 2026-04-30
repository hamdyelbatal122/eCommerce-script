<?php

namespace ECommerce\Core;

use PDOException;

/**
 * Base Model Class
 * 
 * Provides common database operations for all models
 */
abstract class BaseModel
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all records
     * 
     * @param array $columns
     * @param array $where
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function all($columns = ['*'], $where = [], $order = [], $limit = null, $offset = 0)
    {
        $query = "SELECT " . implode(',', $columns) . " FROM {$this->table}";

        if (!empty($where)) {
            $conditions = array_map(fn($key) => "{$key} = ?", array_keys($where));
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        if (!empty($order)) {
            $orderClauses = [];
            foreach ($order as $column => $direction) {
                $orderClauses[] = "{$column} {$direction}";
            }
            $query .= " ORDER BY " . implode(',', $orderClauses);
        }

        if ($limit !== null) {
            $query .= " LIMIT {$limit}";
            if ($offset > 0) {
                $query .= " OFFSET {$offset}";
            }
        }

        $params = array_values($where);
        return Database::getInstance()->query($query, $params);
    }

    /**
     * Find by primary key
     * 
     * @param mixed $id
     * @return array|null
     */
    public function find($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return Database::getInstance()->queryOne($query, [$id]);
    }

    /**
     * Find by specific column
     * 
     * @param string $column
     * @param mixed $value
     * @return array|null
     */
    public function findBy($column, $value)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1";
        return Database::getInstance()->queryOne($query, [$value]);
    }

    /**
     * Get records where condition matches
     * 
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public function where($column, $value)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return Database::getInstance()->query($query, [$value]);
    }

    /**
     * Insert record
     * 
     * @param array $data
     * @return int|false last insert id or false
     */
    public function insert($data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $query = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";

        $result = Database::getInstance()->execute($query, array_values($data));

        if ($result > 0) {
            return Database::getInstance()->lastInsertId();
        }

        return false;
    }

    /**
     * Update record
     * 
     * @param mixed $id
     * @param array $data
     * @return int affected rows
     */
    public function update($id, $data)
    {
        $columns = array_keys($data);
        $setClause = implode(' = ?, ', $columns) . ' = ?';

        $query = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";

        $params = array_merge(array_values($data), [$id]);

        return Database::getInstance()->execute($query, $params);
    }

    /**
     * Delete record
     * 
     * @param mixed $id
     * @return int affected rows
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::getInstance()->execute($query, [$id]);
    }

    /**
     * Count records
     * 
     * @param array $where
     * @return int
     */
    public function count($where = [])
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";

        if (!empty($where)) {
            $conditions = array_map(fn($key) => "{$key} = ?", array_keys($where));
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $params = array_values($where);
        $result = Database::getInstance()->queryOne($query, $params);

        return $result['count'] ?? 0;
    }

    /**
     * Exists check
     * 
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function exists($column, $value)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$column} = ?";
        $result = Database::getInstance()->queryOne($query, [$value]);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get table name
     * 
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get primary key
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
}
