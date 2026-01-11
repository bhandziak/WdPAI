<?php

class Answer
{
    public int $id;
    public string $text;
    public bool $is_correct;

    public function __construct(
        int $id,
        string $text,
        bool $is_correct
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->is_correct = $is_correct;
    }
}
