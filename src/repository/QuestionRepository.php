<?php

require_once 'Repository.php';

class QuestionRepository extends Repository
{
    public function addQuestionToQuiz(PDO $conn, int $quizId, array $question): int
    {
        $stmt = $conn->prepare("
            INSERT INTO questions (quizId, text, audioUrl)
            VALUES (:quizId, :text, :audioUrl)
            RETURNING id
        ");

        $stmt->execute([
            ':quizId' => $quizId,
            ':text' => $question['text'],
            ':audioUrl' => $question['audioUrl']
        ]);

        $questionId = $stmt->fetchColumn();

        return (int)$questionId;
    }
}
