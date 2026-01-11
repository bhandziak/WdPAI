<?php

require_once 'AppController.php';
require_once __DIR__ . './../repository/QuizRepository.php';
require_once __DIR__ . './../repository/QuizResultRepository.php';
require_once __DIR__ . '/../middleware/AllowedMethods.php';
require_once __DIR__ . '/../middleware/AllowedRules.php';
require_once __DIR__ . '/../middleware/QuizSessionRequired.php';
require_once __DIR__ . '/../mappers/QuizResultMapper.php';
require_once __DIR__ . '/../mappers/FinishQuizMapper.php';

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
    #[AllowedRules(['user', 'admin'])]
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

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    public function redirectToCreateQuizForm()
    {
        return $this->render('makeQuiz/createQuiz');
    }

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    public function redirectToAddQuestionForm()
    {
        return $this->render('makeQuiz/addQuestion');
    }

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    public function redirectToQuizCreatedSuccess()
    {
        return $this->render('makeQuiz/createQuizSuccess');
    }

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    public function playQuiz()
    {
        return $this->render('playQuiz/quizView');
    }

    // /api/quiz/finish
    #[AllowedMethods(['POST'])]
    #[AllowedRules(['user', 'admin'])]
    public function finishQuiz()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // get data
        $data = json_decode(file_get_contents('php://input'), true);

        /** @var User $user */
        $user = $_SESSION['user'];

        $userId = (int) $user->getId();

        $quizResult = QuizResultMapper::fromData($data, $userId);

        // save to db
        $this->quizResultRepository->saveQuizResult(
            $quizResult->user_id,
            $quizResult->quiz_id,
            $quizResult->score,
            $quizResult->correct_answers,
            $quizResult->incorrect_answers,
            $quizResult->total_time
        );

        // refresh session data
        $this->securityController->refreshUserSession();

        // fetch other data (quizName, albumCoverUrl)
        $quizData = $this->quizRepository->getQuizContentById($quizResult->quiz_id);
        $finishQuizDto = FinishQuizMapper::fromResultAndQuiz($quizResult, $quizData);

        // render
        return $this->render('playQuiz/quizResultView', [
            'data' => $finishQuizDto
        ]);
    }

    // /api/quiz/details
    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
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
