<?php

require_once __DIR__ . '/../models/FinishQuizDto.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/QuizResult.php';

class FinishQuizMapper
{
    // Maps QuizResult and Quiz data to FinishQuizDto
    public static function fromResultAndQuiz(QuizResult $quizResult, Quiz $quizData): FinishQuizDto
    {
        return new FinishQuizDto(
            quiz_id: $quizResult->quiz_id,
            quiz_title: $quizData->title,
            album_cover_url: $quizData->album_cover_url,
            score: $quizResult->score,
            total_time: $quizResult->total_time,
            correct_answers: $quizResult->correct_answers,
            incorrect_answers: $quizResult->incorrect_answers
        );
    }
}
