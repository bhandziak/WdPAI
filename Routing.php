<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/ErrorController.php';
require_once 'src/controllers/QuizController.php';
require_once 'src/controllers/QuizCreationController.php';

// TODO Controllery to singleton
// TODO /dashboard/{$id}
// URL: /dashboard/{$id}

// dopracowanie elementów takie jak w prototypie na podobnym poziomie

class Routing
{

    public static $routes = [
        // AUTH
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "logout" => [
            "controller" => "SecurityController",
            "action" => "logout"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ],

        // GET QUIZZES
        "home" => [
            "controller" => "QuizController",
            "action" => "index"
        ],

        // CREATE QUIZ
        "create_quiz" => [
            "controller" => "QuizController",
            "action" => "redirectToCreateQuizForm"
        ],
        "create_quiz_start" => [
            "controller" => "QuizCreationController",
            "action" => "createQuizStart"
        ],

        // ADD QUESTION
        "add_question" => [
            "controller" => "QuizController",
            "action" => "redirectToAddQuestionForm"
        ],
        "add_question_action" => [
            "controller" => "QuizCreationController",
            "action" => "addQuestionToQuiz"
        ],

        // CANCEL QUIZ
        "cancel_creating_quiz" => [
            "controller" => "QuizCreationController",
            "action" => "cancelCreatingQuiz"
        ],

        // SAVE QUIZ
        "save_quiz" => [
            "controller" => "QuizCreationController",
            "action" => "saveQuiz"
        ],
        "quiz_created_success" => [
            "controller" => "QuizController",
            "action" => "redirectToQuizCreatedSuccess"
        ],

        // ERROR
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
