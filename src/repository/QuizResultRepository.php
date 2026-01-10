<?php

require_once 'Repository.php';

class QuizResultRepository extends Repository
{
    public function saveQuizResult(
        int $userId,
        int $quizId,
        int $score,
        int $correctAnswers,
        int $incorrectAnswers,
        int $takenTime
    ): void {
        $conn = $this->database->connect();
        $stmt = $conn->prepare("
            INSERT INTO quiz_results (
                user_id,
                quiz_id,
                score,
                correct_answers,
                incorrect_answers,
                taken_time
            ) VALUES (
                :user_id,
                :quiz_id,
                :score,
                :correct_answers,
                :incorrect_answers,
                :taken_time
            )
            ON CONFLICT (user_id, quiz_id)
            DO UPDATE SET
                score = EXCLUDED.score,
                correct_answers = EXCLUDED.correct_answers,
                incorrect_answers = EXCLUDED.incorrect_answers,
                taken_time = EXCLUDED.taken_time,
                played_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':quiz_id' => $quizId,
            ':score' => $score,
            ':correct_answers' => $correctAnswers,
            ':incorrect_answers' => $incorrectAnswers,
            ':taken_time' => $takenTime
        ]);

        $this->database->disconnect();
    }
}
