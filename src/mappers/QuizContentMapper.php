<?php

require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Answer.php';

class QuizContentMapper
{
    public static function fromRows(array $rows): Quiz
    {
        $quiz = new Quiz(
            id: (int)$rows[0]['quiz_id'],
            title: $rows[0]['quiz_title'],
            album_cover_url: $rows[0]['quiz_cover'],
            questions: []
        );

        $questions_map = [];

        foreach ($rows as $row) {
            if (!$row['question_id']) {
                continue;
            }

            $question_id = (int)$row['question_id'];

            if (!isset($questions_map[$question_id])) {
                $questions_map[$question_id] = new Question(
                    id: $question_id,
                    text: $row['question_text'],
                    audio_url: $row['question_audio'],
                    answers: []
                );
            }

            if ($row['answer_id']) {
                $questions_map[$question_id]->answers[] = new Answer(
                    id: (int)$row['answer_id'],
                    text: $row['answer_text'],
                    is_correct: (bool)$row['answer_correct']
                );
            }
        }


        $quiz->questions = array_values($questions_map);

        return $quiz;
    }
}
