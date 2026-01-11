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
}
