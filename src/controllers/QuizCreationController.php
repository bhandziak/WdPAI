<?php

require_once 'AppController.php';
require_once __DIR__ . './../services/QuizService.php';

class QuizCreationController extends AppController
{
    private QuizService $quizService;

    public function __construct()
    {
        $this->quizService = new QuizService();
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
            return $this->render('makeQuiz/createQuiz', ['error' => 'Title is required']);
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
                return $this->render('makeQuiz/createQuiz', ['error' => 'Failed to upload cover image']);
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
            return $this->render('makeQuiz/addQuestion', ['error' => 'Question text is required']);
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

        if (!$correctKey) {
            return $this->render('makeQuiz/addQuestion', [
                'error' => 'Correct answer is required'
            ]);
        }

        // 2b. Check if correct answer exits

        if (!array_key_exists($correctKey, $answers)) {
            return $this->render('makeQuiz/addQuestion', [
                'error' => 'The correct answer does not exist or is empty'
            ]);
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
                'isCorrect' => ($key === $correctKey)
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

    public function cancelCreatingQuiz()
    {
        $publicDir = __DIR__ . '/../../public';
        session_start();

        if (!isset($_SESSION['quiz'])) {
            return $this->render('error', ['message' => 'Cann\'t cancel creating quiz. No quiz in session']);
        }

        // 1. Remove cover image
        if (!empty($_SESSION['quiz']['coverUrl'])) {
            $coverPath =  $publicDir . $_SESSION['quiz']['coverUrl'];
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }
        }

        // 2. Remove audio files (mp3) from questions
        if (!empty($_SESSION['quiz']['questions'])) {
            foreach ($_SESSION['quiz']['questions'] as $question) {
                if (!empty($question['audioUrl'])) {
                    $audioPath = $publicDir . $question['audioUrl'];
                    if (file_exists($audioPath)) {
                        unlink($audioPath);
                    }
                }
            }
        }

        // 3. Remove quiz from session
        unset($_SESSION['quiz']);

        header('Location: /home');
        exit();
    }

    public function saveQuiz()
    {
        session_start();

        if (!isset($_SESSION['quiz'])) {
            return $this->render(
                'error',
                [
                    'message' => 'Cann\'t save quiz. No quiz in session'
                ]
            );
            exit();
        }

        try {
            $this->quizService->createQuiz($_SESSION['quiz']);

            // clear cookie
            unset($_SESSION['quiz']);

            header('Location: /quiz_created_success');
        } catch (Throwable $err) {
            return $this->render(
                'error',
                [
                    'message' => $err
                ]
            );
        }
        exit();
    }
}
