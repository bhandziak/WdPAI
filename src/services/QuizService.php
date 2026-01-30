<?php

require_once __DIR__ . './../repository/QuizRepository.php';
require_once __DIR__ . './../repository/QuestionRepository.php';
require_once __DIR__ . './../repository/AnswerRepository.php';

class QuizService
{
    private QuizRepository $quizRepo;
    private QuestionRepository $questionRepo;
    private AnswerRepository $answerRepo;
    private Database $database;

    public function __construct()
    {
        $this->quizRepo = new QuizRepository();
        $this->questionRepo = new QuestionRepository();
        $this->answerRepo = new AnswerRepository();
        $this->database = Database::getInstance();
    }

    // handles own transaction
    // repos relies on sevice's connection to db
    public function createQuiz(array $quiz)
    {
        $conn = $this->database->connect();
        $conn->beginTransaction();

        try {
            $quizId = $this->quizRepo->createQuiz($conn, $quiz);

            foreach ($quiz['questions'] as $question) {
                $questionId = $this->questionRepo->addQuestionToQuiz($conn, $quizId, $question);

                foreach ($question['answers'] as $answer) {
                    $this->answerRepo->addAnswerToQuestion($conn, $questionId, $answer);
                }
            }

            $conn->commit();
        } catch (Exception $err) {
            $conn->rollBack();
            throw $err;
        }
    }

    public function updateQuizFromForm(int $quizId, array $data)
    {
        $conn = $this->database->connect();
        $conn->beginTransaction();

        try {
            // title
            $this->quizRepo->updateQuizTitle($conn, $quizId, $data['title']);

            foreach ($data['questions'] as $q) {
                // question
                $this->questionRepo->updateQuestionText($conn, $q['id'], $q['text']);

                // answers
                foreach ($q['answers'] as $a) {
                    $this->answerRepo->updateAnswerText($conn, $a['id'], $a['text']);
                }

                // correct answer
                $this->answerRepo->setCorrectAnswer(
                    $conn,
                    $q['id'],
                    $q['answers'][$q['correct']]['id']
                );
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
