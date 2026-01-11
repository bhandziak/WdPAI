<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/ErrorController.php';
require_once 'src/controllers/QuizController.php';
require_once 'src/controllers/QuizCreationController.php';

require_once 'src/middleware/checkRequestAllowed.php';
require_once 'src/middleware/checkRulesAllowed.php';
require_once 'src/middleware/checkQuizSession.php';

class Routing
{
    private static ?ErrorController $errorController = null;
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

        // PLAY QUIZ
        "play_quiz" => [
            "controller" => "QuizController",
            "action" => "playQuiz"
        ],

        // API
        "api/quiz/details" => [
            "controller" => "QuizController",
            "action" => "getQuizDetails"
        ],
        "api/quiz/finish" => [
            "controller" => "QuizController",
            "action" => "finishQuiz"
        ],
    ];

    public static function run(string $path)
    {
        $isApi = str_starts_with($path, 'api/');

        try {
            if (!isset(self::$routes[$path])) {
                throw new Exception('Page not found', 404);
            }

            $controller = self::$routes[$path]['controller'];
            $action = self::$routes[$path]['action'];

            $controllerObj = AppController::getInstance($controller);

            checkRequestAllowed($controllerObj, $action);
            checkRulesAllowed($controllerObj, $action);
            checkQuizSession($controllerObj, $action);

            $controllerObj->$action();
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);

            // For APIs
            if ($isApi) {
                echo json_encode([
                    'error' => $e->getMessage(),
                    'code' => $e->getCode() ?: 500
                ]);
                exit;
            } else {
                self::$errorController ??= AppController::getInstance("ErrorController");
                self::$errorController->error(
                    $e->getCode() ?: 500,
                    $e->getMessage()
                );
            }
        }
    }
}
