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
        return $this->render('createQuiz');
    }

    public function createQuizStart()
    {
        if (!$this->isPost()) {
            return $this->render('error');
        }

        session_start();

        // validations
        $title = $_POST['title'] ?? null;
        if (!$title) {
            return $this->render('createQuiz', ['error' => 'Title is required']);
        }

        // album cover upload
        $coverUrl = null;
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . './../../public/uploads/albumCover/';

            $tmpName = $_FILES['cover']['tmp_name'];
            $extension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $fileName = 'cover-' . time() . '.' . $extension;

            $destination = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $coverUrl = '/uploads/albumCover/' . $fileName;
            } else {
                return $this->render('createQuiz', ['error' => 'Failed to upload cover image']);
            }
        }

        // save to session
        $_SESSION['quiz'] = [
            'title' => $title,
            'coverUrl' => $coverUrl,
            'createdByUserId' => $_SESSION['user']['id'] ?? null
        ];

        header('Location: /add_question');
        exit();
    }

    public function redirectToAddQuestionForm()
    {
        // return $this->render('addQuestion');

        session_start();
        $quizData = $_SESSION['quiz'];
        header('Content-Type: application/json');
        echo json_encode($quizData);
        exit();
    }
}
