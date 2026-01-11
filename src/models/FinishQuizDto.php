<?php

class FinishQuizDto
{
    public int $quiz_id;
    public string $quiz_title;
    public ?string $album_cover_url;
    public int $score;
    public int $total_time;
    public int $correct_answers;
    public int $incorrect_answers;

    public function __construct(
        int $quiz_id,
        string $quiz_title,
        ?string $album_cover_url,
        int $score,
        int $total_time,
        int $correct_answers,
        int $incorrect_answers
    ) {
        $this->quiz_id = $quiz_id;
        $this->quiz_title = $quiz_title;
        $this->album_cover_url = $album_cover_url;
        $this->score = $score;
        $this->total_time = $total_time;
        $this->correct_answers = $correct_answers;
        $this->incorrect_answers = $incorrect_answers;
    }
}
