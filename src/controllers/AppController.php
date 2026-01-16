<?php


class AppController
{
    private static array $instances = [];

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function getInstance(string $controllerClass): self
    {
        if (!isset(self::$instances[$controllerClass])) {
            self::$instances[$controllerClass] = new $controllerClass();
        }
        return self::$instances[$controllerClass];
    }

    protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    protected function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }


    protected function render(?string $template = null, array $variables = [])
    {
        if ($template === null) {
            throw new Exception('Template not specified', 500);
        }

        $templatePath = 'public/views/' . $template . '.html';

        if (!file_exists($templatePath)) {
            throw new Exception('View not found: ' . $template, 404);
        }

        extract($variables, EXTR_SKIP);

        ob_start();
        include $templatePath;
        $output = ob_get_clean();

        echo $output;
    }
}
