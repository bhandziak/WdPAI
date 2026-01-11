<?php

require_once 'AllowedRules.php';

function checkRulesAllowed(object $controller, string $methodName)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $reflection = new ReflectionMethod($controller, $methodName);
    $attributes = $reflection->getAttributes(AllowedRules::class);

    if (!empty($attributes)) {
        $instance = $attributes[0]->newInstance();
        $allowedRoles = $instance->roles;

        /** @var ?User $user */
        $user = $_SESSION['user'];

        if (!isset($user)) {
            throw new Exception('Access Denied: Not logged in', 401);
        }

        if (!in_array($user->getRole(), $allowedRoles)) {
            throw new Exception('Access Denied: Wrong role', 403);
        }
    }
}
