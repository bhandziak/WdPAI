<?php

require_once __DIR__ . '/../models/QuizResult.php';

class QuizResultMapper
{
    // Maps quiz result data with user ID to QuizResult model
    public static function fromData(array $quizResult, int $userId): QuizResult
    {
        return new QuizResult(
            user_id: $userId,
            quiz_id: $quizResult['quiz_id'],
            score: $quizResult['score'],
            correct_answers: $quizResult['correct_answers'],
            incorrect_answers: $quizResult['incorrect_answers'],
            total_time: $quizResult['total_time']
        );
    }
}
