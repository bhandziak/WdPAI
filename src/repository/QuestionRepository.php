<?php

require_once 'Repository.php';

class QuestionRepository extends Repository
{
    public function addQuestionToQuiz(PDO $conn, int $quizId, array $question): int
    {
        $stmt = $conn->prepare("
            INSERT INTO questions (quiz_id, text, audio_url)
            VALUES (:quiz_id, :text, :audio_url)
            RETURNING id
        ");

        $stmt->execute([
            ':quiz_id' => $quizId,
            ':text' => $question['text'],
            ':audio_url' => $question['audio_url']
        ]);

        $questionId = $stmt->fetchColumn();

        return (int)$questionId;
    }
}
