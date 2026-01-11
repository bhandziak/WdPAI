<?php

require_once 'Repository.php';
require_once __DIR__ . './../mappers/QuizPreviewMapper.php';

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

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->database->disconnect();

        if (!$rows) {
            return [];
        }

        return array_map(
            fn($row) => QuizPreviewMapper::fromArray($row),
            $rows
        );
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

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->database->disconnect();

        if (!$rows) {
            return [];
        }

        return array_map(
            fn($row) => QuizPreviewMapper::fromArray($row),
            $rows
        );
    }

    public function createQuiz(PDO $conn, array $quiz): int
    {
        $stmt = $conn->prepare("
            INSERT INTO quizzes (title, album_cover_url, created_by)
            VALUES (:title, :album_cover_url, :created_by)
            RETURNING id
        ");

        $stmt->execute([
            ':title' => $quiz['title'],
            ':album_cover_url' => $quiz['album_cover_url'],
            ':created_by' => $quiz['created_by']
        ]);

        $quizId = $stmt->fetchColumn();

        return (int)$quizId;
    }

    public function getQuizContentById(int $quizId): ?array
    {
        $conn = $this->database->connect();

        $stmt = $conn->prepare("
            SELECT * FROM quiz_content
            WHERE quiz_id = :quiz_id
        ");

        $stmt->execute([':quiz_id' => $quizId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        $quiz = [
            'id' => $rows[0]['quiz_id'],
            'title' => $rows[0]['quiz_title'],
            'album_cover_url' => $rows[0]['quiz_cover'],
            'questions' => []
        ];

        $questionsMap = [];

        foreach ($rows as $row) {
            if ($row['question_id']) {

                if (!isset($questionsMap[$row['question_id']])) {
                    $questionsMap[$row['question_id']] = [
                        'id' => $row['question_id'],
                        'text' => $row['question_text'],
                        'audio_url' => $row['question_audio'],
                        'answers' => []
                    ];
                }

                if ($row['answer_id']) {
                    $questionsMap[$row['question_id']]['answers'][] = [
                        'id' => $row['answer_id'],
                        'text' => $row['answer_text'],
                        'is_correct' => (bool)$row['answer_correct']
                    ];
                }
            }
        }

        $quiz['questions'] = array_values($questionsMap);

        return $quiz;
    }
}
