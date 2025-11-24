<?php

require_once __DIR__ . "/utils/env_loader.php";

loadEnv(__DIR__ . "/.env");

return [
    'db_host' => $_ENV['DB_HOST'],
    'db_name' => $_ENV['DB_NAME'],
    'db_user' => $_ENV['DB_USER'],
    'db_pass' => $_ENV['DB_PASS'],
];