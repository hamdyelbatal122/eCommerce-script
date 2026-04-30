<?php

namespace ECommerce\Core;

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers
 */
abstract class BaseController
{
    protected $view;
    protected $config;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->view = new View();
        $this->config = [
            'app' => require __DIR__ . '/../config/app.php',
            'database' => require __DIR__ . '/../config/database.php',
        ];

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Render a view
     * 
     * @param string $view view name
     * @param array $data data to pass to view
     * @return void
     */
    protected function render($view, $data = [])
    {
        $data['csrf_token'] = $this->csrfToken();
        return $this->view->render($view, $data);
    }

    /**
     * Get configuration value
     * 
     * @param string $key config key (app.key or database.default)
     * @param mixed $default default value
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    protected function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     * 
     * @return array|null
     */
    protected function getUser()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is admin
     * 
     * @return bool
     */
    protected function isAdmin()
    {
        return isset($_SESSION['user']['group_id']) && $_SESSION['user']['group_id'] == 1;
    }

    /**
     * Redirect to URL
     * 
     * @param string $url
     * @param int $code
     * @return void
     */
    protected function redirect($url, $code = 302)
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    /**
     * Return JSON response
     * 
     * @param mixed $data
     * @param int $code HTTP status code
     * @return void
     */
    protected function json($data, $code = 200)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    /**
     * Set flash message
     * 
     * @param string $key
     * @param string $message
     * @param string $type (success, error, warning, info)
     * @return void
     */
    protected function flash($key, $message, $type = 'info')
    {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    /**
     * Get flash messages
     * 
     * @return array
     */
    protected function getFlash()
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Validate URL parameter exists
     * 
     * @param string $param
     * @return mixed
     */
    protected function getParam($param, $default = null)
    {
        return $_GET[$param] ?? $default;
    }

    /**
     * Validate POST data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get CSRF token from session
     *
     * @return string
     */
    protected function csrfToken()
    {
        return $_SESSION['_csrf_token'] ?? '';
    }

    /**
     * Validate incoming CSRF token
     *
     * @param string|null $token
     * @return bool
     */
    protected function isValidCsrfToken($token)
    {
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        if (!is_string($token) || $token === '' || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Enforce CSRF validation for state-changing requests
     *
     * @return void
     */
    protected function requireCsrf()
    {
        $token = $this->post('_csrf_token') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!$this->isValidCsrfToken($token)) {
            $this->abort(419, 'Invalid security token. Please refresh and try again.');
        }
    }

    /**
     * Abort with error code
     * 
     * @param int $code HTTP status code
     * @param string $message
     * @return void
     */
    protected function abort($code = 404, $message = null)
    {
        http_response_code($code);
        $this->view->render('errors/error', [
            'code' => $code,
            'message' => $message ?? "Error {$code}",
        ]);
        exit;
    }
}
