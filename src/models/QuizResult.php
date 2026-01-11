<?php

class QuizResult
{
    public int $user_id;
    public int $quiz_id;
    public int $score;
    public int $correct_answers;
    public int $incorrect_answers;
    public int $total_time;

    public function __construct(
        int $user_id,
        int $quiz_id,
        int $score,
        int $correct_answers,
        int $incorrect_answers,
        int $total_time
    ) {
        $this->user_id = $user_id;
        $this->quiz_id = $quiz_id;
        $this->score = $score;
        $this->correct_answers = $correct_answers;
        $this->incorrect_answers = $incorrect_answers;
        $this->total_time = $total_time;
    }
}
