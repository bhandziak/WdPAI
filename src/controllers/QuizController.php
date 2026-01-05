<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
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

        $quizzes = $this->quizRepository->getAllQuizzes();

        return $this->render('home', [
            'quizzes' => $quizzes ?? []
        ]);
    }
}
