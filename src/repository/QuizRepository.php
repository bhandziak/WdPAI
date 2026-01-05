<?php

require_once 'Repository.php';

class QuizRepository extends Repository
{
    public function getAllQuizzes(): ?array
    {
        $conn = $this->database->connect();

        $stmt = $conn->prepare("
            SELECT 
                q.id,
                q.title,
                q.albumCoverUrl,
                q.createdBy,
                u.username AS createdByUsername
            FROM quizzes q
            JOIN users u ON u.id = q.createdBy
            ORDER BY q.id DESC
        ");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->database->disconnect();

        return $result ?: null;
    }

    public function getQuizzesByText(string $searchText): ?array
    {
        $conn = $this->database->connect();

        $stmt = $conn->prepare("
            SELECT 
                q.id,
                q.title,
                q.albumCoverUrl,
                q.createdBy,
                u.username AS createdByUsername
            FROM quizzes q
            JOIN users u ON u.id = q.createdBy
            WHERE LOWER(q.title) LIKE LOWER(:search)
            ORDER BY q.id DESC
        ");

        $stmt->bindValue(':search', '%' . $searchText . '%', PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->database->disconnect();

        return $result ?: null;
    }
}
