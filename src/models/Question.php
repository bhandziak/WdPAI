<?php

require_once 'Answer.php';

class Question
{
    public int $id;
    public string $text;
    public ?string $audio_url;
    /** @var Answer[] */
    public array $answers;

    public function __construct(
        int $id,
        string $text,
        ?string $audio_url,
        array $answers
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->audio_url = $audio_url;
        $this->answers = $answers;
    }
}
