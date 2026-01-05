<?php

require_once 'AppController.php';
require_once __DIR__ . './../repository/QuizRepository.php';

class QuizApiController extends AppController
{
    private QuizRepository $quizRepository;

    public function __construct()
    {
        $this->quizRepository = new QuizRepository();
    }

    // GET /api/quizzes?search=text
    public function list()
    {
        if (!$this->isGet()) {
            http_response_code(405);
            exit;
        }

        $search = $_GET['search'] ?? null;

        if ($search) {
            $quizzes = $this->quizRepository->getQuizzesByText($search);
        } else {
            $quizzes = $this->quizRepository->getAllQuizzes();
        }

        header('Content-Type: application/json');
        echo json_encode($quizzes ?? []);
        exit;
    }
}
