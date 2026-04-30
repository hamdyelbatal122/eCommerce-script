<?php

namespace ECommerce\Core;

/**
 * Validator Class
 * 
 * Provides validation for user input
 */
class Validator
{
    private $errors = [];
    private $data = [];

    /**
     * Create validator instance
     * 
     * @param array $data
     * @return self
     */
    public static function validate($data)
    {
        return new self($data);
    }

    /**
     * Constructor
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Validate required field
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function required($field, $message = null)
    {
        if (empty($this->data[$field])) {
            $this->addError($field, $message ?? "{$field} is required");
        }
        return $this;
    }

    /**
     * Validate email
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function email($field, $message = null)
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? "{$field} must be a valid email");
        }
        return $this;
    }

    /**
     * Validate minimum length
     * 
     * @param string $field
     * @param int $min
     * @param string $message
     * @return self
     */
    public function minLength($field, $min, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->addError($field, $message ?? "{$field} must be at least {$min} characters");
        }
        return $this;
    }

    /**
     * Validate maximum length
     * 
     * @param string $field
     * @param int $max
     * @param string $message
     * @return self
     */
    public function maxLength($field, $max, $message = null)
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->addError($field, $message ?? "{$field} must not exceed {$max} characters");
        }
        return $this;
    }

    /**
     * Validate unique in database
     * 
     * @param string $field
     * @param string $table
     * @param string $column
     * @param string $message
     * @return self
     */
    public function unique($field, $table, $column = null, $message = null)
    {
        $column = $column ?? $field;
        $db = Database::getInstance();
        
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $db->queryOne($query, [$this->data[$field]]);

        if ($result['count'] > 0) {
            $this->addError($field, $message ?? "{$field} already exists");
        }
        return $this;
    }

    /**
     * Validate numeric
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function numeric($field, $message = null)
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->addError($field, $message ?? "{$field} must be numeric");
        }
        return $this;
    }

    /**
     * Validate regex pattern
     * 
     * @param string $field
     * @param string $pattern
     * @param string $message
     * @return self
     */
    public function regex($field, $pattern, $message = null)
    {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->addError($field, $message ?? "{$field} format is invalid");
        }
        return $this;
    }

    /**
     * Check if validation passes
     * 
     * @return bool
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Check if validation fails
     * 
     * @return bool
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get all errors
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get error for specific field
     * 
     * @param string $field
     * @return string|null
     */
    public function error($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Add error
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
}
