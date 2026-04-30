<?php

namespace ECommerce\Core;

/**
 * View Class
 * 
 * Handles view rendering
 */
class View
{
    private $viewPath = __DIR__ . '/../app/Views';
    private $data = [];

    /**
     * Render a view
     * 
     * @param string $view view file path (without extension)
     * @param array $data variables to pass to view
     * @return string
     */
    public function render($view, $data = [])
    {
        $this->data = $data;
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("View not found: {$viewFile}");
        }

        ob_start();
        extract($data);
        require $viewFile;
        return ob_get_clean();
    }

    /**
     * Include partial view
     * 
     * @param string $partial partial view name
     * @param array $data
     * @return string
     */
    public function partial($partial, $data = [])
    {
        return $this->render($partial, $data);
    }

    /**
     * Get data property
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function data($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Escape output
     * 
     * @param string $string
     * @return string
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
