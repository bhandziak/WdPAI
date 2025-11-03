<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';

// TODO Controllery to singleton
// TODO /dashboard/{$id}
// URL: /dashboard/{$id}

// dopracowanie elementów takie jak w prototypie na podobnym poziomie

class Routing{

public static $routes = [
    "login" => [
        "controller" => "SecurityController",
        "action" => "login"
    ],
    "register" => [
        "controller" => "SecurityController",
        "action" => "register"
    ],
    "dashboard" => [
        "controller" => "DashboardController",
        "action" => "index"
    ]
    ];

    public static function run(string $path){
        $parts = explode('/', $path);
        $route = $parts[0];
        $details = $parts[1] ?? null;

        switch ($route) {
            case 'dashboard':
            case 'login':
            case 'register':
                $controller = self::$routes[$route]['controller'];
                $controllerObj = AppController::getInstance($controller);
                $action = self::$routes[$route]['action'];
                $controllerObj->$action($details);
                break;
            // case 'register':
            //     $controller = new SecurityController();
            //     $controller->register();
            //     break;
            default:
                include "public/views/404.html";
                break;
        }
    }

}