<?php

require_once 'AppController.php';
require_once __DIR__ . './../repository/QuizRepository.php';
require_once __DIR__ . './../repository/QuizResultRepository.php';
require_once __DIR__ . '/../middleware/AllowedMethods.php';

class QuizController extends AppController
{

    private QuizRepository $quizRepository;
    private QuizResultRepository $quizResultRepository;
    private SecurityController $securityController;

    public function __construct()
    {
        $this->quizRepository = new QuizRepository();
        $this->quizResultRepository = new QuizResultRepository();
        $this->securityController = SecurityController::getInstance("SecurityController");
    }

    #[AllowedMethods(['GET'])]
    public function index()
    {

        $search = $_GET['search'] ?? null;

        if ($search) {
            $quizzes = $this->quizRepository->getQuizzesByText($search);
        } else {
            $quizzes = $this->quizRepository->getAllQuizzes();
        }

        return $this->render('home', [
            'quizzes' => $quizzes ?? []
        ]);
    }

    public function redirectToCreateQuizForm()
    {
        return $this->render('makeQuiz/createQuiz');
    }

    public function redirectToAddQuestionForm()
    {
        return $this->render('makeQuiz/addQuestion');
    }

    public function redirectToQuizCreatedSuccess()
    {
        return $this->render('makeQuiz/createQuizSuccess');
    }

    public function playQuiz()
    {
        return $this->render('playQuiz/quizView');
    }

    // /api/quiz/finish
    public function finishQuiz()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // get data
        $data = json_decode(file_get_contents('php://input'), true);

        $userId = (int) $_SESSION['user']['user_id'];
        $quizId = $data['quizId'] ?? 0;
        $score = $data['score'] ?? 0;
        $totalTime = $data['totalTime'] ?? 0;
        $correctAnswers = $data['correctAnswers'] ?? 0;
        $incorrectAnswers = $data['incorrectAnswers'] ?? 0;

        // save to db
        $this->quizResultRepository->saveQuizResult(
            $userId,
            $quizId,
            $score,
            $correctAnswers,
            $incorrectAnswers,
            $totalTime
        );

        // refresh session data
        $this->securityController->refreshUserSession();

        // fetch other data (quizName, albumCoverUrl)
        $quizData = $this->quizRepository->getQuizContentById($quizId);
        $quizTitle = $quizData['title'] ?? "";
        $albumCoverUrl = $quizData['albumCoverUrl'] ?? null;


        // render
        return $this->render('playQuiz/quizResultView', [
            'data' => [
                'quizId' => $quizId,
                'quizTitle' => $quizTitle,
                'albumCoverUrl' => $albumCoverUrl,
                'score' => $score,
                'totalTime' => $totalTime,
                'correctAnswers' => $correctAnswers,
                'incorrectAnswers' => $incorrectAnswers
            ]
        ]);
    }

    // /api/quiz/details
    public function getQuizDetails()
    {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo 'Missing quiz id';
            exit;
        }

        $quizId = (int)$_GET['id'];

        $quiz = $this->quizRepository->getQuizContentById($quizId);

        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode($quiz);
        exit;
    }
}
