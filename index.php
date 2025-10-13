<?php

// zadanie
// 

require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

$segments = explode('/', $path);
$site = $segments[0] ?? null;
$details   = $segments[1] ?? null; 

Routing::run($site, $details);

?>
