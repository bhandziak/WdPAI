<?php

require_once 'Repository.php';

class AnswerRepository extends Repository
{
    public function addAnswerToQuestion(PDO $conn, int $questionId, array $answer): void
    {
        $stmt = $conn->prepare("
            INSERT INTO answers (question_id, text, is_correct)
            VALUES (:question_id, :text, :is_correct)
        ");

        $stmt->execute([
            ':question_id' => $questionId,
            ':text' => $answer['text'],
            ':is_correct' => $answer['is_correct'] ? 1 : 0
        ]);
    }

    public function updateAnswerText(PDO $conn, int $answerId, string $text): void
    {
        $stmt = $conn->prepare("
            UPDATE answers
            SET text = :text
            WHERE id = :answer_id
        ");

        $stmt->execute([
            ':text' => $text,
            ':answer_id' => $answerId
        ]);
    }

    public function setCorrectAnswer(PDO $conn, int $questionId, int $correctAnswerId): void
    {
        // reset
        $stmt = $conn->prepare("
            UPDATE answers
            SET is_correct = false
            WHERE question_id = :question_id
        ");
        $stmt->execute([':question_id' => $questionId]);

        // set correct
        $stmt = $conn->prepare("
            UPDATE answers
            SET is_correct = true
            WHERE id = :answer_id
        ");
        $stmt->execute([':answer_id' => $correctAnswerId]);
    }
}
