<?php

class Routing{

    public static function run(string $path, ?string $details){
        switch ($path) {
        case 'dashboard':
            if ($details) {
                $details = htmlspecialchars($details);
                include "public/views/dashboard.php";
            } else {
                include "public/views/dashboard.html";
            }
            break;
        case 'login':
            include "public/views/login.html";
            break;
        default:
            include "public/views/404.html";
            break;
        }
    }

}