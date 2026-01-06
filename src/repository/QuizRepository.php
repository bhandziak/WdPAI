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
        SELECT
            q.id            AS quiz_id,
            q.title         AS quiz_title,
            q.albumCoverUrl AS quiz_cover,

            qs.id           AS question_id,
            qs.text         AS question_text,
            qs.audioUrl     AS question_audio,

            a.id            AS answer_id,
            a.text          AS answer_text,
            a.isCorrect     AS answer_correct
        FROM quizzes q
        LEFT JOIN questions qs ON qs.quizId = q.id
        LEFT JOIN answers a ON a.questionId = qs.id
        WHERE q.id = :quizId
        ORDER BY qs.id, a.id
    ");

        $stmt->execute([':quizId' => $quizId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        // 🧠 Mapowanie flat rows → struktura drzewa
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
