<?php

require_once 'src/controllers/SecurityController.php';

// TODO Controllery to singleton

class Routing{

public static $routes = [
    "login" => [
        "controller" => "SecurityController",
        "action" => "login"
    ],
    "register" => [
        "controller" => "SecurityController",
        "action" => "register"
    ]
    ];

    public static function run(string $path){


        switch ($path) {
        case 'dashboard':
            include "public/views/dashboard.html";
            break;
        case 'login':
        case 'register':
            $controller = self::$routes[$path]['controller'];
            $controllerObj = new $controller;
            $action = self::$routes[$path]['action'];
            $controllerObj->$action();
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