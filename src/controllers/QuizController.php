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
            'createdByUserId' => $_SESSION['user']['id'] ?? null,
            'currentQuestionNumber' => 1
        ];

        header('Location: /add_question');
        exit();
    }

    public function redirectToAddQuestionForm()
    {
        return $this->render('addQuestion');
    }

    public function addQuestionToQuiz()
    {
        if (!$this->isPost()) {
            return $this->render('error', ['message' => 'Invalid request method']);
        }

        session_start();

        if (!isset($_SESSION['quiz'])) {
            return $this->render('error', ['message' => 'No quiz in session']);
        }

        // 1. Fetch data form
        $questionText = $_POST['question'] ?? null;
        if (!$questionText) {
            return $this->render('addQuestion', ['error' => 'Question text is required']);
        }

        $answers = [
            'A' => $_POST['ans_A'] ?? null,
            'B' => $_POST['ans_B'] ?? null,
            'C' => $_POST['ans_C'] ?? null,
            'D' => $_POST['ans_D'] ?? null
        ];

        // 2. Filter null answers
        $answers = array_filter($answers, fn($ans) => !empty($ans));

        $correctKey = $_POST['correct'] ?? null;
        $correctAnswer = null;
        if ($correctKey) {
            $correctAnswer = substr($correctKey, 4);
        }

        // 3. Audio file upload
        $audioUrl = null;
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/audio/';

            $extension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $fileName = 'audio-' . time() . '.' . $extension;
            $destination = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['cover']['tmp_name'], $destination)) {
                $audioUrl = '/uploads/audio/' . $fileName;
            }
        }

        // 4. Add question with answers to session
        $question = [
            'text' => $questionText,
            'audioUrl' => $audioUrl,
            'answers' => []
        ];

        foreach ($answers as $key => $text) {
            $question['answers'][] = [
                'text' => $text,
                'isCorrect' => ($key === $correctAnswer)
            ];
        }

        if (!isset($_SESSION['quiz']['questions'])) {
            $_SESSION['quiz']['questions'] = [];
        }
        $_SESSION['quiz']['questions'][] = $question;

        // 5. Save current question number
        $_SESSION['quiz']['currentQuestionNumber'] = count($_SESSION['quiz']['questions']) + 1;

        // 6. Save or next question
        if (isset($_POST['save'])) {
            header('Location: /save_quiz');
            exit();
        } else {
            header('Location: /add_question');
            exit();
        }
    }

    public function saveQuiz()
    {
        session_start();

        if (!isset($_SESSION['quiz'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No quiz data in session']);
            exit();
        }

        $quizData = $_SESSION['quiz'];
        header('Content-Type: application/json');
        echo json_encode($quizData);
        exit();
    }
}
