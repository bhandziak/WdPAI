<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class QuizController extends AppController
{

    public function index(?int $id = null)
    {

        return $this->render('home', ['quizzes' => []]);
    }
}
