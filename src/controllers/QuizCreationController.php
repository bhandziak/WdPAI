<?php

require_once 'AppController.php';
require_once __DIR__ . './../services/QuizService.php';
require_once __DIR__ . './../services/UploadService.php';

require_once __DIR__ . '/../middleware/AllowedMethods.php';
require_once __DIR__ . '/../middleware/AllowedRules.php';
require_once __DIR__ . '/../middleware/QuizSessionRequired.php';

class QuizCreationController extends AppController
{
    private QuizService $quizService;
    private UploadService $imageUploadService;
    private UploadService $audioUploadService;


    public function __construct()
    {
        $this->quizService = new QuizService();
        $this->imageUploadService = new UploadService(
            __DIR__ . '/../../public/uploads/albumCover/',
            '/uploads/albumCover/'
        );
        $this->audioUploadService = new UploadService(
            __DIR__ . '/../../public/uploads/audio/',
            '/uploads/audio/'
        );
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
        try {
            if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                $coverUrl = $this->imageUploadService->save($_FILES['cover'], 'cover');
            }
        } catch (Exception $e) {
            return $this->render('makeQuiz/createQuiz', ['error' => $e->getMessage()]);
        }

        // save to session
        /** @var User $user */
        $user = $_SESSION['user'];

        $_SESSION['quiz'] = [
            'title' => $title,
            'album_cover_url' => $coverUrl,
            'created_by' => $user->getId(),
            'current_question_number' => 1
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
        try {
            $audioUrl = $this->audioUploadService->save($_FILES['cover'], 'audio');
        } catch (Exception $e) {
            return $this->render('makeQuiz/addQuestion', ['error' => $e->getMessage()]);
        }

        // 4. Add question with answers to session
        $question = [
            'text' => $questionText,
            'audio_url' => $audioUrl,
            'answers' => []
        ];

        foreach ($answers as $key => $text) {
            $question['answers'][] = [
                'text' => $text,
                'is_correct' => ($key === $correctKey)
            ];
        }

        if (!isset($_SESSION['quiz']['questions'])) {
            $_SESSION['quiz']['questions'] = [];
        }
        $_SESSION['quiz']['questions'][] = $question;

        // 5. Save current question number
        $_SESSION['quiz']['current_question_number'] = count($_SESSION['quiz']['questions']) + 1;

        // 6. Save or next question
        if (isset($_POST['save'])) {
            header('Location: /save_quiz');
            exit();
        } else {
            header('Location: /add_question');
            exit();
        }
    }

    #[AllowedMethods(['GET'])]
    #[AllowedRules(['user', 'admin'])]
    #[QuizSessionRequired]
    public function cancelCreatingQuiz()
    {
        // 1. Remove cover image
        if (!empty($_SESSION['quiz']['album_cover_url'])) {
            $coverUrl = $_SESSION['quiz']['album_cover_url'];
            $this->imageUploadService->delete($coverUrl);
        }

        // 2. Remove audio files (mp3) from questions
        if (!empty($_SESSION['quiz']['questions'])) {
            foreach ($_SESSION['quiz']['questions'] as $question) {
                if (!empty($question['audio_url'])) {
                    $audioUrl = $question['audio_url'];
                    $this->audioUploadService->delete($audioUrl);
                }
            }
        }

        // 3. Remove quiz from session
        unset($_SESSION['quiz']);

        header('Location: /home');
        exit();
    }

    #[AllowedMethods(['GET'])]
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
            throw new Exception('An error occurred while creating the quiz' . $err->getMessage(), 500);
        }
        exit();
    }
}
