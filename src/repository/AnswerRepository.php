<?php

require_once 'Repository.php';

class AnswerRepository extends Repository
{
    public function addAnswerToQuestion(PDO $conn, int $questionId, array $answer): void
    {
        $stmt = $conn->prepare("
            INSERT INTO answers (questionId, text, isCorrect)
            VALUES (:questionId, :text, :isCorrect)
        ");

        $stmt->execute([
            ':questionId' => $questionId,
            ':text' => $answer['text'],
            ':isCorrect' => $answer['isCorrect'] ? 1 : 0
        ]);
    }
}
