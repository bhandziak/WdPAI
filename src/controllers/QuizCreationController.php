<?php

require_once 'AppController.php';
require_once __DIR__ . './../services/QuizService.php';
require_once __DIR__ . '/../middleware/AllowedMethods.php';
require_once __DIR__ . '/../middleware/AllowedRules.php';
require_once __DIR__ . '/../middleware/QuizSessionRequired.php';

class QuizCreationController extends AppController
{
    private QuizService $quizService;

    public function __construct()
    {
        $this->quizService = new QuizService();
    }

    #[AllowedMethods(['POST'])]
    #[AllowedRules(['user', 'admin'])]
    public function createQuizStart()
    {
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
            'createdByUserId' => $_SESSION['user']['user_id'] ?? null,
            'currentQuestionNumber' => 1
        ];

        header('Location: /add_question');
        exit();
    }

    #[AllowedMethods(['POST'])]
    #[AllowedRules(['user', 'admin'])]
    #[QuizSessionRequired]
    public function addQuestionToQuiz()
    {
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

    #[AllowedRules(['user', 'admin'])]
    #[QuizSessionRequired]
    public function cancelCreatingQuiz()
    {
        $publicDir = __DIR__ . '/../../public';

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

    #[AllowedRules(['user', 'admin'])]
    #[QuizSessionRequired]
    public function saveQuiz()
    {
        try {
            $this->quizService->createQuiz($_SESSION['quiz']);

            // clear cookie
            unset($_SESSION['quiz']);

            header('Location: /quiz_created_success');
        } catch (Throwable $err) {
            throw new Exception('An error occurred while creating the quiz', 500);
        }
        exit();
    }
}
