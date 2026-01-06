<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/ErrorController.php';
require_once 'src/controllers/QuizController.php';

// TODO Controllery to singleton
// TODO /dashboard/{$id}
// URL: /dashboard/{$id}

// dopracowanie elementów takie jak w prototypie na podobnym poziomie

class Routing
{

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ],
        "home" => [
            "controller" => "QuizController",
            "action" => "index"
        ],
        "error" => [
            "controller" => "ErrorController",
            "action" => "error"
        ],

        // API
    ];

    public static function run(string $path)
    {
        if (!isset(self::$routes[$path])) {
            include "public/views/404.html";
            return;
        }

        $controller = self::$routes[$path]['controller'];
        $action = self::$routes[$path]['action'];

        $controllerObj = AppController::getInstance($controller);
        $controllerObj->$action();
    }
}
