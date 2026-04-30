<?php

namespace ECommerce\Core;

use PDO;
use PDOException;

/**
 * Database Connection Class
 * 
 * Singleton pattern for database connection management
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct()
    {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->connect();
    }

    /**
     * Get singleton instance
     * 
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     * 
     * @throws PDOException
     */
    private function connect()
    {
        try {
            $config = $this->config['connections'][$this->config['default']];
            
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }

    /**
     * Get database connection
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute raw query
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    public function query($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException('Query Error: ' . $e->getMessage());
        }
    }

    /**
     * Execute query and get single row
     * 
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function queryOne($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new PDOException('Query Error: ' . $e->getMessage());
        }
    }

    /**
     * Execute INSERT/UPDATE/DELETE
     * 
     * @param string $query
     * @param array $params
     * @return int affected rows
     */
    public function execute($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new PDOException('Execute Error: ' . $e->getMessage());
        }
    }

    /**
     * Get last inserted ID
     * 
     * @return string
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->connection->rollBack();
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
    }
}
