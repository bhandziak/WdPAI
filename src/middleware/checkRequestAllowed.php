<?php

require_once 'AllowedMethods.php';

function checkRequestAllowed(object $controller, string $methodName)
{
    $reflection = new ReflectionMethod($controller, $methodName);
    $attributes  = $reflection->getAttributes(AllowedMethods::class);

    if (!empty($attributes)) {
        $instance = $attributes[0]->newInstance();
        $allowed = $instance->methods;

        if (!in_array($_SERVER['REQUEST_METHOD'], $allowed)) {
            throw new Exception('Method Not Allowed', 405);
        }
    }
}
