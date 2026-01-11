<?php

require_once 'QuizSessionRequired.php';

function checkQuizSession(object $controller, string $methodName)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $reflection = new ReflectionMethod($controller, $methodName);
    $attributes = $reflection->getAttributes(QuizSessionRequired::class);

    if (!empty($attributes)) {
        if (!isset($_SESSION['quiz'])) {
            throw new Exception('No quiz in session', 400);
        }
    }
}
