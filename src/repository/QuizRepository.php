<?php

require_once 'Repository.php';

class QuizRepository extends Repository
{
    public function getAllQuizzes(): ?array
    {
        $conn = $this->database->connect();

        $stmt = $conn->prepare("
            SELECT * FROM quiz_preview
            LIMIT 10
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
            SELECT * FROM quiz_preview
            WHERE LOWER(title) LIKE LOWER(:search)
        ");

        $stmt->bindValue(':search', '%' . $searchText . '%', PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->database->disconnect();

        return $result ?: null;
    }

    public function createQuiz(PDO $conn, array $quiz): int
    {
        $stmt = $conn->prepare("
            INSERT INTO quizzes (title, albumCoverUrl, createdBy)
            VALUES (:title, :coverUrl, :createdBy)
            RETURNING id
        ");

        $stmt->execute([
            ':title' => $quiz['title'],
            ':coverUrl' => $quiz['coverUrl'],
            ':createdBy' => $quiz['createdByUserId']
        ]);

        $quizId = $stmt->fetchColumn();

        return (int)$quizId;
    }

    public function getQuizContentById(int $quizId): ?array
    {
        $conn = $this->database->connect();

        $stmt = $conn->prepare("
            SELECT * FROM quiz_content
            WHERE q.id = :quizId
        ");

        $stmt->execute([':quizId' => $quizId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        $quiz = [
            'id' => $rows[0]['quiz_id'],
            'title' => $rows[0]['quiz_title'],
            'albumCoverUrl' => $rows[0]['quiz_cover'],
            'questions' => []
        ];

        $questionsMap = [];

        foreach ($rows as $row) {
            if ($row['question_id']) {

                if (!isset($questionsMap[$row['question_id']])) {
                    $questionsMap[$row['question_id']] = [
                        'id' => $row['question_id'],
                        'text' => $row['question_text'],
                        'audioUrl' => $row['question_audio'],
                        'answers' => []
                    ];
                }

                if ($row['answer_id']) {
                    $questionsMap[$row['question_id']]['answers'][] = [
                        'id' => $row['answer_id'],
                        'text' => $row['answer_text'],
                        'isCorrect' => (bool)$row['answer_correct']
                    ];
                }
            }
        }

        $quiz['questions'] = array_values($questionsMap);

        return $quiz;
    }
}
