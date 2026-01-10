<?php

require_once 'AppController.php';
require_once __DIR__ . './../repository/QuizRepository.php';

class QuizController extends AppController
{

    private QuizRepository $quizRepository;

    public function __construct()
    {
        $this->quizRepository = new QuizRepository();
    }

    public function index()
    {
        if (!$this->isGet()) {
            return $this->render('error');
        }

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

    public function finishQuiz()
    {
        // get data
        $data = json_decode(file_get_contents('php://input'), true);

        $quizId = $data['quizId'] ?? 0;
        $score = $data['score'] ?? 0;
        $totalTime = $data['totalTime'] ?? 0;
        $correctAnswers = $data['correctAnswers'] ?? 0;
        $incorrectAnswers = $data['incorrectAnswers'] ?? 0;

        // save to db

        // render
        return $this->render('playQuiz/quizResultView', [
            'data' => [
                'quizId' => $quizId,
                'quizName' => "name",
                'albumCoverUrl' => null,
                'score' => $score,
                'totalTime' => $totalTime,
                'correctAnswers' => $correctAnswers,
                'incorrectAnswers' => $incorrectAnswers
            ]
        ]);
    }

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
